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
	 * tribe_get_object_property_from_array loop through an array of objects to retrieve a single property
	 *
	 * @param array   $array_objects
	 * @param string  $property
	 * @return array
	 */
	function tribe_get_object_property_from_array( $array_objects = array(), $property = null ) {
		$array_properties = array();
		if ( !empty( $array_objects ) && !empty( $property ) ) {
			if ( ! is_wp_error( $array_objects ) ) {
				// loop through array of objects to pick out property
				foreach ( $array_objects as $object ) {
					if ( !isset( $object->{$property} ) )
						continue;
					$array_properties[] = $object->{$property};
				}
			} else {
				// return $array_objects if WP_Error to pass the error through
				return $array_objects;
			}
		}
		return $array_properties;
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
		return apply_filters( 'tribe_get_option', $tribe_ecp->getOption( $optionName, $default ) );
	}

	/**
	 * Update Option
	 *
	 * Set specific key from options array, optionally provide a default return value
	 *
	 * @param string  $optionName Name of the option to retrieve.
	 * @param string  $value      Value to save
	 * @return void
	 * @since 2.0.3
	 */
	function tribe_update_option( $optionName, $value ) {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->setOption( $optionName, $value );
	}

	/**
	 * Get Network Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @param string  $optionName Name of the option to retrieve.
	 * @param string  $default    Value to return if no such option is found.
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
	 * @param int     $postId (optional)
	 * @return bool true if this post is an Event post type
	 * @since 2.0
	 */
	function tribe_is_event( $postId = null ) {
		return apply_filters( 'tribe_is_event', TribeEvents::instance()->isEvent( $postId ) );
	}

	/**
	 * Get Event
	 *
	 * Queries the events using WordPress get_posts() by setting the post type and sorting by event date.
	 *
	 * @param array   $args query vars with added defaults including post_type of events, sorted (orderby) by event date (order) ascending
	 * @return array List of posts.
	 * @link http://codex.wordpress.org/Template_Tags/get_posts
	 * @link http://codex.wordpress.org/Function_Reference/get_post
	 * @uses get_posts()
	 * @see get_posts()
	 * @since 2.0
	 */
	function tribe_get_events( $args = '' ) {
		return apply_filters( 'tribe_get_events', TribeEventsQuery::getEvents( $args ) );
	}

	/**
	 * All Day Event Test
	 *
	 * Returns true if the event is an all day event
	 *
	 * @param int     $postId (optional)
	 * @return bool
	 * @since 2.0
	 */
	function tribe_get_all_day( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = !! tribe_get_event_meta( $postId, '_EventAllDay', true );
		return apply_filters( 'tribe_get_all_day', $output );
	}

	/**
	 * Multi-day Event Test
	 *
	 * Returns true if the event spans multiple days
	 *
	 * @param int     $postId (optional)
	 * @return bool true if event spans multiple days
	 * @since 2.0
	 */
	function tribe_is_multiday( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$start = (array)tribe_get_event_meta( $postId, '_EventStartDate', false );
		sort( $start );
		$start = strtotime( $start[0] );
		$end = strtotime( tribe_get_event_meta( $postId, '_EventEndDate', true ) );
		$output = date( 'd-m-Y', $start ) != date( 'd-m-Y', $end );
		return apply_filters( 'tribe_is_multiday', $output );
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
		return (array) tribe_get_object_property_from_array( get_the_terms( $post_id, TribeEvents::TAXONOMY ), 'term_id' );
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
		return (array) tribe_get_object_property_from_array( get_the_terms( $post_id, TribeEvents::TAXONOMY ), 'slug' );
	}

	function tribe_get_event_taxonomy( $post_id = null, $args = array() ) {
		$tribe_ecp = TribeEvents::instance();
		$defaults = array(
			'taxonomy' => $tribe_ecp->get_event_taxonomy(),
			'before' => '<li>',
			'sep' => '</li><li>',
			'after' => '</li>' );
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		$taxonomy = get_the_term_list( $post_id, $taxonomy, $before, $sep, $after );
		return apply_filters( 'tribe_get_event_taxonomy', $taxonomy );
	}

	/**
	 * Event Categories (Display)
	 *
	 * Display the event categories with display param
	 *
	 * @uses tribe_get_event_taxonomy()
	 * @replaces tribe_meta_event_cats()
	 * @param string  $post_id
	 * @param array   $args
	 * @return $html (echo if provided in $args)
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
			echo apply_filters( 'tribe_get_event_categories', $html );
		} else {
			return apply_filters( 'tribe_get_event_categories', $html );
		}
	}

	/**
	 * Event Tags (Display)
	 *
	 * Display the event tags
	 *
	 * @param string  $label
	 * @param string  $separator
	 * @uses the_terms()
	 * @since 2.1
	 */
	function tribe_meta_event_tags( $label=null, $separator=', ', $echo = true ) {
		if ( !$label ) { $label = __( 'Tags:', 'tribe-events-calendar' ); }

		$tribe_ecp = TribeEvents::instance();
		$list = apply_filters( 'tribe_meta_event_tags', get_the_term_list( get_the_ID(), 'post_tag', '<dt>'.$label.'</dt><dd class="tribe-event-tags">', $separator, '</dd>' ) );
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
	 * @param int     $postId (optional)
	 * @param string  $meta   name of the meta_key
	 * @param bool    $single determines if the results should be a single item or an array of items.
	 * @return mixed meta value(s)
	 * @since 2.0
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ) {
		//$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getEventMeta( $postId, $meta, $single );
		return apply_filters( 'tribe_get_event_meta', $output );
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
			return apply_filters( 'tribe_meta_event_category_name', $term_info->name );
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
	 * @param int     $postId (optional)
	 * @return bool True if post type id Venue
	 * @since 2.0
	 */
	function tribe_is_venue( $postId = null ) {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters( 'tribe_is_venue', $tribe_ecp->isVenue( $postId ) );
	}

	/**
	 * HTML Before Event (Display)
	 *
	 * Display HTML to output before the event template
	 *
	 * @since 2.0
	 */
	function tribe_events_before_html() {
		echo apply_filters( 'tribe_events_before_html', stripslashes( tribe_get_option( 'tribeEventsBeforeHTML' ) ) );
	}

	/**
	 * HTML After Event (Display)
	 *
	 * Display HTML to output after the event template
	 *
	 * @since 2.0
	 */
	function tribe_events_after_html() {
		echo apply_filters( 'tribe_events_after_html', stripslashes( tribe_get_option( 'tribeEventsAfterHTML' ) ) );
	}

	/**
	 * Event Cost
	 *
	 * If EventBrite plugin is active
	 * - If the event is registered in Eventbrite, and has one ticket. Return the cost of that ticket.
	 * - If the event is registered in Eventbrite, and there are many tickets, return "Varies"
	 *   - If the event is not registered in Eventbrite, and there is meta, return that.
	 *   - If the event is not registered in Eventbrite, and there is no meta, return ""
	 *
	 * @param int     $postId (optional)
	 * @return string Cost of the event.
	 */
	function tribe_get_cost( $postId = null, $withCurrencySymbol = false ) {
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );
		if ( class_exists( 'Eventbrite_for_TribeEvents' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->tribe_get_cost( $postId );
			if ( $returned ) {
				return apply_filters( 'tribe_get_cost', esc_html( $returned ) );
			}
		}

		$cost = tribe_get_event_meta( $postId, '_EventCost', true );

		if ( $cost === '' ) {
			$cost = '';
		}elseif ( $cost == '0' ) {
			$cost = __( "Free", 'tribe-events-calendar' );
		}else {
			$cost = esc_html( $cost );
		}

		if ( $withCurrencySymbol && is_numeric( $cost ) ) {
			$currency = tribe_get_event_meta( $postId, '_EventCurrencySymbol', true );

			if ( !$currency ) {
				$currency = tribe_get_option( 'defaultCurrencySymbol', '$' );
			}

			$cost = $currency . $cost;
		}

		return apply_filters( 'tribe_get_cost', $cost );
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
	 * @param unknown $costs
	 * @return $costs
	 */
	function tribe_map_cost_array_callback( $costs ) {
		return $costs;
	}

	/**
	 * Event in Category Conditional
	 *
	 * Returns true if the event is in the specified catagory slug
	 *
	 * @param string  $event_cat_slug
	 * @param int     $event_id
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
	 * Placeholder function that is used for ticketing plugins
	 * meant to be filtered by such plugins
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
		return apply_filters( 'tribe_event_featured_image', $featured_image );
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
				$tooltip .= '<span class="recurringinfo">';
				$tooltip .= '<div class="event-is-recurring">';
				$tooltip .= '<span class="tribe-divider">|</span>';
				$tooltip .= __( 'Recurring Event', 'tribe-events-calendar' );
				$tooltip .= '<div id="tribe-events-tooltip-'. $post_id .'" class="tribe-events-tooltip recurring-info-tooltip">';
				$tooltip .= '<div class="tribe-events-event-body">';
				$tooltip .= tribe_get_recurrence_text( $post_id );
				$tooltip .= '</div>';
				$tooltip .= '<span class="tribe-events-arrow"></span>';
				$tooltip .= '</div>';
				$tooltip .= '</div>';
				$tooltip .= '</span>';
			}
		}
		return $tooltip;
		return apply_filters( 'tribe_events_event_recurring_info_tooltip', $tooltip );
	}

	/**
	 * return the details of the start/end date/time
	 *
	 * @since  3.0
	 * @param int     $post_id
	 * @return string
	 */
	function tribe_events_event_schedule_details( $event = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) )
			$event = get_post( $event );

		if ( !isset( $event->EventStartDate ) )
			$event->EventStartDate = tribe_get_start_date( $event );

		if ( !isset( $event->EventEndDate ) )
			$event->EventEndDate = tribe_get_end_date( $event );

		$format = '';
		$timeFormat = get_option( 'time_format' );

		// If the WordPress date setting matches DATEONLYFORMAT, make the string more readable
		if ( get_option( 'date_format' ) == TribeDateUtils::DATEONLYFORMAT ) {
			/* If the event happens this year, no need to show the year, unless it ends on another year (multi-day) */
			if ( tribe_get_start_date( $event, false, 'Y' ) === date( 'Y' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
				$format = 'F j';
			}
		}

		$schedule = '<div class="tribe-events-event-schedule-details">';

		if ( tribe_is_multiday( $event ) ) { // multi-date event

			$format2ndday = $format;

			// If the WordPress date setting matches DATEONLYFORMAT, make the string more readable
			if ( get_option( 'date_format' ) == TribeDateUtils::DATEONLYFORMAT ) {
				//If it's all day and the end date is in the same month and year, just show the day.
				if ( tribe_get_all_day( $event ) && tribe_get_end_date( $event, false, 'm' ) === tribe_get_start_date( $event, false, 'm' ) && tribe_get_end_date( $event, false, 'Y' ) === date( 'Y' ) ) {
					$format2ndday = 'j';
				}
			}

			$schedule .= '<span class="date-start">' . tribe_get_start_date( $event, true, $format ) . '</span> - <span class="date-end">' . tribe_get_end_date( $event, true, $format2ndday ) . '</span>';

		} elseif ( tribe_get_all_day( $event ) ) { // all day event
			$schedule .= '<span class="date-start">' . tribe_get_start_date( $event, true, $format ) . '</span>';
		} else { // single day event
			if ( tribe_get_start_date( $event, false, 'g:i A' ) === tribe_get_end_date( $event, false, 'g:i A' ) ) { // Same start/end time
				$schedule .= '<span class="date-start">' . tribe_get_start_date( $event, false, $format ) . '</span> @ <span class="start-time">' . tribe_get_start_date( $event, false, $timeFormat ) . '</span>';
			} else { // defined start/end time
				$schedule .= '<span class="date-start">' . tribe_get_start_date( $event, false, $format ) . '</span> @ <span class="start-time">' . tribe_get_start_date( $event, false, $timeFormat ) . '</span> - <span class="end-time">' . tribe_get_end_date( $event, false, $timeFormat ) . '</span>';
			}
		}

		$schedule .= '</div>';

		return apply_filters( 'tribe_events_event_schedule_details', $schedule );
	}

	function tribe_get_days_between( $start_date, $end_date ) {

		$start_date = new DateTime( $start_date );
		$end_date   = new DateTime( $end_date );
		//      This doesn't work on php 5.2
		//  $interval = $start_date->diff($end_date);

		return TribeEventsQuery::dateDiff( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );
	}

	function tribe_include_view_list( $args = null ) {
		global $wp_query;

		// hijack the main query to load the events via provided $args
		if ( !$wp_query->is_main_query() && ( !is_null( $args ) || ! ( $wp_query->tribe_is_event || $wp_query->tribe_is_event_category ) ) ) {
			$reset_q = $wp_query;
			$wp_query = TribeEventsQuery::getEvents( $args, true );
		}

		// single-event notices are jumping in on this init when loading as a module
		TribeEvents::removeNotice( 'event-past' );

		// remove promo banner since this will most likely be used on venue/organizer or some submodule
		add_filter( 'tribe_events_promo_banner', '__return_false' );

		// get the list view template
		ob_start();
		include apply_filters( 'tribe_include_view_list', TribeEventsTemplates::getTemplateHierarchy( 'list' ) );
		$list_view_html = ob_get_clean();

		// fix the error of our ways
		if ( !empty( $reset_q ) ) {
			$wp_query = $reset_q;
		}

		// return the parsed template
		return $list_view_html;
	}

	function tribe_events_the_notices( $echo = true ) {
		$notices = TribeEvents::getNotices();
		$html = !empty( $notices ) ? '<div class="tribe-event-notices"><ul><li>' . implode( '</li><li>', $notices ) . '</li></ul></div>' : '';
		$the_notices = apply_filters( 'tribe_events_the_notices', $html, $notices );
		if ( $echo ) {
			echo $the_notices;
		} else {
			return $the_notices;
		}
	}

	function tribe_events_enabled_views( $views ) {
		$enabled_views_defaults = array();
		foreach ( $views as $view ) {
			$enabled_views_defaults[] = $view['displaying'];
		}
		return tribe_get_option( 'tribeEnableViews', $enabled_views_defaults );
	}

	function tribe_events_disabled_views() {
		static $disabled = NULL;
		if ( isset( $disabled ) ) {
			return $disabled;
		}
		$views = apply_filters( 'tribe-events-bar-views', array(), FALSE );
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

	function tribe_count_hierarchical_keys( $value, $key ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment++;
	}
	function tribe_count_hierarchical( array $walk ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment = 0;
		array_walk_recursive( $walk, 'tribe_count_hierarchical_keys' );
		return $tribe_count_hierarchical_increment;
	}

	/**
	 * tribe_is_bot checks if the visitor is a bot and returns status
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

	function tribe_events_promo_banner( $echo = true ){
		if ( tribe_get_option( 'donate-link', false ) == true && !tribe_is_bot() ) {
			$promo = apply_filters( 'tribe_events_promo_banner_message', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="http://tri.be/wordpress-events-calendar/">', '</a>' ) );
			$html = apply_filters( 'tribe_events_promo_banner', sprintf( '<p class="tribe-events-promo">%s</p>', $promo ), $promo );
			if( $echo ) {
				echo $html;
			} else {
				return $html;
			}
		}
	}

}
