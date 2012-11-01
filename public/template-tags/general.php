<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Get Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $default Value to return if no such option is found.
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 * @since 2.0
	 */
	function tribe_get_option($optionName, $default = '')  {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_option', $tribe_ecp->getOption($optionName, $default));
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
	function tribe_update_option($optionName, $value)  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->setOption($optionName, $value);
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
	function tribe_is_event( $postId = null )  {
		return apply_filters('tribe_is_event', TribeEvents::instance()->isEvent($postId));
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
	function tribe_get_events( $args = '' )  {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_events', $tribe_ecp->getEvents( $args ));
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
	function tribe_get_all_day( $postId = null )  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = !! tribe_get_event_meta( $postId, '_EventAllDay', true );
		return apply_filters('tribe_get_all_day', $output);
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
	function tribe_is_multiday( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$start = (array)tribe_get_event_meta( $postId, '_EventStartDate', false );
		sort($start);
		$start = strtotime($start[0]);
		$end = strtotime(tribe_get_event_meta( $postId, '_EventEndDate', true ));
		$output = date('d-m-Y', $start) != date('d-m-Y', $end);
		return apply_filters('tribe_is_multiday', $output);
	}
	
	/**
 	 * Event Category ID's
 	 *
 	 * Display the event category ID as a class for events wrapper
 	 *
 	 * @uses wp_get_object_terms()
 	 * @since 2.1
 	 */    	
	function tribe_get_event_cat_ids( $post_id ) {

		if ( isset( $post_id ) ) {
			$post_id = get_the_ID();
		}
		
		$return_id = array();
		
		$tribe_cat_ids = wp_get_object_terms( $post_id, TribeEvents::TAXONOMY );
		if( !empty( $tribe_cat_ids ) ){
			if( !is_wp_error( $tribe_cat_ids ) ) {
				foreach( $tribe_cat_ids as $tribe_cat_id ) {
					$return_id[] = $tribe_cat_id->term_id;
				}
			}
		}
		return $return_id;
	}

	function tribe_get_event_taxonomy( $post_id = null, $args = array() ){
		$tribe_ecp = TribeEvents::instance();
		$defaults = array(
			'taxonomy' => $tribe_ecp->get_event_taxonomy(),
			'before' => '<li>',
			'sep' => '</li><li>',
			'after' => '</li>');
		$args = wp_parse_args( $args, $defaults);
		extract( $args, EXTR_SKIP );
		$taxonomy = get_the_term_list( $post_id, $taxonomy, $before, $sep, $after);
		return apply_filters( 'tribe_get_event_taxonomy', $taxonomy );
	}

	/**
	 * Event Categories (Display)
	 *
	 * Display the event categories with display param
	 *
	 * @uses tribe_get_event_taxonomy()
	 * @replaces tribe_meta_event_cats()
	 * @param string $post_id
	 * @param array $args
	 * @return $html (echo if provided in $args)
	 * @since 3.0
	 */	
	function tribe_get_event_categories( $post_id = null, $args = array() ){
		$post_id = is_null($post_id) ? get_the_ID() : $post_id;
		$defaults = array(
			'echo' => false,
			'label' => null,
			'label_before' => '<div>',
			'label_after' => '</div>',
			'wrap_before' => '<ul class="tribe-event-categories">',
			'wrap_after' => '</ul>');
		$args = wp_parse_args( $args, $defaults);
		$categories = tribe_get_event_taxonomy( $post_id, $args );

		// check for the occurances of links in the returned string
		$label = is_null( $args['label'] ) ? _n( 'Event Category', 'Event Categories', substr_count( $categories, "<a href" ), 'tribe-events-calendar') : $args['label'];

		$html = !empty( $categories ) ? sprintf('%s%s:%s %s%s%s',
			$args['label_before'],
			$label,
			$args['label_after'],
			$args['wrap_before'],
			$categories,
			$args['wrap_after']
			) : '';
		if( $args['echo'] ) {
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
	 * @param string $label
	 * @param string $separator
	 * @uses the_terms()
	 * @since 2.1
	 */
	function tribe_meta_event_tags( $label=null, $separator=', ')  {
		if( !$label ) { $label = __('Tags:', 'tribe-events-calendar'); }

		$tribe_ecp = TribeEvents::instance();
		$list = apply_filters('tribe_meta_event_tags', get_the_term_list( get_the_ID(), 'post_tag', '<dt>'.$label.'</dt><dd class="tribe-event-tags">', $separator, '</dd>' ));

		echo $list;
	}

	/**
	 * Event Post Meta
	 *
	 * Get event post meta.
	 *
	 * @param int $postId (optional)
	 * @param string $meta name of the meta_key
	 * @param bool $single determines if the results should be a single item or an array of items.
	 * @return mixed meta value(s)
	 * @since 2.0
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ){
		$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getEventMeta( $postId, $meta, $single );
		return apply_filters('tribe_get_event_meta', $output);
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
		$current_cat = get_query_var('tribe_events_cat');
		if($current_cat){
			$term_info = get_term_by('slug',$current_cat,$tribe_ecp->get_event_taxonomy());
			return apply_filters('tribe_meta_event_category_name', $term_info->name);
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
		return apply_filters('tribe_get_current_template', TribeEventsTemplates::get_current_page_template());
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
	function tribe_is_venue( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_is_venue', $tribe_ecp->isVenue($postId));
	}

	/**
	 * HTML Before Event (Display)
	 *
	 * Display HTML to output before the event template
	 *
	 * @since 2.0
	 */
	function tribe_events_before_html() {
		echo apply_filters('tribe_events_before_html', stripslashes(tribe_get_option('tribeEventsBeforeHTML')));
	}

	/**
	 * HTML After Event (Display)
	 *
	 * Display HTML to output after the event template
	 *
	 * @since 2.0
	 */
	function tribe_events_after_html() {
		echo apply_filters('tribe_events_after_html', stripslashes(tribe_get_option('tribeEventsAfterHTML')));
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
	 * @param int $postId (optional)
	 * @return string Cost of the event.
	 */
	function tribe_get_cost( $postId = null)  {
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );
		if( class_exists( 'Eventbrite_for_TribeEvents' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->tribe_get_cost($postId);
			if($returned) {
				return apply_filters( 'tribe_get_cost', esc_html($returned));
			}
		}

		$cost = tribe_get_event_meta( $postId, '_EventCost', true );

		if($cost === ''){
			$cost = '';
		}elseif($cost == '0'){
			$cost = __( "Free", 'tribe-events-calendar' );
		}else{
			$cost = esc_html($cost);
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
		
		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';');
		
		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		
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
		
		$costs = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_EventCost\';');
		
		$costs = array_map( 'tribe_map_cost_array_callback', $costs );
		
		$max = max( $costs );
		if ( $max == '' )
			$max = 0;
			
		return $max;
	}
	
	/**
	 * Maps the cost array to make finding the minimum and maximum costs possible.
	 *
	 * @param $costs
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
	 * @param string $event_cat_slug
	 * @param int $event_id
	 * @return boolean
	 * @since 2.0.2
	 */
	function tribe_event_in_category($event_cat_slug, $event_id = null){
	
		if( empty($event_id) )
			$event_id = get_the_ID();
		
		$term = term_exists($event_cat_slug, TribeEvents::TAXONOMY);
		
		if ( tribe_is_event($event_id) && is_object_in_term($event_id, TribeEvents::TAXONOMY, array( $term['term_id'] ) ) ) {
			$return = true;
		}else{
			$return = false;
		}
		return apply_filters('tribe_event_in_category', $return);
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
		$ticket_form = apply_filters('tribe_get_ticket_form', false);
		if ($ticket_form && is_string($ticket_form)) {
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
	 * @param string $multi_line_string a multiline string
	 * @return string the same string without empty lines
	 */
	function tribe_multi_line_remove_empty_lines( $multi_line_string ) {
		return preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $multi_line_string );
	}

	/**
	 * return the featured image html to an event (within the loop automatically will get event ID)
	 * @since  3.0
	 * @param  int $post_id
	 * @param  string $size
	 * @return string
	 */
	function tribe_event_featured_image( $post_id = null, $size = 'full' ){
		if( is_null( $post_id ))
			$post_id = get_the_ID();
		$image_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );			
		$featured_image = '';
		if ( !empty($image_src) ) {
			$featured_image .= '<div class="tribe-events-event-image"><a href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'"><img src="'.  $image_src[0] .'" title="'. get_the_title( $post_id) .'" /></a></div>';
		}
		return apply_filter('tribe_event_featured_image', $featured_image);
	}

	/**
	 * show the recurring event info in a tooltip
	 * 
	 * return the details of the start/end date/time
	 * @since  3.0
	 * @param  int $post_id
	 * @return string
	 */
	function tribe_event_recurring_info_tooltip( $post_id = null ){
		if( is_null( $post_id ))
			$post_id = get_the_ID();
		$eventID = $post_id;
		$tooltip = '';
		if( class_exists( 'TribeEventsPro' ) )  { // should this be a template tag?
			if ( tribe_is_recurring_event() ) { 
				$tooltip .= '<span class="recurringinfo">';
				$tooltip .= '<div class="event-is-recurring">';
					$tooltip .= '[ <img src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/recurring-event-icon.png" /> event ]';
					$tooltip .= '<div id="tribe-events-tooltip-'. $eventID .'" class="tribe-events-tooltip recurring-info-tooltip">';
						$tooltip .= '<div class="tribe-events-event-body">';
							$tooltip .= tribe_get_recurrence_text();
						$tooltip .= '</div>';	
						$tooltip .= '<span class="tribe-events-arrow"></span>';	
					$tooltip .= '</div>';
				$tooltip .= '</div>';
			$tooltip .= '</span>';		
		 }
		}
		return $tooltip;
		return apply_filters('tribe_event_recurring_info_tooltip', $tooltip);
	}

	/** 
	 * return the details of the start/end date/time
	 * @since  3.0
	 * @param  int $post_id
	 * @return string
	 */
	function tribe_event_schedule_details( $post_id = null){
		if (is_null( $post_id ))
			$post_id = get_the_ID();
			$schedule = '<div class="tribe-event-schedule-details">';
			 if ( tribe_is_multiday( $post_id ) ) { // multi-date event 
				$schedule .= '<span class="date-start">'. tribe_get_start_date() .'</span> - <span class="date-end">'. tribe_get_end_date() .'</span>';
			 } elseif ( tribe_get_all_day( $post_id ) ) {  // all day event
				$schedule .= '<span class="date-start">'. tribe_get_start_date() .'</span>';
			} else { // single day event
					if ( tribe_get_start_date( $post_id, false, 'g:i A' ) == tribe_get_end_date( $post_id, false, 'g:i A' ) ) { // Same start/end time 
						$schedule .= '<span class="date-start">'. tribe_get_start_date($post_id, false) .'</span> @ <span class="start-time">'. tribe_get_start_date( $post_id, false, 'g:i A' ) .'</span>';
					 } else {  // defined start/end time
					  $schedule .= '<span class="date-start">'. tribe_get_start_date( $post_id, false ) .'</span> <span class="date-divider">|</span> <span class="start-time">'. tribe_get_start_date( $post_id, false, 'g:i A' ) .' - <span class="start-time">'. tribe_get_end_date( $post_id, false, 'g:i A' ) .'</span>';	
					 } 					
			}
			$schedule .= '</div>';			 
			return $schedule;
			return apply_filters('tribe_event_schedule_details', $schedule);
	}

	function tribe_get_days_between( $start_date, $end_date ){

		$start_date = new DateTime( $start_date );
		$end_date = new DateTime( $end_date );
		$interval = $start_date->diff($end_date);

		return $interval->days;
	}

	function tribe_include_view_list( $args = null ){
		global $wp_query;

		// hijack the main query to load the events via provided $args
		if( !is_null($args) ) {
			$reset_q = $wp_query;
			$wp_query = TribeEventsQuery::getEvents( $args, true );
		}

		// get the list view template
		ob_start();
		include apply_filters( 'tribe_include_view_list', TribeEventsTemplates::getTemplateHierarchy('list') );
		$list_view_html = ob_get_clean();

		// fix the error of our ways
		if( !is_null($args) ) {
			$wp_query = $reset_q;
		}

		// return the parsed template
		return $list_view_html;
	}
		
}
