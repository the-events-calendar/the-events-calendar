<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * Instantiate the template class, preparing a view file for use. If no name is passed, defaults to the class for the current view
	 *
	 * @param bool|string $class Classname you want to instantiate
	 *
	 * @return void
	 * @uses TribeEventsTemplates::instantiate_template_class()
	 **/
	function tribe_initialize_view( $class = false ) {
		do_action( 'tribe_pre_initialize_view' );
		TribeEventsTemplates::instantiate_template_class( $class );
	}

	/**
	 * Includes a view file, runs hooks around the view
	 *
	 * @param bool|string $view View slug
	 *
	 * @return void
	 **/
	function tribe_get_view( $view = false ) {

		do_action( 'tribe_pre_get_view' );

		if ( ! $view ) {
			$template_file = tribe_get_current_template();
		} else {
			$template_file = TribeEventsTemplates::getTemplateHierarchy( $view, array( 'disable_view_check' => true ) );
		}

		if ( file_exists( $template_file ) ) {
			do_action( 'tribe_events_before_view', $template_file );
			include( $template_file );
			do_action( 'tribe_events_after_view', $template_file );
		}
	}

	/**
	 * Includes a template part, similar to the WP get template part, but looks
	 * in the correct directories for Tribe Events templates
	 *
	 * @param string      $slug
	 * @param null|string $name
	 * @param array       $data optional array of vars to inject into the template part
	 *
	 * @uses TribeEventsTemplates::getTemplateHierarchy
	 **/
	function tribe_get_template_part( $slug, $name = null, array $data = null ) {

		// Execute code for this part
		do_action( 'tribe_pre_get_template_part_' . $slug, $slug, $name );
		// Setup possible parts
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.php';
		}
		$templates[] = $slug . '.php';

		// Allow template parts to be filtered
		$templates = apply_filters( 'tribe_get_template_part_templates', $templates, $slug, $name );

		// Make any provided variables available in the template's symbol table
		if ( is_array( $data ) ) {
			extract( $data );
		}

		// loop through templates, return first one found.
		foreach ( $templates as $template ) {
			$file = TribeEventsTemplates::getTemplateHierarchy( $template, array( 'disable_view_check' => true ) );
			$file = apply_filters( 'tribe_get_template_part_path', $file, $template, $slug, $name );
			$file = apply_filters( 'tribe_get_template_part_path_' . $template, $file, $slug, $name );
			if ( file_exists( $file ) ) {
				ob_start();
				do_action( 'tribe_before_get_template_part', $template, $file, $template, $slug, $name );
				include( $file );
				do_action( 'tribe_after_get_template_part', $template, $file, $slug, $name );
				$html = ob_get_clean();
				echo apply_filters( 'tribe_get_template_part_content', $html, $template, $file, $slug, $name );
			}
		}
		do_action( 'tribe_post_get_template_part_' . $slug, $slug, $name );
	}

	/**
	 * Get Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $default    Value to return if no such option is found.
	 *
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 */
	function tribe_get_option( $optionName, $default = '' ) {
		$tribe_ecp = TribeEvents::instance();

		return apply_filters( 'tribe_get_option', $tribe_ecp->getOption( $optionName, $default ), $optionName, $default );
	}

	/**
	 * Check if the current request is for a tribe view via ajax
	 * @return boolean
	 */
	function tribe_is_ajax_view_request( $view = false ) {
		$is_ajax_view_request = false;
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! empty( $_REQUEST['action'] ) ) {
			switch ( $view ) {
				case false:
					$is_ajax_view_request = ( ! empty( $_REQUEST['tribe_event_display'] ) || ! empty( $_REQUEST['eventDate'] ) || ! empty( $_REQUEST['tribe-bar-date'] ) || ! empty( $_REQUEST['tribe_paged'] ) );
					break;
				case 'month' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_Month_Template::AJAX_HOOK );
					break;
				case 'list' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_List_Template::AJAX_HOOK);
					break;
				case 'day' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_Day_Template::AJAX_HOOK );
					break;
			}
		}

		return apply_filters( 'tribe_is_ajax_view_request', $is_ajax_view_request, $view );
	}

	/**
	 * Update Option
	 *
	 * Set specific key from options array, optionally provide a default return value
	 *
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $value      Value to save
	 *
	 * @return void
	 */
	function tribe_update_option( $optionName, $value ) {
		$tribe_ecp = TribeEvents::instance();
		$tribe_ecp->setOption( $optionName, $value );
	}

	/**
	 * Get Network Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $default    Value to return if no such option is found.
	 *
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 */
	function tribe_get_network_option( $optionName, $default = '' ) {
		$tribe_ecp = TribeEvents::instance();

		return $tribe_ecp->getNetworkOption( $optionName, $default );
	}

	/**
	 * Event Type Test
	 *
	 * Checks type of $postId to determine if it is an Event
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool true if this post is an Event post type
	 */
	function tribe_is_event( $postId = null ) {
		return apply_filters( 'tribe_is_event', TribeEvents::instance()->isEvent( $postId ), $postId );
	}

	/**
	 * Get Event
	 *
	 * Queries the events using WordPress get_posts() by setting the post type and sorting by event date.
	 *
	 * @param array $args query vars with added defaults including post_type of events, sorted (orderby) by event date (order) ascending
	 * @param bool  $full (optional) if the full query object is required or just an array of event posts
	 *
	 * @return array List of posts.
	 * @link http://codex.wordpress.org/Template_Tags/get_posts
	 * @link http://codex.wordpress.org/Function_Reference/get_post
	 * @uses get_posts()
	 * @see  get_posts()
	 */
	function tribe_get_events( $args = array(), $full = false ) {
		if ( empty ( $args['eventDisplay'] ) ) {
			$args['eventDisplay'] = 'custom';
		}

		return apply_filters( 'tribe_get_events', TribeEventsQuery::getEvents( $args, $full ), $args, $full );
	}

	/**
	 * Returns the current event post object (if there is one) or else null.
	 *
	 * Optionally the post object or ID of an event can be passed in and,
	 * again, the event post object will be returned if possible.
	 *
	 * @param $event
	 * @return null|WP_Post
	 */
	function tribe_events_get_event( $event = null ) {
		global $post;

		if ( null === $event ) {
			return $post;
		}

		if ( is_a( $event, 'WP_Post' ) && TribeEvents::POSTTYPE === get_post_type( $event ) ) {
			return $post;
		}

		if ( is_numeric( $event ) && $event == intval( $event ) ) {
			$event = get_post( $event );

			if ( null !== $event && TribeEvents::POSTTYPE === get_post_type( $event ) ) {
				return $event;
			}
		}

		return null;
	}

	/**
	 * All Day Event Test
	 *
	 * Returns true if the event is an all day event
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool
	 */
	function tribe_event_is_all_day( $postId = null ) {
		//		$postId = TribeEvents::postIdHelper( $postId );
		$output = ! ! tribe_get_event_meta( $postId, '_EventAllDay', true );

		return apply_filters( 'tribe_event_is_all_day', $output, $postId );
	}

	/**
	 * Multi-day Event Test
	 *
	 * Returns true if the event spans multiple days
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool true if event spans multiple days
	 */
	function tribe_event_is_multiday( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$start  = (array) tribe_get_event_meta( $postId, '_EventStartDate', false );
		sort( $start );
		$start  = $start[0];
		$end    = strtotime( tribe_get_event_meta( $postId, '_EventEndDate', true ) );
		$output = ( $end > strtotime( tribe_event_end_of_day( $start ) ) );

		return apply_filters( 'tribe_event_is_multiday', $output, $postId, $start, $end );
	}

	/**
	 * Event Category ID's
	 *
	 * Display the event category ID as a class for events wrapper
	 *
	 * @uses wp_get_object_terms()
	 */
	function tribe_get_event_cat_ids( $post_id = 0 ) {
		$post_id = TribeEvents::postIdHelper( $post_id );

		return wp_list_pluck( (array) get_the_terms( $post_id, TribeEvents::TAXONOMY ), 'term_id' );
	}

	/**
	 * Event Category slugs
	 *
	 * Display the event category ID as a class for events wrapper
	 *
	 * @uses wp_get_object_terms()
	 */
	function tribe_get_event_cat_slugs( $post_id = 0 ) {
		$post_id = TribeEvents::postIdHelper( $post_id );
		$slugs   = wp_list_pluck( (array) get_the_terms( $post_id, TribeEvents::TAXONOMY ), 'slug' );

		return apply_filters( 'tribe_get_event_cat_slugs', $slugs, $post_id );
	}

	/**
	 * get the term list by taxonomy (default categories) for a single event
	 *
	 * @param  int   $post_id
	 * @param  array $args
	 *
	 * @return string HTML string of taxonomy terms
	 */
	function tribe_get_event_taxonomy( $post_id = null, $args = array() ) {
		$post_id   = TribeEvents::postIdHelper( $post_id );
		$tribe_ecp = TribeEvents::instance();
		$defaults  = array(
			'taxonomy' => $tribe_ecp->get_event_taxonomy(),
			'before'   => '<li>',
			'sep'      => '</li><li>',
			'after'    => '</li>'
		);
		$args      = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		$taxonomy = get_the_term_list( $post_id, $taxonomy, $before, $sep, $after );

		return apply_filters( 'tribe_get_event_taxonomy', $taxonomy, $post_id, $args );
	}

	/**
	 * Event Categories (Display)
	 *
	 * Display the event categories with display param
	 *
	 * @uses     tribe_get_event_taxonomy()
	 * @replaces tribe_meta_event_cats()
	 *
	 * @param int   $post_id
	 * @param array $args
	 *
	 * @return string $html (echo if provided in $args)
	 */
	function tribe_get_event_categories( $post_id = null, $args = array() ) {
		$post_id    = is_null( $post_id ) ? get_the_ID() : $post_id;
		$defaults   = array(
			'echo'         => false,
			'label'        => null,
			'label_before' => '<div>',
			'label_after'  => '</div>',
			'wrap_before'  => '<ul class="tribe-event-categories">',
			'wrap_after'   => '</ul>'
		);
		$args       = wp_parse_args( $args, $defaults );
		$categories = tribe_get_event_taxonomy( $post_id, $args );

		// check for the occurances of links in the returned string
		$label = is_null( $args['label'] ) ? _n( 'Event Category', 'Event Categories', substr_count( $categories, "<a href" ), 'tribe-events-calendar' ) : $args['label'];

		$html = ! empty( $categories ) ? sprintf(
			'%s%s:%s %s%s%s',
			$args['label_before'],
			$label,
			$args['label_after'],
			$args['wrap_before'],
			$categories,
			$args['wrap_after']
		) : '';
		if ( $args['echo'] ) {
			echo apply_filters( 'tribe_get_event_categories', $html, $post_id, $args, $categories );
		} else {
			return apply_filters( 'tribe_get_event_categories', $html, $post_id, $args, $categories );
		}
	}

	/**
	 * Event Tags (Display)
	 *
	 * Display the event tags
	 *
	 * @param null|string $label
	 * @param string      $separator
	 * @param bool        $echo
	 *
	 * @return array
	 * @uses the_terms()
	 */
	function tribe_meta_event_tags( $label = null, $separator = ', ', $echo = true ) {
		if ( ! $label ) {
			$label = __( 'Tags:', 'tribe-events-calendar' );
		}

		$tribe_ecp = TribeEvents::instance();
		$list      = get_the_term_list( get_the_ID(), 'post_tag', '<dt>' . $label . '</dt><dd class="tribe-event-tags">', $separator, '</dd>' );
		$list      = apply_filters( 'tribe_meta_event_tags', $list, $label, $separator, $echo );
		if ( $echo ) {
			echo $list;
		} else {
			return $list;
		}
	}

	/**
	 * Event Post Meta
	 *
	 * Get event post meta.
	 *
	 * @param int|null    $postId (optional)
	 * @param string|bool $meta   name of the meta_key
	 * @param bool        $single determines if the results should be a single item or an array of items.
	 *
	 * @return mixed meta value(s)
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ) {
		$postId    = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getEventMeta( $postId, $meta, $single );

		return apply_filters( 'tribe_get_event_meta', $output, $postId, $meta, $single );
	}

	/**
	 * Event Category Name
	 *
	 * Return the current event category name based the url.
	 *
	 * @return string Name of the Event Category
	 */
	function tribe_meta_event_category_name() {
		$tribe_ecp   = TribeEvents::instance();
		$current_cat = get_query_var( 'tribe_events_cat' );
		if ( $current_cat ) {
			$term_info = get_term_by( 'slug', $current_cat, $tribe_ecp->get_event_taxonomy() );

			return apply_filters( 'tribe_meta_event_category_name', $term_info->name, $current_cat, $term_info );
		}
	}

	/**
	 * Current Template
	 *
	 * Get the current page template that we are on
	 *
	 * @todo Update the function name to ensure there are no namespace conflicts.
	 * @return string Page template
	 */
	function tribe_get_current_template() {
		return apply_filters( 'tribe_get_current_template', TribeEventsTemplates::get_current_page_template() );
	}

	/**
	 * Venue Type Test
	 *
	 * Checks type of $postId to determine if it is a Venue
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool True if post type id Venue
	 */
	function tribe_is_venue( $postId = null ) {
		$tribe_ecp = TribeEvents::instance();

		return apply_filters( 'tribe_is_venue', $tribe_ecp->isVenue( $postId ), $postId );
	}

	/**
	 * Organizer Type Test
	 *
	 * Checks type of $postId to determine if it is a Organizer
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool True if post type id Venue
	 */
	function tribe_is_organizer( $postId = null ) {
		$tribe_ecp = TribeEvents::instance();

		return apply_filters( 'tribe_is_organizer', $tribe_ecp->isOrganizer( $postId ), $postId );
	}

	/**
	 * HTML Before Event (Display)
	 *
	 * Display HTML to output before the event template
	 *
	 */
	function tribe_events_before_html() {
		$before = stripslashes( tribe_get_option( 'tribeEventsBeforeHTML', '' ) );
		$before = wptexturize( $before );
		$before = convert_chars( $before );
		$before = wpautop( $before );
		$before = do_shortcode( stripslashes( shortcode_unautop( $before ) ) );
		$before = '<div class="tribe-events-before-html">' . $before . '</div>';
		$before = $before . '<span class="tribe-events-ajax-loading"><img class="tribe-events-spinner-medium" src="' . tribe_events_resource_url( 'images/tribe-loading.gif' ) . '" alt="' . __( 'Loading Events', 'tribe-events-calendar' ) . '" /></span>';

		echo apply_filters( 'tribe_events_before_html', $before );
	}

	/**
	 * HTML After Event (Display)
	 *
	 * Display HTML to output after the event template
	 *
	 */
	function tribe_events_after_html() {
		$after = stripslashes( tribe_get_option( 'tribeEventsAfterHTML', '' ) );
		$after = wptexturize( $after );
		$after = convert_chars( $after );
		$after = wpautop( $after );
		$after = do_shortcode( stripslashes( shortcode_unautop( $after ) ) );
		$after = '<div class="tribe-events-after-html">' . $after . '</div>';

		echo apply_filters( 'tribe_events_after_html', $after );
	}

	/**
	 * Prints out or returns classes on an event wrapper
	 *
	 * @param $event |0 post id or object
	 * @param $echo  |true
	 *
	 * @return void or string
	 **/
	function tribe_events_event_classes( $event = 0, $echo = true ) {
		global $post, $wp_query;

		// May be called when the global $post object does not exist - ie during ajax loads of various views
		// ... creating a dummy object allows the method to proceed semi-gracefully (interim measure only)

		//If $post object doesn't exist and an $event_id wasn't specified, then use a dummy object
		if ( is_a( 'WP_Post', $event ) ) {
			$event_id = $event->ID;
		} elseif ( $event !== 0 ) {
			$event_id = $event;
		} else {
			$event_id = $post->ID;
		}

		if ( ! $event_id ) {
			return '';
		}

		$classes         = array( 'hentry', 'vevent', 'type-tribe_events', 'post-' . $event_id, 'tribe-clearfix' );
		$tribe_cat_slugs = tribe_get_event_cat_slugs( $event_id );

		foreach ( $tribe_cat_slugs as $tribe_cat_slug ) {
			if ( ! empty( $tribe_cat_slug ) ) {
				$classes[] = 'tribe-events-category-' . $tribe_cat_slug;
			}
		}
		if ( $venue_id = tribe_get_venue_id( $event_id ) ) {
			$classes[] = 'tribe-events-venue-' . $venue_id;
		}
		if ( $organizer_id = tribe_get_organizer_id( $event_id ) ) {
			$classes[] = 'tribe-events-organizer-' . $organizer_id;
		}
		// added first class for css
		if ( ( $wp_query->current_post == 0 ) && ! tribe_is_day() ) {
			$classes[] = 'tribe-events-first';
		}
		// added last class for css
		if ( $wp_query->current_post == $wp_query->post_count - 1 ) {
			$classes[] = 'tribe-events-last';
		}

		$classes = apply_filters( 'tribe_events_event_classes', $classes );
		if ( $echo ) {
			echo implode( ' ', $classes );
		} else {
			return implode( ' ', $classes );
		}
	}

	/**
	 * Prints out data attributes used in the template header tags
	 *
	 * @param string|null $current_view
	 *
	 * @return void
	 * @todo move to template classes
	 **/
	function tribe_events_the_header_attributes( $current_view = null ) {
		$attrs               = array();
		$current_view        = ! empty( $current_view ) ? $current_view : basename( tribe_get_current_template() );
		$attrs['data-title'] = wp_title( '|', false, 'right' );
		switch ( $current_view ) {
			case 'month.php' :
				$attrs['data-view']    = 'month';
				$attrs['data-date']    = date( 'Y-m', strtotime( tribe_get_month_view_date() ) );
				$attrs['data-baseurl'] = tribe_get_gridview_link( false );
				break;
			case 'day.php' :
				$attrs['data-startofweek'] = get_option( 'start_of_week' );
				break;
			case 'list.php' :
				$attrs['data-startofweek'] = get_option( 'start_of_week' );				
				$attrs['data-view'] = 'list';
				if ( tribe_is_upcoming() ) {
					$attrs['data-baseurl'] = tribe_get_listview_link( false );
				} elseif ( tribe_is_past() ) {
					$attrs['data-view']    = 'past';
					$attrs['data-baseurl'] = tribe_get_listview_past_link( false );
				}
				break;
		}

		if ( has_filter( 'tribe_events_mobile_breakpoint' ) ) {
			$attrs['data-mobilebreak'] = tribe_get_mobile_breakpoint();
		}

		$attrs = apply_filters( 'tribe_events_header_attributes', $attrs, $current_view );
		foreach ( $attrs as $attr => $value ) {
			echo " $attr=" . '"' . esc_attr( $value ) . '"';
		}
	}

	/**
	 * Returns or echoes a url to a file in the Events Calendar plugin resources directory
	 *
	 * @param string $resource the filename of the resource
	 * @param bool   $echo     whether or not to echo the url
	 *
	 * @return string
	 **/
	function tribe_events_resource_url( $resource, $echo = false ) {
		$url = apply_filters( 'tribe_events_resource_url', trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/' . $resource, $resource );
		if ( $echo ) {
			echo $url;
		}

		return $url;
	}

	/**
	 * Return an array with the days of the week, numbered with respect to the start_of_week WP option
	 *
	 * @param string $format the display format for the days of the week
	 *
	 * @return array Days of the week.
	 **/
	function tribe_events_get_days_of_week( $format = null ) {
		if ( $format == 'short' ) {
			$days_of_week = TribeEvents::instance()->daysOfWeekShort;
		} else {
			$days_of_week = TribeEvents::instance()->daysOfWeek;
		}
		$start_of_week = get_option( 'start_of_week', 0 );
		for ( $i = 0; $i < $start_of_week; $i ++ ) {
			$day = $days_of_week[$i];
			unset( $days_of_week[$i] );
			$days_of_week[$i] = $day;
		}

		return apply_filters( 'tribe_events_get_days_of_week', $days_of_week );
	}

	/**
	 * conditional tag to determine if the cost field should be shown in the admin editors.
	 * @return bool
	 */
	function tribe_events_admin_show_cost_field() {
		$modules      = apply_filters( 'tribe_events_tickets_modules', null );
		$event_origin = get_post_meta( get_the_ID(), '_EventOrigin', true );
		$show_cost    = empty( $modules ) ||
						class_exists( 'Event_Tickets_PRO' ) ||
						in_array( $event_origin, apply_filters( 'tribe_events_admin_show_cost_field_origin', array( 'community-events' ) ) );

		return apply_filters( 'tribe_events_admin_show_cost_field', $show_cost, $modules );
	}

	/**
	 * Get an event's cost
	 *
	 * @param null|int $postId             (optional)
	 * @param bool     $withCurrencySymbol Include the currency symbol
	 *
	 * @return string Cost of the event.
	 */
	function tribe_get_cost( $postId = null, $withCurrencySymbol = false ) {
		$tribe_ecp = TribeEvents::instance();
		$postId    = TribeEvents::postIdHelper( $postId );

		$cost = tribe_get_event_meta( $postId, '_EventCost', true );

		if ( $cost === '' ) {
			$cost = '';
		} elseif ( $cost === '0' ) {
			$cost = __( "Free", 'tribe-events-calendar' );
		} else {
			$cost = esc_html( $cost );
		}

		// check if the currency symbol is desired, and it's just a number in the field
		// be sure to account for european formats in decimals, and thousands separators
		if ( $withCurrencySymbol && is_numeric( str_replace( array( ',', '.' ), '', $cost ) ) ) {
			$cost = tribe_format_currency( $cost );
		}

		return apply_filters( 'tribe_get_cost', $cost, $postId, $withCurrencySymbol );
	}

	/**
	 * Returns the event cost complete with currency symbol.
	 *
	 * Essentially an alias of tribe_get_cost(), as if called with the $withCurrencySymbol
	 * argument set to true. Useful for callbacks.
	 *
	 * @param null $postId
	 *
	 * @return mixed|void
	 */
	function tribe_get_formatted_cost( $postId = null ) {
		return apply_filters( 'tribe_get_formatted_cost', tribe_get_cost( $postId, true ) );
	}

	/**
	 * Receives a float and formats it with a currency symbol
	 *
	 * @param $cost pricing to format
	 */
	function tribe_format_currency( $cost, $postId = null, $currency_symbol = null, $reverse_position = null ) {

		$postId = TribeEvents::postIdHelper( $postId );

		// if no currency symbol was passed, and we're looking at a particular event,
		// let's check if there was a currency symbol set on that event
		if ( $postId && $currency_symbol == null ) {
			$currency_symbol = tribe_get_event_meta( $postId, '_EventCurrencySymbol', true );
		}

		// if no currency symbol was passed, or we're not looking at a particular event,
		// let's get the default currency symbol
		if ( ! $postId || ! $currency_symbol ) {
			$currency_symbol = tribe_get_option( 'defaultCurrencySymbol', '$' );
		}

		if ( $postId && $reverse_position == null ) {
			$reverse_position = tribe_get_event_meta( $postId, '_EventCurrencyPosition', true );
			$reverse_position = ( 'suffix' === $reverse_position );
		}

		if ( ! $reverse_position || ! $postId ) {
			$reverse_position = tribe_get_option( 'reverseCurrencyPosition', false );
		}

		$cost = $reverse_position ? $cost . $currency_symbol : $currency_symbol . $cost;

		return $cost;

	}

	/**
	 * Get the minimum cost of all events.
	 *
	 * @return int the minimum cost.
	 */
	function tribe_get_minimum_cost() {
		global $wpdb;

		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';' );

		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		foreach ( $costs as $index => $value ) {
			$costs[$index] = preg_replace( '/^[^\d]+(\d+\.?\d*)?.*$/', '$1', $value );
		}
		if ( empty( $costs ) ) {
			$costs = array( '0' );
		}

		$min = min( $costs );
		if ( $min == '' ) {
			$min = 0;
		}

		return $min;
	}

	/**
	 * Get the maximum cost of all events.
	 *
	 * @return int the maximum cost.
	 */
	function tribe_get_maximum_cost() {
		global $wpdb;

		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';' );

		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		foreach ( $costs as $index => $value ) {
			$costs[$index] = preg_replace( '/^[^\d]+(\d+\.?\d*)?.*$/', '$1', $value );
		}

		if ( empty( $costs ) ) {
			$costs = array( '0' );
		}

		$max = max( $costs );
		if ( $max == '' ) {
			$max = 0;
		}

		return $max;
	}

	/**
	 * Maps the cost array to make finding the minimum and maximum costs possible.
	 *
	 * @param array $costs
	 *
	 * @return array $costs
	 */
	function tribe_map_cost_array_callback( $costs ) {
		return $costs;
	}

	/**
	 * Event in Category Conditional
	 *
	 * Returns true if the event is in the specified category slug
	 *
	 * @param string $event_cat_slug
	 * @param int    $event_id
	 *
	 * @return boolean
	 */
	function tribe_event_in_category( $event_cat_slug, $event_id = null ) {

		if ( empty( $event_id ) ) {
			$event_id = get_the_ID();
		}

		$term = term_exists( $event_cat_slug, TribeEvents::TAXONOMY );

		if ( tribe_is_event( $event_id ) && is_object_in_term( $event_id, TribeEvents::TAXONOMY, array( $term['term_id'] ) ) ) {
			$return = true;
		} else {
			$return = false;
		}

		return apply_filters( 'tribe_event_in_category', $return );
	}

	/**
	 * Placeholder function that is used for ticketing plugins meant to be filtered by such plugins
	 *
	 * @return void
	 */
	function tribe_get_ticket_form() {
		$ticket_form = apply_filters( 'tribe_get_ticket_form', false );
		if ( $ticket_form && is_string( $ticket_form ) ) {
			echo $ticket_form;
		} else {
			return $ticket_form;
		}
	}

	/**
	 * helper function to remove empty lines from multi-line strings
	 *
	 * @link http://stackoverflow.com/questions/709669/how-do-i-remove-blank-lines-from-text-in-php
	 *
	 * @param string $multi_line_string a multiline string
	 *
	 * @return string the same string without empty lines
	 */
	function tribe_multi_line_remove_empty_lines( $multi_line_string ) {
		return preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $multi_line_string );
	}

	/**
	 * return the featured image html to an event (within the loop automatically will get event ID)
	 *
	 * @param int    $post_id
	 * @param string $size
	 * @param bool   $link
	 *
	 * @return string
	 */
	function tribe_event_featured_image( $post_id = null, $size = 'full', $link = true ) {
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$image_src      = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		$featured_image = '';

		//if link is not specifically excluded, then include <a>
		if ( ! empty( $image_src ) && $link ) {
			$featured_image .= '<div class="tribe-events-event-image"><a href="' . esc_url( tribe_get_event_link() ) . '" title="' . get_the_title( $post_id ) . '"><img src="' . esc_url( $image_src[0] ) . '" title="' . get_the_title( $post_id ) . '" /></a></div>';
		} elseif ( ! empty( $image_src ) ) {
			$featured_image .= '<div class="tribe-events-event-image"><img src="' . esc_url( $image_src[0] ) . '" title="' . get_the_title( $post_id ) . '" /></div>';
		}

		return apply_filters( 'tribe_event_featured_image', $featured_image, $post_id, $size, $image_src );
	}

	/**
	 * Get the date format specified in the tribe options
	 *
	 * @param bool $with_year
	 *
	 * @return mixed
	 */
	function tribe_get_date_format( $with_year = false ) {
		if ( $with_year ) {
			$format = tribe_get_option( 'dateWithYearFormat', get_option( 'date_format' ) );
		} else {
			$format = tribe_get_option( 'dateWithoutYearFormat', 'F j' );
		}

		return apply_filters( 'tribe_date_format', $format );

	}

	/**
	 * @param bool $with_year
	 *
	 * @return mixed|void
	 */
	function tribe_get_datetime_format( $with_year = false ) {
		$format = tribe_get_date_format( $with_year );
		$format .= tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$format .= get_option( 'time_format' );

		return apply_filters( 'tribe_datetime_format', $format );

	}

	/**
	 *
	 * @return mixed|void
	 */
	function tribe_get_time_format( ) {
		$format = get_option('time_format' );
		return apply_filters( 'tribe_time_format', $format );
	}

	/**
	 * Return the details of the start/end date/time.
	 *
	 * The highest level means of customizing this function's output is simply to adjust the date format settings under
	 * Events > Settings > Display, and WordPress time formats (via the General Settings admin screen).
	 * Beyond that, however, there are two filters which can be used to exercise further control here.
	 *
	 * The first is 'tribe_events_event_schedule_details_formatting' which allows an array of format settings to be
	 * altered - it's basic make-up is as a simple set of key:value pairs as follows.
	 *
	 * "show_end_time": for single day events only (not including all day events) it may not always be desirable to
	 *     include the end time. In that situation, this setting can be set to false and the end time will not be
	 *     displayed.
	 *
	 * "time": if it is undesirable to show times and only dates should be displayed then this setting can be set to
	 *     false. If it is false it will by extension cause 'show_end_time' to be false.
	 *
	 * The resulting string can also be caught and manipulated, or completely overridden, using the
	 * 'tribe_events_event_schedule_details' filter, should none of the above settings be sufficient.
	 *
	 * @param int|null $event
	 *
	 * @return string
	 * @TODO use tribe_get_datetime_format() and related functions if possible
	 */
	function tribe_events_event_schedule_details( $event = null, $before = '', $after = '' ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		$schedule                 = '<span class="date-start dtstart">';
		$format                   = '';
		$date_without_year_format = tribe_get_date_format();
		$date_with_year_format    = tribe_get_date_format( true );
		$time_format              = get_option( 'time_format' );
		$datetime_separator       = tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$time_range_separator     = tribe_get_option( 'timeRangeSeparator', ' - ' );
		$microformatStartFormat   = tribe_get_start_date( $event, false, 'Y-m-dTh:i' );
		$microformatEndFormat     = tribe_get_end_date( $event, false, 'Y-m-dTh:i' );

		$settings = array(
			'show_end_time' => true,
			'time'          => true,
		);

		$settings = wp_parse_args( apply_filters( 'tribe_events_event_schedule_details_formatting', $settings ), $settings );
		if ( ! $settings['time'] ) {
			$settings['show_end_time'] = false;
		}
		extract( $settings );

		$format = $date_with_year_format;

		// if it starts and ends in the current year then there is no need to display the year
		if ( tribe_get_start_date( $event, false, 'Y' ) === date( 'Y' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
			$format = $date_without_year_format;
		}

		if ( tribe_event_is_multiday( $event ) ) { // multi-date event

			$format2ndday = apply_filters( 'tribe_format_second_date_in_range', $format, $event );

			if ( tribe_event_is_all_day( $event ) ) {
				$schedule .= tribe_get_start_date( $event, true, $format );
				$schedule .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
				$schedule .= '</span>' . $time_range_separator;
				$schedule .= '<span class="date-end dtend">';
				$schedule .= tribe_get_end_date( $event, true, $format2ndday );
				$schedule .= '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
			} else {
				$schedule .= tribe_get_start_date( $event, false, $format ) . ( $time ? $datetime_separator . tribe_get_start_date( $event, false, $time_format ) : '' );
				$schedule .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
				$schedule .= '</span>' . $time_range_separator;
				$schedule .= '<span class="date-end dtend">';
				$schedule .= tribe_get_end_date( $event, false, $format2ndday ) . ( $time ? $datetime_separator . tribe_get_end_date( $event, false, $time_format ) : '' );
				$schedule .= '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
			}

		} elseif ( tribe_event_is_all_day( $event ) ) { // all day event
			$schedule .= tribe_get_start_date( $event, true, $format );
			$schedule .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
		} else { // single day event
			if ( tribe_get_start_date( $event, false, 'g:i A' ) === tribe_get_end_date( $event, false, 'g:i A' ) ) { // Same start/end time
				$schedule .= tribe_get_start_date( $event, false, $format ) . ( $time ? $datetime_separator . tribe_get_start_date( $event, false, $time_format ) : '' );
				$schedule .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
			} else { // defined start/end time
				$schedule .= tribe_get_start_date( $event, false, $format ) . ( $time ? $datetime_separator . tribe_get_start_date( $event, false, $time_format ) : '' );
				$schedule .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
				$schedule .= '</span>' . ( $show_end_time ? $time_range_separator : '' );
				$schedule .= '<span class="end-time dtend">';
				$schedule .= ( $show_end_time ? tribe_get_end_date( $event, false, $time_format ) : '' ) . '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
			}
		}

		$schedule .= '</span>';

		$schedule = $before . $schedule . $after;

		return apply_filters( 'tribe_events_event_schedule_details', $schedule );
	}

	/**
	 * Accepts two dates and returns the number of days between them
	 *
	 * @param string      $start_date
	 * @param string      $end_date
	 * @param string|bool $day_cutoff
	 *
	 * @return int
	 * @see TribeDateUtils::dateDiff()
	 **/
	function tribe_get_days_between( $start_date, $end_date, $day_cutoff = '00:00' ) {
		if ( $day_cutoff === false ) {
			$day_cutoff = '00:00';
		} elseif ( $day_cutoff === true ) {
			$day_cutoff = tribe_get_option( 'multiDayCutoff', '00:00' );
		}

		$start_date = new DateTime( $start_date );
		if ( $start_date < new DateTime( $start_date->format( 'Y-m-d ' . $day_cutoff ) ) ) {
			$start_date->modify( '-1 day' );
		}
		$end_date = new DateTime( $end_date );
		if ( $end_date <= new DateTime( $end_date->format( 'Y-m-d ' . $day_cutoff ) ) ) {
			$end_date->modify( '-1 day' );
		}

		return TribeDateUtils::dateDiff( $start_date->format( 'Y-m-d ' . $day_cutoff ), $end_date->format( 'Y-m-d ' . $day_cutoff ) );
	}

	/**
	 * Function to prepare content for use as a value in a json encoded string destined for storage on a html data attribute.
	 * Hence the double quote fun, especially in case they pass html encoded &quot; along. Any of those getting through to the data att will break jquery's parseJSON method.
	 * Themers can use this function to prepare data they may want to send to tribe_events_template_data() in the templates, and we use it in that function ourselves.
	 *
	 * @param $string
	 *
	 * @return string
	 */

	function tribe_prepare_for_json( $string ) {

		$value = trim( htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' ) );
		$value = str_replace( '&quot;', '"', $value );

		return $value;
	}

	/**
	 * Recursively iterate through an nested structure, calling
	 * tribe_prepare_for_json() on all scalar values
	 *
	 * @param mixed $value The data to be cleaned
	 * @return mixed The clean data
	 */
	function tribe_prepare_for_json_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map('tribe_prepare_for_json_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = tribe_prepare_for_json_deep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = tribe_prepare_for_json($value);
		}
		return $value;
	}

	/**
	 * Returns json for javascript templating functions throughout the plugin.
	 *
	 * @param $event
	 * @param $additional
	 *
	 * @return string
	 */
	function tribe_events_template_data( $event, array $additional = null ) {
		$has_image      = false;
		$start_time     = '';
		$end_time       = '';
		$image_src      = '';
		$image_tool_src = '';

		// @TODO use tribe_events_event_schedule_details()
		$date_format = tribe_get_date_format( true );
		$time_format = get_option( 'time_format', TribeDateUtils::TIMEFORMAT );

		$date_time_separator = tribe_get_option( 'dateTimeSeparator', ' @ ' );

		if ( ! empty( $event->EventStartDate ) ) {
			$start_time .= date_i18n( $date_format, strtotime( $event->EventStartDate ) );
		}

		if ( ! tribe_get_event_meta( $event->ID, '_EventAllDay', true ) ) {
			$start_time .= $date_time_separator . date_i18n( $time_format, strtotime( $event->EventStartDate ) );
		}

		if ( ! empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
			if ( date( 'Y-m-d', strtotime( $event->EventStartDate ) ) == date( 'Y-m-d', strtotime( $event->EventEndDate ) ) ) {

				if ( ! tribe_get_event_meta( $event->ID, '_EventAllDay', true ) ) {
					$end_time .= date_i18n( $time_format, strtotime( $event->EventEndDate ) );
				}
			} else {
				$end_time .= date_i18n( $date_format, strtotime( $event->EventEndDate ) );

				if ( ! tribe_get_event_meta( $event->ID, '_EventAllDay', true ) ) {
					$end_time .= $date_time_separator . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
				}
			}
		}

		if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $event->ID ) ) {
			$has_image = true;
			$image_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $event->ID ), 'medium' );
			$image_src = $image_arr[0];
		}

		if ( $has_image ) {
			$image_tool_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $event->ID ), array( 75, 75 ) );
			$image_tool_src = $image_tool_arr[0];
		}

		if ( has_excerpt( $event->ID ) ) {
			$excerpt = $event->post_excerpt;
		} else {
			$excerpt = $event->post_content;
		}
		$excerpt = TribeEvents::instance()->truncate( $excerpt, 30 );

		$category_classes = tribe_events_event_classes( $event->ID, false );

		$json = array(
			'eventId'         => $event->ID,
			'title'           => $event->post_title,
			'permalink'       => tribe_get_event_link( $event->ID ),
			'imageSrc'        => $image_src,
			'startTime'       => $start_time,
			'endTime'         => $end_time,
			'imageTooltipSrc' => $image_tool_src,
			'excerpt'         => $excerpt,
			'categoryClasses' => $category_classes,
		);

		if ( $additional ) {
			$json = array_merge( (array) $json, (array) $additional );
		}

		$json = apply_filters( 'tribe_events_template_data_array', $json, $event, $additional );

		$json = tribe_prepare_for_json_deep( $json );

		return json_encode( $json );
	}

	/**
	 * Accepts an array of query arguments, retrieves them, and returns the html for those events in list view
	 *
	 * Optional inline example:
	 * < code >
	 * <?php
	 * echo myfunction();
	 * ?>
	 * </ code >
	 *
	 * @param array $args       Args to be passed to TribeEventsQuery::getEvents()
	 * @param bool  $initialize Whether the list view template class needs to be included and initialized
	 *
	 * @return string
	 **/
	function tribe_include_view_list( $args = null, $initialize = true ) {
		global $wp_query;

		// hijack the main query to load the events via provided $args
		if ( ! is_null( $args ) || ! ( $wp_query->tribe_is_event || $wp_query->tribe_is_event_category ) ) {
			$reset_q  = $wp_query;
			$wp_query = TribeEventsQuery::getEvents( $args, true );
		}

		// single-event notices are jumping in on this init when loading as a module
		TribeEvents::removeNotice( 'event-past' );

		// get the list view template
		ob_start();
		if ( $initialize ) {
			tribe_initialize_view( 'Tribe_Events_List_Template' );
		}
		tribe_get_view( 'list/content' );
		$list_view_html = ob_get_clean();

		// fix the error of our ways
		if ( ! empty( $reset_q ) ) {
			$wp_query = $reset_q;
		}

		// return the parsed template
		return $list_view_html;
	}

	/**
	 * Generates html for any notices that have been queued on the current view
	 *
	 * @param bool $echo Whether or not to echo the notices html
	 *
	 * @return void | string
	 * @see TribeEvents::getNotices()
	 **/
	function tribe_events_the_notices( $echo = true ) {
		$notices = TribeEvents::getNotices();

		$html        = ! empty( $notices ) ? '<div class="tribe-events-notices"><ul><li>' . implode( '</li><li>', $notices ) . '</li></ul></div>' : '';
		$the_notices = apply_filters( 'tribe_events_the_notices', $html, $notices );
		if ( $echo ) {
			echo $the_notices;
		} else {
			return $the_notices;
		}
	}

	/**
	 * Get a list of the views that are enabled
	 *
	 * @param array $views Default list of views
	 *
	 * @return array
	 * @see tribeEnableViews option
	 **/
	function tribe_events_enabled_views( $views ) {
		$enabled_views_defaults = array();
		foreach ( $views as $view ) {
			$enabled_views_defaults[] = $view['displaying'];
		}

		return tribe_get_option( 'tribeEnableViews', $enabled_views_defaults );
	}

	/**
	 * Get a list of the views that are disabled
	 *
	 * @return array
	 **/
	function tribe_events_disabled_views() {
		static $disabled;
		if ( isset( $disabled ) ) {
			return $disabled;
		}
		$views    = apply_filters( 'tribe-events-bar-views', array(), false );
		$enabled  = tribe_events_enabled_views( $views );
		$disabled = array();
		foreach ( $views as $view ) {
			if ( ! in_array( $view['displaying'], $enabled ) ) {
				$disabled[] = $view['displaying'];
			}
		}

		return $disabled;
	}

	/**
	 * tribe_is_bot checks if the visitor is a bot and returns status
	 *
	 * @return bool
	 */
	function tribe_is_bot() {
		// get the current user agent
		$user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

		// check if the user agent is empty since most browsers identify themselves, so possibly a bot
		if ( empty( $user_agent ) ) {
			return apply_filters( 'tribe_is_bot_status', true, $user_agent, null );
		}

		// declare known bot user agents (lowercase)
		$user_agent_bots = (array) apply_filters(
			'tribe_is_bot_list', array(
				'bot',
				'slurp',
				'spider',
				'crawler',
				'yandex'
			)
		);

		foreach ( $user_agent_bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return apply_filters( 'tribe_is_bot_status', true, $user_agent, $bot );
			}
		}

		// we think this is probably a real human
		return apply_filters( 'tribe_is_bot_status', false, $user_agent, null );
	}

	/**
	 * Display the Events Calendar promo banner
	 *
	 * @param bool $echo Whether or not to echo the banner, if false, it's returned
	 *
	 * @return void|string
	 **/
	function tribe_events_promo_banner( $echo = true ) {
		if ( tribe_get_option( 'donate-link', false ) == true && ! tribe_is_bot() ) {
			$promo = apply_filters( 'tribe_events_promo_banner_message', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="' . TribeEvents::$tribeUrl . 'wordpress-events-calendar/?utm_medium=plugin-tec&utm_source=banner&utm_campaign=in-app">', '</a>' ) );
			$html  = apply_filters( 'tribe_events_promo_banner', sprintf( '<p class="tribe-events-promo">%s</p>', $promo ), $promo );
			if ( $echo ) {
				echo $html;
			} else {
				return $html;
			}
		}
	}

	/**
	 * Return the filters registered in the tribe bar
	 *
	 * @return array
	 **/
	function tribe_events_get_filters() {
		return apply_filters( 'tribe-events-bar-filters', array() );
	}

	/**
	 * Return the views registered in the tribe bar
	 *
	 * @return array
	 **/
	function tribe_events_get_views() {
		return apply_filters( 'tribe-events-bar-views', array() );
	}

	/**
	 * Count keys in a hierarchical array
	 *
	 * @param $value
	 * @param $key
	 */
	function tribe_count_hierarchical_keys( $value, $key ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment ++;
	}

	/**
	 * Count items in a hierarchical array
	 *
	 * @param array $walk
	 *
	 * @return int
	 */
	function tribe_count_hierarchical( array $walk ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment = 0;
		array_walk_recursive( $walk, 'tribe_count_hierarchical_keys' );

		return $tribe_count_hierarchical_increment;
	}

	/**
	 * Get and increment tab index in form fields
	 *
	 */
	function tribe_events_get_tab_index() {
		$tribe_events = TribeEvents::instance();

		return apply_filters( 'tribe_events_tab_index', $tribe_events->tabIndex() );
	}

	/**
	 * Echo and increment tab index in form fields
	 *
	 */
	function tribe_events_tab_index() {
		echo tribe_events_get_tab_index();
	}

	/**
	 * Check if a particular view is enabled
	 *
	 * @param string $view Name of view to check, should match what's in TribeEvents->displaying when on that view
	 *
	 * @return bool
	 **/
	function tribe_events_is_view_enabled( $view ) {
		$enabled       = false;
		$enabled_views = apply_filters( 'tribe-events-bar-views', array() );
		foreach ( $enabled_views as $enabled_view ) {
			if ( $enabled_view['displaying'] == $view ) {
				$enabled = true;
				break;
			}
		}

		return apply_filters( 'tribe_events_is_view_enabled', $enabled, $view, $enabled_views );
	}

	/**
	 * Effectively aliases WP's get_the_excerpt() function, except that it additionally strips shortcodes
	 * during ajax requests.
	 *
	 * The reason for this is that shortcodes added by other plugins/themes may not have been registered
	 * by the time our ajax responses are generated. To avoid leaving unparsed shortcodes in our excerpts
	 * then we strip out anything that looks like one.
	 *
	 * If this is undesirable the use of this function can simply be replaced within template overrides by
	 * WP's own get_the_excerpt() function.
	 *
	 * @return string
	 */
	function tribe_events_get_the_excerpt() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return get_the_excerpt();
		}

		return preg_replace( '#\[.+\]#U', '', get_the_excerpt() );
	}

	/**
	 * Get the breakpoint for switching to mobile styles
	 *
	 * @param int $breakpoint The default width (in pixels) at which to break into mobile styles
	 *
	 * @return int
	 */
	function tribe_get_mobile_breakpoint( $default = 768 ) {
		return apply_filters( 'tribe_events_mobile_breakpoint', $default );
	}

	/**
	 * Returns the latest known event end date, which can be expected to be a string
	 * in MySQL datetime format (unless some other specific format is provided).
	 *
	 * If this is impossible to determine it will return boolean false.
	 *
	 * @param string $format
	 *
	 * @return mixed bool|string
	 */
	function tribe_events_latest_date( $format = TribeDateUtils::DBDATETIMEFORMAT ) {
		// Check if the latest end date is already known
		$latest = tribe_get_option( 'latest_date', false );
		if ( false !== $latest ) {
			return TribeDateUtils::reformat( $latest, $format );
		}

		// If not, try to determine now
		TribeEvents::instance()->rebuild_known_range();
		$latest = tribe_get_option( 'latest_date', false );
		if ( false !== $latest ) {
			return TribeDateUtils::reformat( $latest, $format );
		}

		return false;
	}

	/**
	 * Returns the earliest known event start date, which can be expected to be a string
	 * in MySQL datetime format (unless some other specific format is provided).
	 *
	 * If this is impossible to determine it will return boolean false.
	 *
	 * @param string $format
	 *
	 * @return mixed bool|string
	 */
	function tribe_events_earliest_date( $format = TribeDateUtils::DBDATETIMEFORMAT ) {
		// Check if the earliest start date is already known
		$earliest = tribe_get_option( 'earliest_date', false );
		if ( false !== $earliest ) {
			return TribeDateUtils::reformat( $earliest, $format );
		}

		// If not, try to determine now
		TribeEvents::instance()->rebuild_known_range();
		$earliest = tribe_get_option( 'earliest_date', false );
		if ( false !== $earliest ) {
			return TribeDateUtils::reformat( $earliest, $format );
		}

		return false;
	}
}
