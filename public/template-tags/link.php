<?php
/**
 * Link Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Link Event Day
	 *
	 * @param string $date 
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_day_link($date) {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getLink('day', $date);		
	}
	
	/**
	 * Link to Previous Event (Display)
	 *
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param string $anchor link text. Use %title% to place the post title in your string.
	 * @since 2.0
	 */
	function tribe_previous_event_link( $anchor = false )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		echo $tribe_ecp->get_event_link($post,'previous',$anchor);
	}

	/**
	 * Link to Next Event (Display)
	 * 
	 * Display a link to the next post by start date for the given event
	 *
	 * @param string $anchor link text. Use %title% to place the post title in your string.
	 * @since 2.0
	 */
	function tribe_next_event_link( $anchor = false )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		echo $tribe_ecp->get_event_link($post, 'next',$anchor);
	}

	/**
	 * Link to All Events
	 *
	 * Returns a link to the events URL
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_events_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('home');
		return $output;
	}

	/**
	 * Link to Grid View
	 *
	 * Returns a link to the general or category calendar grid view
	 *
	 * @param string $term Optional event category to link to.
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_gridview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('month', false, $term);
		return $output;
	}

	/**
	 * Link to List View
	 *
	 * Returns a link to the general or category upcoming view
	 *
	 * @param string $term Optional event category to link to.
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_listview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('upcoming', false, $term);
		return $output;
	}
	
	/**
	 * Link to List View (Past)
	 *
	 * Returns a link to the general or category past view
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_listview_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past');
		return $output;
	}

	/**
	 * Single Event Link (Display)
	 *
	 * Display link to a single event
	 *
	 * @param int $postId (optional)
	 * @since 2.0
	 */
	function tribe_event_link($post = null) {
		// pass in whole post object to retain start date
		echo tribe_get_event_link($post);
	}	

	/**
	 * Single Event Link
	 *
	 * Get link to a single event
	 * 
	 * @param int $postId (optional)
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_event_link($event = null) {
		if ( '' == get_option('permalink_structure') ) {
			return apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink('single', $event), $event );
		} else {
			return trailingslashit( apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink('single', $event), $event ) );
		}
	}

}
?>
