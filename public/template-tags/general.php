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
		return $tribe_ecp->getOption($optionName, $default);
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
	 * Event Categories (Display)
	 *
	 * Display the event categories
	 *
	 * @param string $label
	 * @param string $separator
	 * @uses the_terms()
	 * @since 2.0
	 */	
	function tribe_meta_event_cats( $label=null, $separator=', ')  {
		if( !$label ) { $label = __('Category:', 'tribe-events-calendar'); }

		$tribe_ecp = TribeEvents::instance();

		$list = apply_filters('tribe_meta_event_cats', get_the_term_list( get_the_ID(), $tribe_ecp->get_event_taxonomy(), '<dt class="category-label">'.$label.'</dt><dd class="category-meta">', $separator, '</dd>' ));

		echo $list;
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
		$list = apply_filters('tribe_meta_event_tags', get_the_term_list( get_the_ID(), 'post_tag', '<dt class="tribe-tag-label">'.$label.'</dt><dd class="tribe-tag-meta">', $separator, '</dd>' ));

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
	 * - If the event is registered in eventbrite, and has one ticket. Return the cost of that ticket.
	 * - If the event is registered in eventbrite, and there are many tickets, return "Varies"
	 *   - If the event is not registered in eventbrite, and there is meta, return that.
	 *   - If the event is not registered in eventbrite, and there is no meta, return ""
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

}
?>