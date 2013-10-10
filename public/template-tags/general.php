<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * Instantiate the template class, preparing a view file for use. If no name is passed, defaults to the class for the current view
	 *
	 * @param bool|string $class Classname you want to instantiate
	 * @return void
	 * @author Jessica Yazbek
	 * @uses TribeEventsTemplates::instantiate_template_class()
	 * @since 3.0
	 **/
	function tribe_initialize_view( $class = false )	{
		do_action( 'tribe_pre_initialize_view' );
		TribeEventsTemplates::instantiate_template_class( $class );
	}

	/**
	 * Includes a view file, runs hooks around the view
	 *
	 * @param bool|string $view View slug
	 * @return void
	 * @author Jessica Yazbek
	 * @since 3.0
	 **/
	function tribe_get_view( $view = false ) {

		do_action( 'tribe_pre_get_view' );

		if ( ! $view ) {
			$template_file = tribe_get_current_template();
		} else {
			$template_file = TribeEventsTemplates::getTemplateHierarchy( $view, array('disable_view_check' => true)  );
		}

		if (file_exists($template_file)) {
			do_action('tribe_events_before_view', $template_file);
			include( $template_file );
			do_action('tribe_events_after_view', $template_file);
		}
	}

	/**
	 * Includes a template part, similar to the WP get template part, but looks
	 * in the correct directories for Tribe Events templates
	 *
	 * @param string $slug
	 * @param null|string $name
	 * @uses TribeEventsTemplates::getTemplateHierarchy
	 * @author Jessica Yazbek
	 * @since 3.0
	 **/
	function tribe_get_template_part( $slug, $name = null ) {

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


		// loop through templates, return first one found.
		foreach( $templates as $template ) {
			$file = TribeEventsTemplates::getTemplateHierarchy( $template, array('disable_view_check' => true) );
			$file = apply_filters( 'tribe_get_template_part_path', $file, $template, $slug, $name );
			$file = apply_filters( 'tribe_get_template_part_path_'.$template, $file, $slug, $name );
			if (file_exists($file)) {
				ob_start();
				do_action( 'tribe_before_get_template_part', $template, $file, $template, $slug, $name );
				include($file);
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
	 * @param string  $optionName Name of the option to retrieve.
	 * @param string  $default    Value to return if no such option is found.
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 * @since 2.0
	 */
	function tribe_get_option( $optionName, $default = '' ) {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters( 'tribe_get_option', $tribe_ecp->getOption( $optionName, $default ), $optionName, $default );
	}

	/**
	 * Update Option
	 *
	 * Set specific key from options array, optionally provide a default return value
	 *
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $value Value to save
	 * @return void
	 * @since 2.0.3
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
	 * @param string $default Value to return if no such option is found.
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 * @since 2.0
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
	 * @return bool true if this post is an Event post type
	 * @since 2.0
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
	 * @return array List of posts.
	 * @link http://codex.wordpress.org/Template_Tags/get_posts
	 * @link http://codex.wordpress.org/Function_Reference/get_post
	 * @uses get_posts()
	 * @see get_posts()
	 * @since 2.0
	 */
	function tribe_get_events( $args = array() ) {
		return apply_filters( 'tribe_get_events', TribeEventsQuery::getEvents( $args ), $args );
	}

	/**
	 * All Day Event Test
	 *
	 * Returns true if the event is an all day event
	 *
	 * @param int $postId (optional)
	 * @return bool
	 * @since 2.0
	 */
	function tribe_event_is_all_day( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = !! tribe_get_event_meta( $postId, '_EventAllDay', true );
		return apply_filters( 'tribe_event_is_all_day', $output, $postId );
	}

	/**
	 * Multi-day Event Test
	 *
	 * Returns true if the event spans multiple days
	 *
	 * @param int $postId (optional)
	 * @return bool true if event spans multiple days
	 * @since 2.0
	 */
	function tribe_event_is_multiday( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$start = (array)tribe_get_event_meta( $postId, '_EventStartDate', false );
		sort( $start );
		$start = strtotime( $start[0] );
		$end = strtotime( tribe_get_event_meta( $postId, '_EventEndDate', true ) );
		$output = date( 'd-m-Y', $start ) != date( 'd-m-Y', $end );
		return apply_filters( 'tribe_event_is_multiday', $output, $postId, $start, $end );
	}

	/**
	 * Event Category ID's
	 *
	 * Display the event category ID as a class for events wrapper
	 *
	 * @uses wp_get_object_terms()
	 * @since 2.1
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
	 * @since 2.1
	 */
	function tribe_get_event_cat_slugs( $post_id = 0 ) {
		$post_id = TribeEvents::postIdHelper( $post_id );
		$slugs = wp_list_pluck( (array) get_the_terms( $post_id, TribeEvents::TAXONOMY ), 'slug' );
		return apply_filters( 'tribe_get_event_cat_slugs', $slugs, $post_id );
	}

	/**
	 * get the term list by taxonomy (default categories) for a single event
	 * @param  int $post_id
	 * @param  array  $args
	 * @return string HTML string of taxonomy terms
	 */
	function tribe_get_event_taxonomy( $post_id = null, $args = array() ) {
		$post_id = TribeEvents::postIdHelper( $post_id );
		$tribe_ecp = TribeEvents::instance();
		$defaults = array(
			'taxonomy' => $tribe_ecp->get_event_taxonomy(),
			'before' => '<li>',
			'sep' => '</li><li>',
			'after' => '</li>' );
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		$taxonomy = get_the_term_list( $post_id, $taxonomy, $before, $sep, $after );
		return apply_filters( 'tribe_get_event_taxonomy', $taxonomy, $post_id, $args );
	}

	/**
	 * Event Categories (Display)
	 *
	 * Display the event categories with display param
	 *
	 * @uses tribe_get_event_taxonomy()
	 * @replaces tribe_meta_event_cats()
	 * @param int $post_id
	 * @param array $args
	 * @return string $html (echo if provided in $args)
	 * @since 3.0
	 */
	function tribe_get_event_categories( $post_id = null, $args = array() ) {
		$post_id = is_null( $post_id ) ? get_the_ID() : $post_id;
		$defaults = array(
			'echo' => false,
			'label' => null,
			'label_before' => '<div>',
			'label_after' => '</div>',
			'wrap_before' => '<ul class="tribe-event-categories">',
			'wrap_after' => '</ul>' );
		$args = wp_parse_args( $args, $defaults );
		$categories = tribe_get_event_taxonomy( $post_id, $args );

		// check for the occurances of links in the returned string
		$label = is_null( $args['label'] ) ? _n( 'Event Category', 'Event Categories', substr_count( $categories, "<a href" ), 'tribe-events-calendar' ) : $args['label'];

		$html = !empty( $categories ) ? sprintf( '%s%s:%s %s%s%s',
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
	 * @param string $separator
	 * @param bool $echo
	 * @return array
	 * @uses the_terms()
	 * @since 2.1
	 */
	function tribe_meta_event_tags( $label=null, $separator=', ', $echo = true ) {
		if ( !$label ) { $label = __( 'Tags:', 'tribe-events-calendar' ); }

		$tribe_ecp = TribeEvents::instance();
		$list = get_the_term_list( get_the_ID(), 'post_tag', '<dt>'.$label.'</dt><dd class="tribe-event-tags">', $separator, '</dd>' );
		$list = apply_filters( 'tribe_meta_event_tags', $list, $label, $separator, $echo );
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
	 * @param int|null $postId (optional)
	 * @param string|bool $meta name of the meta_key
	 * @param bool $single determines if the results should be a single item or an array of items.
	 * @return mixed meta value(s)
	 * @since 2.0
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ) {
		//$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getEventMeta( $postId, $meta, $single );
		return apply_filters( 'tribe_get_event_meta', $output, $postId, $meta, $single );
	}

	/**
	 * Event Category Name
	 *
	 * Return the current event category name based the url.
	 *
	 * @return string Name of the Event Category
	 * @since 2.0
	 */
	function tribe_meta_event_category_name() {
		$tribe_ecp = TribeEvents::instance();
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
	 * @since 2.0
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
	 * @return bool True if post type id Venue
	 * @since 2.0
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
	 * @return bool True if post type id Venue
	 * @since 2.0
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
	 * @since 2.0
	 */
	function tribe_events_before_html() {
		$before = stripslashes( tribe_get_option( 'tribeEventsBeforeHTML', '' ) );
		$before = wptexturize( $before );
		$before = convert_chars( $before );
		$before = wpautop( $before );
		$before = '<div class="tribe-events-before-html">'. stripslashes( shortcode_unautop( $before  ) ) .'</div>';
		$before = $before.'<span class="tribe-events-ajax-loading"><img class="tribe-events-spinner-medium" src="'.tribe_events_resource_url('images/tribe-loading.gif').'" alt="'.__('Loading Events', 'tribe-events').'" /></span>';
		$before = apply_filters( 'tribe_events_before_html', $before );

		echo apply_filters( 'tribe_events_before_html', $before );
	}

	/**
	 * HTML After Event (Display)
	 *
	 * Display HTML to output after the event template
	 *
	 * @since 2.0
	 */
	function tribe_events_after_html() {
		$after = stripslashes( tribe_get_option( 'tribeEventsAfterHTML', '' ) );
		$after = wptexturize( $after );
		$after = convert_chars( $after );
		$after = wpautop( $after );
		$after = '<div class="tribe-events-after-html">'. stripslashes( shortcode_unautop( $after ) ) .'</div>';

		echo apply_filters( 'tribe_events_after_html', $after );
	}

	/**
	 * Prints out classes on an event wrapper
	 *
	 * @return void
	 * @since 3.0
	 **/
	function tribe_events_event_classes() {
		$classes = apply_filters('tribe_events_event_classes', array());
		echo implode(' ', $classes);
	}

	/**
	 * Prints out data attributes used in the template header tags
	 *
	 * @param string|null $current_view
	 * @return void
	 * @since 3.0
	 **/
	function tribe_events_the_header_attributes( $current_view = null ) {
		$attrs = array();
		$current_view = !empty( $current_view ) ? $current_view : basename(tribe_get_current_template());
		$attrs['data-title'] = wp_title('&raquo;', false);
		switch($current_view) {
			case 'month.php' :
				$attrs['data-view'] = 'month';
				$attrs['data-date'] =  date( 'Y-m', strtotime( tribe_get_month_view_date() ) );
				$attrs['data-baseurl'] =  tribe_get_gridview_link(false);
			break;
			case 'list.php' :
				$attrs['data-view']= 'list';
				if ( tribe_is_upcoming() ) {
					$attrs['data-baseurl'] = tribe_get_listview_link(false);
				} elseif( tribe_is_past() ) {
					$attrs['data-view']= 'past';
					$attrs['data-baseurl'] = tribe_get_listview_past_link(false);
				}
			break;
		}
		$attrs = apply_filters('tribe_events_header_attributes', $attrs, $current_view);
		foreach ($attrs as $attr => $value) {
			echo " $attr=".'"'.$value.'"';
		}
	}

	/**
	 * Returns or echoes a url to a file in the Events Calendar plugin resources directory
	 *
	 * @param string $resource the filename of the resource
	 * @param bool $echo whether or not to echo the url
	 * @return string
	 * @author Jessica Yazbek
	 * @since 3.0
	 **/
	function tribe_events_resource_url($resource, $echo = false) {
		$url = apply_filters('tribe_events_resource_url', trailingslashit( TribeEvents::instance()->pluginUrl ).'resources/'.$resource, $resource);
		if ($echo) {
			echo $url;
		}
		return $url;
	}

	/**
	 * Return an array with the days of the week, numbered with respect to the start_of_week WP option
	 * @param string $format the display format for the days of the week
	 * @return array Days of the week.
	 * @since 3.0
	 **/
	function tribe_events_get_days_of_week( $format = null ) {
		if ( $format == 'short' ) {
			$days_of_week = TribeEvents::instance()->daysOfWeekShort;
		} else {
			$days_of_week = TribeEvents::instance()->daysOfWeek;
		}
		$start_of_week = get_option('start_of_week', 0);
		for ($i = 0; $i < $start_of_week; $i++) {
			$day = $days_of_week[$i];
			unset($days_of_week[$i]);
			$days_of_week[$i] = $day;
		}
		// $days_of_week = array_values($days_of_week);
		return apply_filters('tribe_events_get_days_of_week', $days_of_week);
	}

	/**
	 * conditional tag to determine if the cost field should be shown in the admin editors.
	 * @author tim@imaginesimplicity.com
	 * @since 3.0
	 * @return bool
	 */
	function tribe_events_admin_show_cost_field(){
		$modules = apply_filters( 'tribe_events_tickets_modules', NULL );
		$event_origin = get_post_meta( get_the_ID(), '_EventOrigin', true );
		$show_cost = empty( $modules ) ||
					 class_exists( 'Event_Tickets_PRO' ) ||
					 in_array( $event_origin, apply_filters( 'tribe_events_admin_show_cost_field_origin', array( 'community-events' ) ) );
		return apply_filters( 'tribe_events_admin_show_cost_field', $show_cost, $modules );
	}

	/**
	 * Get an event's cost
	 *
	 *
	 * @param null|int $postId (optional)
	 * @param bool $withCurrencySymbol Include the currency symbol
	 * @return string Cost of the event.
	 */
	function tribe_get_cost( $postId = null, $withCurrencySymbol = false ) {
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );

		$cost = tribe_get_event_meta( $postId, '_EventCost', true );

		if ( $cost === '' ) {
			$cost = '';
		} elseif ( $cost === '0' ) {
			$cost = __( "Free", 'tribe-events-calendar' );
		} else {
			$cost = esc_html( $cost );
		}

		if ( $withCurrencySymbol && is_numeric( $cost ) ) {
			$currency = tribe_get_event_meta( $postId, '_EventCurrencySymbol', true );

			if ( !$currency ) {
				$currency = tribe_get_option( 'defaultCurrencySymbol', '$' );
			}

			$cost = $currency . $cost;
		}

		return apply_filters( 'tribe_get_cost', $cost, $postId, $withCurrencySymbol );
	}

	/**
	 * Get the minimum cost of all events.
	 *
	 * @author PaulHughes01
	 * @since 2.1
	 * @return int the minimum cost.
	 */
	function tribe_get_minimum_cost() {
		global $wpdb;

		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';' );

		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		foreach ( $costs as $index => $value ) {
			$costs[$index] = preg_replace( '/^[^\d]+(\d+\.?\d*)?.*$/', '$1', $value );
		}
		if ( empty( $costs ) )
			$costs = array( '0' );

		$min = min( $costs );
		if ( $min == '' )
			$min = 0;

		return $min;
	}

	/**
	 * Get the maximum cost of all events.
	 *
	 * @author PaulHughes01
	 * @since 2.1
	 * @return int the maximum cost.
	 */
	function tribe_get_maximum_cost() {
		global $wpdb;

		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';' );

		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		foreach ( $costs as $index => $value ) {
			$costs[$index] = preg_replace( '/^[^\d]+(\d+\.?\d*)?.*$/', '$1', $value );
		}

		if ( empty( $costs ) )
			$costs = array( '0' );

		$max = max( $costs );
		if ( $max == '' )
			$max = 0;

		return $max;
	}

	/**
	 * Maps the cost array to make finding the minimum and maximum costs possible.
	 *
	 * @param array $costs
	 * @return array $costs
	 */
	function tribe_map_cost_array_callback( $costs ) {
		return $costs;
	}

	/**
	 * Event in Category Conditional
	 *
	 * Returns true if the event is in the specified catagory slug
	 *
	 * @param string $event_cat_slug
	 * @param int $event_id
	 * @return boolean
	 * @since 2.0.2
	 */
	function tribe_event_in_category( $event_cat_slug, $event_id = null ) {

		if ( empty( $event_id ) )
			$event_id = get_the_ID();

		$term = term_exists( $event_cat_slug, TribeEvents::TAXONOMY );

		if ( tribe_is_event( $event_id ) && is_object_in_term( $event_id, TribeEvents::TAXONOMY, array( $term['term_id'] ) ) ) {
			$return = true;
		}else {
			$return = false;
		}
		return apply_filters( 'tribe_event_in_category', $return );
	}

	/**
	 * Placeholder function that is used for ticketing plugins meant to be filtered by such plugins
	 *
	 * @author jkudish
	 * @since 2.1
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
	 * @since 2.0.5
	 * @author jkudish
	 * @link http://stackoverflow.com/questions/709669/how-do-i-remove-blank-lines-from-text-in-php
	 * @param string  $multi_line_string a multiline string
	 * @return string the same string without empty lines
	 */
	function tribe_multi_line_remove_empty_lines( $multi_line_string ) {
		return preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $multi_line_string );
	}

	/**
	 * return the featured image html to an event (within the loop automatically will get event ID)
	 *
	 * @since  3.0
	 * @param int     $post_id
	 * @param string  $size
	 * @return string
	 */
	function tribe_event_featured_image( $post_id = null, $size = 'full' ) {
		if ( is_null( $post_id ) )
			$post_id = get_the_ID();
		$image_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		$featured_image = '';
		if ( !empty( $image_src ) ) {
			$featured_image .= '<div class="tribe-events-event-image"><a href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'"><img src="'.  $image_src[0] .'" title="'. get_the_title( $post_id ) .'" /></a></div>';
		}
		return apply_filters( 'tribe_event_featured_image', $featured_image, $post_id, $size, $image_src );
	}

	/**
	 * show the recurring event info in a tooltip
	 *
	 * return the details of the start/end date/time
	 *
	 * @since  3.0
	 * @param int     $post_id
	 * @return string
	 */
	function tribe_events_event_recurring_info_tooltip( $post_id = null ) {
		if ( is_null( $post_id ) )
			$post_id = get_the_ID();
		$tooltip = '';
		if ( class_exists( 'TribeEventsPro' ) ) { // should this be a template tag?
			if ( tribe_is_recurring_event( $post_id ) ) {
				$tooltip .= '<div class="recurringinfo">';
				$tooltip .= '<div class="event-is-recurring">';
				$tooltip .= '<span class="tribe-events-divider">|</span>';
				$tooltip .= __( 'Recurring Event', 'tribe-events-calendar' );
				$tooltip .= sprintf(' <a href="%s">%s</a>',
					tribe_all_occurences_link( $post_id, false ),
					__( '(See all)', 'tribe-events-calendar' )
					);
				$tooltip .= '<div id="tribe-events-tooltip-'. $post_id .'" class="tribe-events-tooltip recurring-info-tooltip">';
				$tooltip .= '<div class="tribe-events-event-body">';
				$tooltip .= tribe_get_recurrence_text( $post_id );
				$tooltip .= '</div>';
				$tooltip .= '<span class="tribe-events-arrow"></span>';
				$tooltip .= '</div>';
				$tooltip .= '</div>';
				$tooltip .= '</div>';
			}
		}
		return apply_filters( 'tribe_events_event_recurring_info_tooltip', $tooltip );
	}

/**
	 * Return the details of the start/end date/time
	 *
	 * @since 3.0
	 * @param int|null $event
	 * @return string
	 */
	function tribe_events_event_schedule_details( $event = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) )
			$event = get_post( $event );

		$format = '';
		$timeFormat = get_option( 'time_format' );
		$microformatStartFormat = tribe_get_start_date( $event, false, 'Y-m-dTh:i' );
		$microformatEndFormat = tribe_get_end_date( $event, false, 'Y-m-dTh:i' );

		// If the WordPress date setting matches DATEONLYFORMAT, make the string more readable
		if ( get_option( 'date_format' ) == TribeDateUtils::DATEONLYFORMAT ) {
			/* If the event happens this year, no need to show the year, unless it ends on another year (multi-day) */
			if ( tribe_get_start_date( $event, false, 'Y' ) === date( 'Y' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
				$format = 'F j';
			}
		}

		$schedule = '';

		if ( tribe_event_is_multiday( $event ) ) { // multi-date event

			$format2ndday = $format;

			// If the WordPress date setting matches DATEONLYFORMAT, make the string more readable
			if ( get_option( 'date_format' ) == TribeDateUtils::DATEONLYFORMAT ) {
				//If it's all day and the end date is in the same month and year, just show the day.
				if ( tribe_event_is_all_day( $event ) && tribe_get_end_date( $event, false, 'm' ) === tribe_get_start_date( $event, false, 'm' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
					$format2ndday = 'j';
				}
			}

			if ( tribe_event_is_all_day( $event ) ) {
				// If the multi-day event begins and ends in the same month, just show the month once.
				if ( tribe_get_end_date( $event, false, 'm' ) === tribe_get_start_date( $event, false, 'm' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
					$schedule .= '<span class="date-start dtstart">';
					$schedule .= tribe_get_start_date( $event, true, $format );
					$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
					$schedule .= '</span> - ';
					$schedule .= '<span class="date-end dtend">';
					$schedule .= tribe_get_end_date( $event, true, $format2ndday );
					$schedule .= '<span class="value-title" title="'. $microformatEndFormat .'"></span>';
					$schedule .= '</span>';
				} else {
					$schedule .= '<span class="date-start dtstart">';
					$schedule .= tribe_get_start_date( $event, true, $format );
					$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
					$schedule .= '</span> - ';
					$schedule .= '<span class="date-end dtend">';
					$schedule .= tribe_get_end_date( $event, true, $format2ndday );
					$schedule .= '<span class="value-title" title="'. $microformatEndFormat .'"></span>';
					$schedule .= '</span>';
				}
			} else {
				$schedule .= '<span class="date-start dtstart">';
				$schedule .= tribe_get_start_date( $event, false, $format ) . ' @ ' . tribe_get_start_date( $event, false, $timeFormat );
				$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
				$schedule .= '</span> - ';
				$schedule .= '<span class="date-end dtend">';
				$schedule .= tribe_get_end_date( $event, false, $format2ndday ) . ' @ ' . tribe_get_end_date( $event, false, $timeFormat );
				$schedule .= '<span class="value-title" title="'. $microformatEndFormat .'"></span>';
				$schedule .= '</span>';
			}


		} elseif ( tribe_event_is_all_day( $event ) ) { // all day event
			$schedule .= '<span class="date-start dtstart">';
			$schedule .=  tribe_get_start_date( $event, true, $format );
			$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
			$schedule .= '</span>';
		} else { // single day event
			if ( tribe_get_start_date( $event, false, 'g:i A' ) === tribe_get_end_date( $event, false, 'g:i A' ) ) { // Same start/end time
				$schedule .= '<span class="date-start dtstart">';
				$schedule .= tribe_get_start_date( $event, false, $format ) . ' @ ' . tribe_get_start_date( $event, false, $timeFormat );
				$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
				$schedule .= '</span>';
			} else { // defined start/end time
				$schedule .= '<span class="date-start dtstart">';
				$schedule .= tribe_get_start_date( $event, false, $format ) . ' @ ' . tribe_get_start_date( $event, false, $timeFormat );
				$schedule .= '<span class="value-title" title="'. $microformatStartFormat .'"></span>';
				$schedule .= '</span> - ';
				$schedule .= '<span class="end-time dtend">';
				$schedule .= tribe_get_end_date( $event, false, $timeFormat ) . '<span class="value-title" title="'. $microformatEndFormat .'"></span>';
				$schedule .= '</span>';
			}
		}

		return apply_filters( 'tribe_events_event_schedule_details', $schedule );
	}

	/**
	 * Accepts two dates and returns the number of days between them
	 *
	 * @param string $start_date
	 * @param string $end_date
	 * @return int
	 * @author Modern Tribe
	 * @see TribeEventsQuery::dateDiff()
	 **/
	function tribe_get_days_between( $start_date, $end_date ) {

		$start_date = new DateTime( $start_date );
		$end_date   = new DateTime( $end_date );
		//      This doesn't work on php 5.2
		//  $interval = $start_date->diff($end_date);

		return TribeEventsQuery::dateDiff( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );
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
	 * @param array $args Args to be passed to TribeEventsQuery::getEvents()
	 * @param bool $initialize Whether the list view template class needs to be included and initialized
	 * @return string
	 * @author Modern Tribe
	 **/
	function tribe_include_view_list( $args = null, $initialize = true ) {
		global $wp_query;

		// hijack the main query to load the events via provided $args
		if ( !is_null( $args ) || ! ( $wp_query->tribe_is_event || $wp_query->tribe_is_event_category ) ) {
			$reset_q = $wp_query;
			$wp_query = TribeEventsQuery::getEvents( $args, true );
		}

		// single-event notices are jumping in on this init when loading as a module
		TribeEvents::removeNotice( 'event-past' );

		// get the list view template
		ob_start();
		if ( $initialize ) {
			tribe_initialize_view('Tribe_Events_List_Template');
		}
		tribe_get_view('list/content');
		$list_view_html = ob_get_clean();

		// fix the error of our ways
		if ( !empty( $reset_q ) ) {
			$wp_query = $reset_q;
		}

		// return the parsed template
		return $list_view_html;
	}

	/**
	 * Generates html for any notices that have been queued on the current view
	 *
	 * @param bool $echo Whether or not to echo the notices html
	 * @return void | string
	 * @author Modern Tribe
	 * @see TribeEvents::getNotices()
	 **/
	function tribe_events_the_notices( $echo = true ) {
		$notices = TribeEvents::getNotices();

		$html = !empty( $notices ) ? '<div class="tribe-events-notices"><ul><li>' . implode( '</li><li>', $notices ) . '</li></ul></div>' : '';
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
	 * @return array
	 * @author Modern Tribe
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
	 * @author Modern Tribe
	 **/
	function tribe_events_disabled_views() {
		static $disabled;
		if ( isset( $disabled ) ) {
			return $disabled;
		}
		$views = apply_filters( 'tribe-events-bar-views', array(), false );
		$enabled = tribe_events_enabled_views( $views );
		$disabled = array();
		foreach ( $views as $view ) {
			if ( !in_array( $view['displaying'], $enabled ) ) {
				if ( $view['displaying'] == 'upcoming' )
					$disabled[] = 'past';
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
		if ( empty( $user_agent ) )
			return apply_filters( 'tribe_is_bot_status', true, $user_agent, null );

		// declare known bot user agents (lowercase)
		$user_agent_bots = (array) apply_filters( 'tribe_is_bot_list', array( 'bot', 'slurp', 'spider', 'crawler', 'yandex' ) );

		foreach( $user_agent_bots as $bot ){
			if( stripos($user_agent, $bot) !== false )
				return apply_filters( 'tribe_is_bot_status', true, $user_agent, $bot );
		}

		// we think this is probably a real human
		return apply_filters( 'tribe_is_bot_status', false, $user_agent, null );
	}

	/**
	 * Display the Events Calendar promo banner
	 *
	 * @param bool $echo Whether or not to echo the banner, if false, it's returned
	 * @return void|string
	 **/
	function tribe_events_promo_banner( $echo = true ){
		if ( tribe_get_option( 'donate-link', false ) == true && !tribe_is_bot() ) {
			$promo = apply_filters( 'tribe_events_promo_banner_message', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="' . TribeEvents::$tribeUrl . 'wordpress-events-calendar/?utm_medium=plugin-tec&utm_source=banner&utm_campaign=in-app">', '</a>' ) );
			$html = apply_filters( 'tribe_events_promo_banner', sprintf( '<p class="tribe-events-promo">%s</p>', $promo ), $promo );
			if( $echo ) {
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
	 * @author Jessica Yazbek
	 * @since 3.0
	 **/
	function tribe_events_get_filters()	{
		return apply_filters('tribe-events-bar-filters', array());
	}

	/**
	 * Return the views registered in the tribe bar
	 *
	 * @return array
	 * @author Jessica Yazbek
	 * @since 3.0
	 **/
	function tribe_events_get_views()	{
		return apply_filters('tribe-events-bar-views', array());
	}

	/**
	 * Count keys in a hierarchical array
	 *
	 * @param $value
	 * @param $key
	 */
	function tribe_count_hierarchical_keys( $value, $key ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment++;
	}

	/**
	 * Count items in a hierarchical array
	 *
	 * @param array $walk
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
	 * @since 3.0.2
	 */
	function tribe_events_get_tab_index() {
		$tribe_events = TribeEvents::instance();
		return apply_filters( 'tribe_events_tab_index', $tribe_events->tabIndex() );
	}

	/**
	 * Echo and increment tab index in form fields
	 *
	 * @since 3.0.2
	 */
	function tribe_events_tab_index() {
		echo tribe_events_get_tab_index();
	}

	/**
	 * Check if a particular view is enabled
	 *
	 * @param string $view Name of view to check, should match what's in TribeEvents->displaying when on that view
	 * @return bool
	 * @author Jessica Yazbek
	 * @since 3.1
	 **/
	function tribe_events_is_view_enabled( $view ) {
		$enabled = false;
		$enabled_views = apply_filters( 'tribe-events-bar-views', array() );
		foreach ( $enabled_views as $enabled_view ) {
			if ( $enabled_view['displaying'] == $view ) {
				$enabled = true;
				break;
			}
		}
		return apply_filters( 'tribe_events_is_view_enabled', $enabled, $view, $enabled_views );
	}

}
