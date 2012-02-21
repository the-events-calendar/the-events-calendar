<?php
/**
 * Loop Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * New Day Test
	 *
	 * Called inside of the loop, returns true if the current post's meta_value (EventStartDate)
	 * is different than the previous post. Will always return true for the first event in the loop.
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_new_event_day()  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$retval = false;
		$now = time();
		if(isset($post->EventStartDate)) {
			$postTimestamp = strtotime( $post->EventStartDate, $now );
			$postTimestamp = strtotime( date( TribeDateUtils::DBDATEFORMAT, $postTimestamp ), $now); // strip the time
			if ( $postTimestamp != $tribe_ecp->currentPostTimestamp ) {
				$retval = true;
			}
			$tribe_ecp->currentPostTimestamp = $postTimestamp;
			return $retval;
		} else {
			return true;
		}
	}

	/**
	 * Single Day Test
	 *
	 * Returns true if the query is set for single day, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_day()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'day') ? true : false;
	}


	/**
	 * Past Loop View Test
	 *
	 * Returns true if the query is set for past events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_past()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'past') ? true : false;
	}

	/**
	 * Upcoming Loop View Test
	 *
	 * Returns true if the query is set for upcoming events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_upcoming()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'upcoming') ? true : false;
	}
	
	/**
	 * Show All Test
	 *
	 * Returns true if the query is set to show all events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_showing_all()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'all') ? true : false;		
	}

	/**
	 * Date View Test
	 *
	 *  Check if current display is "bydate"
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_by_date() {
		$tribe_ecp = TribeEvents::instance();
		return ( $tribe_ecp->displaying == 'bydate' ) ? true : false;
	}

	/**
	 * Event Title (Display)
	 *
	 * Display an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 * @since 2.0
	 */ 
	function tribe_events_title( $depth = true )  {
		echo tribe_get_events_title( $depth );
	}
	
	/**
	 * Event Title
	 *
	 * Return an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 * @return string title
	 * @since 2.0
	 */
	function tribe_get_events_title( $depth = true )  {
		$tribe_ecp = TribeEvents::instance();

		$title = __('Calendar of Events', 'tribe-events-calendar');

		if ( tribe_is_upcoming() ) {
			$title = __('Upcoming Events', 'tribe-events-calendar');
		}

		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
			$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
			if ( $depth ) {
				$title = '<a href="'.tribe_get_events_link().'">'.$title.'</a>';
				$title .= ' &#8250; ' . $cat->name;
			} else {
				$title = $cat->name;
			}
		}

		return apply_filters('tribe_get_events_title', $title);
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

}
?>