<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions for use in WordPress templates.
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
	 * Link to Next Month
	 * 
	 * Returns a link to the next month's events page. Used in the grid view.
	 *
	 * @return string URL 
	 * @since 2.0
	 */
	function tribe_get_next_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->nextMonth(tribe_get_month_view_date() ));
		return $output;
	}

	/**
	 * Link to Previous Month
	 * 
	 * Returns a link to the previous month's events page. Used in the grid view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_previous_month_link()  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->previousMonth( tribe_get_month_view_date() ));
		return $output;
	}

	/**
	 * Link to Upcoming Events
	 * 
	 * Returns a link to the upcoming events in list view. Used in the loop view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_upcoming_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('upcoming');
		return $output;
	}
	
	/**
	 * Link to Past Events
	 * 
	 * Returns a link to the previous events in list view. Used in the loop view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past');
		return $output;
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
	 * Drop Menu Post Link
	 *
	 * Returns the URL where the jump menu sends the month/year request.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_dropdown_link_prefix()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('dropdown');
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
	function tribe_get_event_link($post = null) {
		return apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink('single', $post), $post );
	}		

	/**
	 * Link to This Month
	 *
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_this_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			$output = $tribe_ecp->getLink( 'month', $tribe_ecp->date );
			return $output;
		}
		return false;
	}

}
?>