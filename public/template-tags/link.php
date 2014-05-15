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
	function tribe_get_day_link( $date = null ) {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_day_link', $tribe_ecp->getLink('day', $date), $date);
	}

	/**
	 * Day View Link
	 *
	 * Get a link to day view
	 *
	 * @param string $date
	 * @param string $day
	 * @return string HTML linked date
	 * @since 2.0
	 */
	function tribe_get_linked_day($date, $day) {
		$return = '';
		$return .= "<a href='" . tribe_get_day_link($date) . "'>";
		$return .= $day;
		$return .= "</a>";
		return apply_filters('tribe_get_linked_day', $return);
	}

	/**
	 * Output an html link to a day
	 *
	 * @param string $date 'previous day', 'next day', 'yesterday', 'tomorrow', or any date string that strtotime() can parse
	 * @param string $text text for the link
	 * @return void
	 * @since 3.0
	 **/
	function tribe_the_day_link( $date = null, $text = null ) {
		try {
			if ( is_null( $text ) ) {
				$text = tribe_get_the_day_link_label($date);
			}
			$date = tribe_get_the_day_link_date( $date );

			$link = tribe_get_day_link($date);

			$html = '<a href="'. $link .'" data-day="'. $date .'" rel="prev">'.$text.'</a>';
		} catch ( OverflowException $e ) {
			$html = '';
		}

		echo apply_filters( 'tribe_the_day_link', $html );
	}

	/**
	 * Get the label for the day navigation link
	 *
	 * @param string $date_description
	 * @return string
	 * @since 3.1.1
	 */
	function tribe_get_the_day_link_label( $date_description ) {
		switch ( strtolower( $date_description ) ) {
			case null :
				return __( 'Today', 'tribe-events-calendar-pro' );
			case 'previous day' :
				return __( '<span>&laquo;</span> Previous Day', 'tribe-events-calendar-pro' );
			case 'next day' :
				return __( 'Next Day <span>&raquo;</span>', 'tribe-events-calendar-pro' );
			case 'yesterday' :
				return __( 'Yesterday', 'tribe-events-calendar-pro' );
			case 'tomorrow' :
				return __( 'Tomorrow', 'tribe-events-calendar-pro' );
			default :
				return date_i18n( 'Y-m-d', strtotime( $date_description ) );
		}
	}


	/**
	 * Get the date for the day navigation link.
	 *
	 * @param string $date_description
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_the_day_link_date( $date_description ) {
		if ( is_null($date_description) ) {
			return TribeEventsPro::instance()->todaySlug;
		}
		if ( $date_description == 'previous day' ) {
			return tribe_get_previous_day_date(get_query_var('start_date'));
		}
		if ( $date_description == 'next day' ) {
			return tribe_get_next_day_date(get_query_var('start_date'));
		}
		return date('Y-m-d', strtotime($date_description) );
	}

	/**
	 * Get the next day's date
	 *
	 * @param string $start_date
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_next_day_date( $start_date ) {
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($start_date)) > '2037-12-30' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}
		$date = Date('Y-m-d', strtotime($start_date . " +1 day") );
		return $date;
	}

	/**
	 * Get the previous day's date
	 *
	 * @param string $start_date
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_previous_day_date( $start_date ) {
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($start_date)) < '1902-01-02' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}
		$date = Date('Y-m-d', strtotime($start_date . " -1 day") );
		return $date;
	}

	/**
	 * Link to Previous Event (Display)
	 *
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 * @return void
	 * @see tribe_get_prev_event_link()
	 * @since 2.1
	 */
	function tribe_the_prev_event_link( $anchor = false ){
		echo apply_filters('tribe_the_prev_event_link', tribe_get_prev_event_link( $anchor ));
	}

	/**
	 * Return a link to the previous post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 * @return string
	 * @since 2.1
	 */
	function tribe_get_prev_event_link( $anchor = false ){
		global $post;
		return apply_filters('tribe_get_next_event_link', TribeEvents::instance()->get_event_link($post,'previous',$anchor));
	}

	/**
	 * Link to Next Event (Display)
	 *
	 * Display a link to the next post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 * @return void
	 * @see tribe_get_next_event_link()
	 * @since 2.1
	 */
	function tribe_the_next_event_link( $anchor = false ){
		echo apply_filters('tribe_the_next_event_link', tribe_get_next_event_link( $anchor ));
	}

	/**
	 * Return a link to the next post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 * @return string
	 * @since 2.1
	 */
	function tribe_get_next_event_link( $anchor = false ){
		global $post;
		return apply_filters('tribe_get_next_event_link', TribeEvents::instance()->get_event_link($post,'next',$anchor));
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
		return apply_filters('tribe_get_events_link', $output);
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
		return apply_filters('tribe_get_gridview_link', $output);
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
		return apply_filters('tribe_get_listview_link', $output);
	}

	/**
	 * Link to List View (Past)
	 *
	 * Returns a link to the general or category past view
	 *
	 * @param int|null $term Term ID
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_listview_past_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past', false, $term);
		return apply_filters('tribe_get_listview_past_link', $output);
	}

	/**
	 * Single Event Link (Display)
	 *
	 * Display link to a single event
	 *
	 * @param null|int $post Optional post ID
	 * @return string Link html
	 * @since 2.0
	 */
	function tribe_event_link($post = null) {
		// pass in whole post object to retain start date
		echo apply_filters('tribe_event_link', tribe_get_event_link($post));
	}

	/**
	 * Single Event Link
	 *
	 * Get link to a single event
	 *
	 * @param int $event Optional post ID
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

	/**
	 * Event Website Link (more info)
	 *
	 * @param null|object|int $event
	 * @param null|string $label
	 * @return string $html
	 */
	function tribe_get_event_website_link( $event = null, $label = null ){
		$url = tribe_get_event_website_url($event);
		if( !empty($url) ) {
			$label = is_null($label) ? $url : $label;
			$html = sprintf('<a href="%s" target="%s">%s</a>',
				$url,
				apply_filters('tribe_get_event_website_link_target', 'self'),
				apply_filters('tribe_get_event_website_link_label', $label)
				);
		} else {
			$html = '';
		}
		return apply_filters('tribe_get_event_website_link', $html );
	}


	/**
	 * Event Website URL
	 *
	 * @param null|object|int $event
	 * @return string The event's website URL
	 */
	function tribe_get_event_website_url( $event = null ) {
		$post_id = ( is_object($event) && isset($event->tribe_is_event) && $event->tribe_is_event ) ? $event->ID : $event;
		$post_id = ( !empty($post_id) || empty($GLOBALS['post']) ) ? $post_id : get_the_ID();
		$url = tribe_get_event_meta( $post_id, '_EventURL', true );
		if ( !empty($url) ) {
			$parseUrl = parse_url($url);
			if (empty($parseUrl['scheme'])) {
				$url = "http://$url";
			}
		}
		return apply_filters( 'tribe_get_event_website_url', $url, $post_id );
	}

	/**
	 * Google Calendar Link
	 *
	 * Returns an "add to Google Calendar link for a single event. Must be used in the loop
	 *
	 * @param int $postId (optional)
	 * @return string URL for google calendar.
	 * @since 2.0
	 */
	function tribe_get_gcal_link( $postId = null )  {
		$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output = esc_url($tribe_ecp->googleCalendarLink( $postId ));
		return apply_filters('tribe_get_gcal_link', $output);
	}



}
?>