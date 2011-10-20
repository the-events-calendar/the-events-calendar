<?php

/**************************************************
 * TABLE OF CONTENTS
 * General Template Tags
 * Calendar View Template Tags
 * Loop Template Tags
 * Google Map Template Tags
 * Organizer Template Tags
 * Venue (& Address) Template Tags
 * Date Template Tags
 * Link Template Tags
 **************************************************/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) && !function_exists( 'tribe_get_option' ) ) {
	
	
	/**************************************************
	 * SECTION: General Template Tags
	 **************************************************/

	/**
	 * retrieve specific key from options array, optionally provide a default return value
	 * @since 2.0
	 */
	function tribe_get_option($optionName, $default = '')  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getOption($optionName, $default);
	}

	/**
	 * Checks type of $postId to determine if it is an event
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_event( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->isEvent($postId);
	}
	
	/**
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
		return $tribe_ecp->getEvents( $args );
	}

	/**
	 * Returns true if the event is an all day event
	 *
	 * @param int $postId (optional)
	 * @return bool
	 * @since 2.0
	 */
	function tribe_get_all_day( $postId = null )  {
		$postId = TribeEvents::postIdHelper( $postId );
		return !! tribe_get_event_meta( $postId, '_EventAllDay', true );
	}
	
	/**
	 * Returns true if the event spans multiple days
	 *
	 * @param int $postId (optional)
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_multiday( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$start = (array)tribe_get_event_meta( $postId, '_EventStartDate', false );
		sort($start);
		$start = strtotime($start[0]);
		$end = strtotime(tribe_get_event_meta( $postId, '_EventEndDate', true ));
		return date('d-m-Y', $start) != date('d-m-Y', $end);
	}

	/**
	 * Echo the event categories
	 *
	 * @param string $label
	 * @param string $separator
	 * @uses the_terms()
	 * @since 2.0
	 */	
	function tribe_meta_event_cats( $label=null, $separator=', ')  {
		if( !$label ) { $label = __('Category:', 'tribe-events-calendar'); }

		$tribe_ecp = TribeEvents::instance();
		the_terms( get_the_ID(), $tribe_ecp->get_event_taxonomy(), '<dt>'.$label.'</dt><dd>', $separator, '</dd>' );
	}

	/**
	 * Get event post meta.
	 *
	 * @param string $postId 
	 * @param string $meta 
	 * @param string $single 
	 * @return string meta value
	 * @since 2.0
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ){
		$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getEventMeta( $postId, $meta, $single );
	}
	
	/**
	 * Return the current event category name
	 *
	 * @since 2.0
	 */ 
	function tribe_meta_event_category_name() {
		$tribe_ecp = TribeEvents::instance();
		$current_cat = get_query_var('tribe_events_cat');
		if($current_cat){
			$term_info = get_term_by('slug',$current_cat,$tribe_ecp->get_event_taxonomy());
			return $term_info->name;
		}
	}
	
	/**
	 * Is this event recurring
	 * 
	 * @since 2.0
	 */
	function tribe_is_recurring_event( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );
		return sizeof(get_post_meta($postId, '_EventStartDate')) > 1;
	}
		
	/**
	 * Get the current page template that we are on
	 * @since 2.0
	 */
	function tribe_get_current_template() {
		return TribeEventsTemplates::get_current_page_template();
	}

	/**
	 * Is this postId a venue?
	 * @since 2.0
	 */
	function tribe_is_venue( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->isVenue($postId);
	}

	/**
	 * HTML to output before the event template
	 * @since 2.0
	 */
	function tribe_events_before_html() {
		echo stripslashes(tribe_get_option('spEventsBeforeHTML'));
	}

	/**
	 * HTML to ouput after the event template
	 * @since 2.0
	 */
	function tribe_events_after_html() {
		echo stripslashes(tribe_get_option('spEventsAfterHTML'));
	}
	
	/**
	* If EventBrite plugin is active
	* * If the event is registered in eventbrite, and has one ticket.  Return the cost of that ticket.
	* * If the event is registered in eventbrite, and there are many tickets, return "Varies"
	* If the event is not registered in eventbrite, and there is meta, return that.
	* If the event is not registered in eventbrite, and there is no meta, return ""
	*
	* @param mixed post id or null if used in the loop
	* @return string
	*/
	function tribe_get_cost( $postId = null)  {
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );
		if( class_exists( 'Eventbrite_for_TribeEvents' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->tribe_get_cost($postId);
			if($returned) {
				return esc_html($returned);
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


	/**************************************************
	 * SECTION: Calendar View Template Tags
	 **************************************************/

	/**
	 * Display the full size grid calendar table
	 *
	 * @return void
	 * @since 2.0
	 */
	function tribe_calendar_grid()  {
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( TribeEventsTemplates::getTemplateHierarchy('table') );
	}

	/**
	 * Displays the mini grid calendar table (usually in a widget)
	 *
	 * @return void
	 * @since 2.0
	 */
	function tribe_calendar_mini_grid()  {
		global $wp_query;
		$old_query = $wp_query;

		$wp_query = NEW WP_Query('post_type='.TribeEvents::POSTTYPE);
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( TribeEventsTemplates::getTemplateHierarchy('table-mini') );
	
		$wp_query = $old_query;
	}
	
	/**
	 * Maps events to days
	 *
	 * @param array of events from tribe_get_events()
	 * @param string date of the 
	 * @return array days of the month with events as values
	 * @since 2.0
	 */
	function tribe_sort_by_month( $results, $date )  {
		$cutoff_time = tribe_get_option('multiDayCutoff', '12:00');
		
		if( preg_match( '/(\d{4})-(\d{2})/', $date, $matches ) ) {
			$queryYear	= $matches[1];
			$queryMonth = $matches[2];
		} else {
			return false; // second argument not a date we recognize
		}
		$monthView = array();
		for( $i = 1; $i <= 31; $i++ ) {
			$monthView[$i] = array();
		}


		foreach ( $results as $event ) {
			$started = false;

			list( $startYear, $startMonth, $startDay ) = explode( '-', $event->EventStartDate );
			list( $endYear, $endMonth, $endDay ) = explode( '-', $event->EventEndDate );

			list( $startDay, $garbage ) = explode( ' ', $startDay );
	
			list( $endDay, $garbage ) = explode( ' ', $endDay );
			for( $i = 1; $i <= 31 ; $i++ ) {
				$curDate = strtotime( $queryYear.'-'.$queryMonth.'-'.$i );

				if ( ( $i == $startDay && $startMonth == $queryMonth ) ||  strtotime( $startYear.'-'.$startMonth ) < strtotime( $queryYear.'-'.$queryMonth ) ) {
					$started = true;
				}
				
				// if last day of multiday event 			
				if( !tribe_get_all_day() && tribe_is_multiday($event->ID) && date('Y-m-d', $curDate) == date('Y-m-d', strtotime($event->EventEndDate)) ) {
					$endTime = strtotime(date('Y-m-d', $curDate) . date('h:i A', strtotime($event->EventEndDate)));
					$cutoffTime = strtotime(date('Y-m-d', $curDate) . $cutoff_time .  "AM");
					
					// if end time is before cutoff, then don't show
					if ($endTime <= $cutoffTime) {
						$started = false;
					}
				}
				
				if ( $started ) {
					$monthView[$i][] = $event;
				}
				if( $i == $endDay && $endMonth == $queryMonth ) {
					continue 2;
				}
			}
		}

		return $monthView;
	}

	/**
	 * Prints the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event. 
	 *
	 * @param string a prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @return void
	 * @since 2.0
	 */
	function tribe_month_year_dropdowns( $prefix = '' )  {
		global $wp_query;

		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'] . "-01";
		} else {
			$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
		}
		$monthOptions = TribeEventsViewHelpers::getMonthOptions( $date );
		$yearOptions = TribeEventsViewHelpers::getYearOptions( $date );
		include(TribeEvents::instance()->pluginPath.'admin-views/datepicker.php');
	}

	/**
	 *  Get current calendar gridview date
	 *
	 * @return date $date
	 * @since 2.0
	 */
	function tribe_get_month_view_date()  {
		global $wp_query;

		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'] . "-01";
		} else {
			$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
		}
		
		return $date;
	}
	
	/**
	 * Returns a textual description of the previous month
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_previous_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getDateString( $tribe_ecp->previousMonth( tribe_get_month_view_date() ) );
	}

	/**
	 * Returns a textual description of the current month
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_current_month_text( ) {
		return date( 'F', strtotime( tribe_get_month_view_date() ) );
	}

	/**
	 * Returns a textual description of the next month
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_next_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getDateString( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
	}

	/**
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_displayed_month()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			return $tribe_ecp->getDateString( $tribe_ecp->date );
		}
		return " ";
	}




	/**************************************************
	 * SECTION: Loop Template Tags
	 **************************************************/
	
	
	/**
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
	 * Returns true if the query is set for month display (as opposed to Upcoming / Past)
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_month()  {
		$tribe_ecp = TribeEvents::instance();
		return ( $tribe_ecp->displaying == 'month' ) ? true : false;
	}

	/**
	 *  Check if current display is "bydate"
	 * @since 2.0
	 */
	function tribe_is_by_date() {
		$tribe_ecp = TribeEvents::instance();
		return ( $tribe_ecp->displaying == 'bydate' ) ? true : false;
	}

	/**
	 * Echo an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 */ 
	function tribe_events_title( $depth = true )  {
		echo tribe_get_events_title( $depth );
	}
	
	/**
	 * Return an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 * @return string title
	 * @since 2.0
	 */
	function tribe_get_events_title( $depth = true )  {
		$tribe_ecp = TribeEvents::instance();

		$title = __('Calendar of Events', 'tribe-events-calendar');
		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
			$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
			if ( $depth ) {
				$title = '<a href="'.tribe_get_events_link().'">'.$title.'</a>';
				$title .= ' &#8250; ' . $cat->name;
			} else {
				$title = $cat->name;
			}
		}

		return $title;
	}	




	/**************************************************
	 * SECTION: Google Map Template Tags
	 **************************************************/

	/**
	 * Returns a url to google maps for the given event
	 *
	 * @param string $postId 
	 * @return string a fully qualified link to http://maps.google.com/ for this event
	 * @since 2.0
	 */
	function tribe_get_map_link( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		$output = esc_url($tribe_ecp->googleMapLink( $postId ));
		return apply_filters( 'tribe_get_map_link', $output );
	}

	/**
	 * Returns an embedded google maps for an event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 * @since 2.0
	 */
	function tribe_get_embedded_map( $postId = null, $width = '', $height = '', $force_load = false )  {
		$postId = TribeEvents::postIdHelper( $postId );
		if ( !tribe_is_venue( $postId ) && !tribe_is_event( $postId ) ) {
			return false;
		}

		$postId = tribe_is_venue( $postId ) ? $postId : tribe_get_venue_id( $postId );
		$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
		$toUrlEncode = "";

		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = call_user_func('tribe_get_'.$val);
			if ( $metaVal ) 
				$toUrlEncode .= $metaVal . " ";
		}

		if ( $toUrlEncode ) 
			$address = $toUrlEncode;
		else
			$address = null;		


		if (!$height) $height = tribe_get_option('embedGoogleMapsHeight','350');
		if (!$width) $width = tribe_get_option('embedGoogleMapsWidth','100%');

		if ($address || $force_load) {
			ob_start();
			include(TribeEvents::instance()->pluginPath.'admin-views/event-map.php');
			$google_map = ob_get_contents();
			ob_get_clean();
			return $google_map;
		}
		else return '';
	}

	/**
	 * Check if embed google map is enabled for this event.
	 *
     * @param int $postId id of the post, if none specified, current post is used
	 * @return bool true if google map option is set to embed the map
	 * @since 2.0
	 */
	function tribe_embed_google_map($postId = null) {
		$postId = TribeEvents::postIdHelper( $postId );
		return get_post_meta( get_the_ID(), '_EventShowMap', 1) == 1;
	}

	/**
	 * Check if google map link is enabled for this event
	 *
     * @param int $postId id of the post, if none specified, current post is used
	 * @return bool true if google map link is set to display the event
	 * @since 2.0
	 */
	function tribe_show_google_map_link($postId = null) {
		return get_post_meta( get_the_ID(), '_EventShowMapLink', 1) == 1;
	}




	/**************************************************
	 * SECTION: Organizer Template Tags
	 **************************************************/
	

	/**
	 * Returns the event Organizer ID.
	 *
	 * @param int $postId can supply either event id or organizer id, if none specified, current post is used
	 * @return int Organizer
	 * @since 2.0
	 */
	function tribe_get_organizer_id( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		if (is_numeric($postId) && $postId > 0) {
			$tribe_ecp = TribeEvents::instance();
			// check if $postId is an organizer id
			if ($tribe_ecp->isOrganizer($postId)) {
				$organizer_id = $postId;
			} else {
				$organizer_id = tribe_get_event_meta( $postId, '_EventOrganizerID', true );
			}
		}
		return apply_filters('tribe_get_organizer_id', $organizer_id, $postId );
	}

	/**
	 * Returns the name of the Organizer
	 *
	 * @param int $postId can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Name
	 * @since 2.0
	 */
	function tribe_get_organizer( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = esc_html(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerOrganizer', true ));
		return apply_filters( 'tribe_get_organizer', $output );
	}

	/**
	 * Returns true or false depending on if the post id has/is a n organizer
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_has_organizer( $postId = null) {
		$postId = TribeEvents::postIdHelper( $postId );
		return ( tribe_get_organizer_id( $postId ) > 0 ) ? true : false;
	}

	/**
	 * Returns the Organizer's Email
	 *
	 * @param int $postId can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Email
	 * @since 2.0
	 */
	function tribe_get_organizer_email( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = esc_html(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerEmail', true ));
		return apply_filters( 'tribe_get_organizer_email', $output);
	}

	/**
	 * Returns the event Organizer Name with a link to their supplied website url
	 *
	 * @param int $postId can supply either event id or organizer id, if none specified, current post is used
	 * @param bool $display if true displays full html links around organizers name, if false returns just the link without displaying it
	 * @return string Organizer Name + Url
	 * @since 2.0
	 */
	function tribe_get_organizer_link( $postId = null, $display = true ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$url = esc_url(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerWebsite', true ));
		if( $display && $url != '' ) {
			$organizer_name = tribe_get_organizer($postId);
			$link = '<a href="'.$url.'">'.$organizer_name.'</a>';
		} else {
			$link = $url;
		}
		$link = apply_filters( 'tribe_get_organizer_link', $link, $postId, $display, $url );
		if ( $display ) {
			echo $link;
		} else {
			return $link;
		}
	}

	/**
	 * Returns the event Organizer's phone number
	 *
	 * @param int $postId can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Phone Number
	 * @since 2.0
	 */
	function tribe_get_organizer_phone( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = esc_html(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerPhone', true ));
		return apply_filters( 'tribe_get_organizer_phone', $output ); 
	}




	/**************************************************
	 * SECTION: Venue Template Tags
	 **************************************************/
	
	/**
	 * Returns the event Venue ID.
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return int Venue
	 * @since 2.0
	 */
	function tribe_get_venue_id( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		if ( tribe_is_venue( $postId ) ) {
			return $postId;
		} else {
			return tribe_get_event_meta( $postId, '_EventVenueID', true );
		}
	}

	/**
	 * Returns true or false depending on if the post id for the event has a venue or if the post id is a venue
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return bool
	 * @since 2.0
	 */
	function tribe_has_venue( $postId = null) {
		return ( tribe_get_venue_id( $postId ) > 0 ) ? true : false;
	}

	/**
	 * Returns the event venue name
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @param bool $with_link (deprecated in 2.0.1)
	 * @return string Venue Name
	 * @since 2.0
	 */
	function tribe_get_venue( $postId = null, $with_link = false )  {
		if ( $with_link ) {	_deprecated_argument( __FUNCTION__, '2.0.1' ); }
		$postId = tribe_get_venue_id( $postId );
		$venue = esc_html(tribe_get_event_meta( $postId, '_VenueVenue', true ));
		return $venue;
	}
	
	/**
	 * Returns the event Organizer Name with a link to their supplied website url
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @param bool $display if true displays full html links around venue's name, if false returns just the link without displaying it
	 * @return string venue
	 * @since 2.0
	 */
	function tribe_get_venue_link( $postId = null, $display = true )  {
		$url = esc_url( get_permalink( tribe_get_venue_id( $postId ) ) );
		if( $display && $url != '' ) {
			$venue_name = tribe_get_venue($postId);
			$link = '<a href="'.$url.'">'.$venue_name.'</a>';
		} else {
			$link = $url;
		}
		$link = apply_filters( 'tribe_get_venue_link', $link, $postId, $display, $url );
		if ( $display ) {
			echo $link;
		} else {
			return $link;
		}
	}
	
	/**
	 * Returns the event country
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string country
	 * @since 2.0
	 */
	function tribe_get_country( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCountry', true ) );
		return $output;
	}

	/**
	 * Returns the full address for the venue. Function uses the views/full-address.php template which you can override in your theme (including google microformats etc).
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string formatted event address
	 * @since 2.0
	 */	
	function tribe_get_full_address( $postId = null, $includeVenueName = false )  {
		$postId = tribe_get_venue_id( $postId );
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_full_address', $tribe_ecp->fullAddress( $postId, $includeVenueName ) );
	}

	/**
	 * Returns true if any of the following exist: address, city, state/province (region), country or zip
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return bool true if any part of an address exists
	 * @since 2.0
	 */
	function tribe_address_exists( $postId = null )  {
		if (
			tribe_get_address( $postId ) ||
			tribe_get_city( $postId ) ||
			tribe_get_region( $postId ) ||
			tribe_get_country( $postId ) ||
			tribe_get_zip( $postId )
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the venue street address
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string street address
	 * @since 2.0
	 */
	function tribe_get_address( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueAddress', true ) );
		return $output;
	}

	/**
	 * Returns the venue city
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string city
	 * @since 2.0
	 */
	function tribe_get_city( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCity', true ) );
		return $output;
	}

	/**
	 * Returns the venue state or province
	 *
	 * @return string state
	 * @since 2.0
	 */
	function tribe_get_stateprovince( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueStateProvince', true ) );
		return $output;
	}

	/**
	 * Returns the venue state
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string state
	 * @since 2.0
	 */
	function tribe_get_state( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueState', true ) );
		return $output;
	}

	/**
	 * Returns the venue province
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string province
	 * @since 2.0
	 */
	function tribe_get_province( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueProvince', true ) );
		return $output;
	}

	/**
	 * Returns the state or province for US or non-US addresses
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_region( $postId = null )  {
		$postId = tribe_get_venue_id( $postId );
		if(tribe_get_event_meta($postId, '_VenueStateProvince', true )){
			return tribe_get_event_meta($postId, '_VenueStateProvince', true );
		} else {
			if ( tribe_get_country($postId) == __('United States', 'tribe-events-calendar' ) ) {
				return tribe_get_state($postId);
			} else {
				return tribe_get_province(); 
			}
		}
	}

	/**
	 * Returns the event zip code
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string zip code 
	 * @since 2.0
	 */
	function tribe_get_zip( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html(tribe_get_event_meta( $postId, '_VenueZip', true ));
		return $output;
	}
	
	/**
	 * Returns the venue phone number
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return string phone number
	 * @since 2.0
	 */
	function tribe_get_phone( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html(tribe_get_event_meta( $postId, '_VenuePhone', true ));
		return $output;
	}




	/**************************************************
	 * SECTION: Date Template Tags
	 **************************************************/

	/**
	 * Returns the event start date and time
	 *
	 * @param int post id
	 * @param bool $displayTime if true shows date and time, if false only shows date
	 * @param string $dateFormat allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string date
	 * @since 2.0
	 */
	function tribe_get_start_date( $postId = null, $displayTime = true, $dateFormat = '' )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );

		if( tribe_get_all_day( $postId ) )
			 $displayTime = false;

		$date = strtotime( $post->EventStartDate ? $post->EventStartDate : tribe_get_event_meta( $postId, '_EventStartDate', true ));

		return tribe_event_format_date($date, $displayTime, $dateFormat );
	}

	/**
	 * Returns formatted date
	 *
	 * @param string $date 
	 * @param bool $displayTime if true shows date and time, if false only shows date
	 * @param string $dateFormat allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string
	 * @since 2.0
	 */
	function tribe_event_format_date($date, $displayTime = true,  $dateFormat = '')  {
		$tribe_ecp = TribeEvents::instance();
		
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', TribeDateUtils::DATEONLYFORMAT );

		if ( $displayTime )
			$format = $tribe_ecp->getTimeFormat( $format );

		$shortMonthNames = ( strstr( $format, 'M' ) ) ? true : false;
		$date = date_i18n ( $format, $date );
		return str_replace( array_keys($tribe_ecp->monthNames( $shortMonthNames )), $tribe_ecp->monthNames( $shortMonthNames ), $date);
	}

	/**
	 * Returns the event end date
	 *
	 * @param int post id
	 * @param bool $displayTime if true shows date and time, if false only shows date
	 * @param string $dateFormat allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string date
	 * @since 2.0
	 */
	function tribe_get_end_date( $postId = null, $displayTime = 'true', $dateFormat = '' )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$postId = TribeEvents::postIdHelper( $postId );
	
		if( tribe_get_all_day( $postId ) )
			 $displayTime = false;

		$date = strtotime( $post->EventEndDate ? $post->EventEndDate : tribe_get_event_meta( $postId, '_EventEndDate', true ));

		return tribe_event_format_date($date, $displayTime, $dateFormat );
	}




	/**************************************************
	 * SECTION: Link Template Tags
	 **************************************************/	
	
	/**
	 * Link for all occurrences of an event (based on the currently queried event).
	 *
	 * @return string url
	 * @since 2.0
	 */
	function tribe_all_occurences_link( )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		echo $tribe_ecp->getLink('all');		
	}

	/**
	 * Link for the event day
	 *
	 * @param string $date 
	 * @return string url
	 * @since 2.0
	 */
	function tribe_get_day_link($date) {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getLink('day', $date);		
	}
	
	/**
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param string $anchor link text. Use %title% to place the post title in your string.
	 * @return void
	 * @since 2.0
	 */
	function tribe_previous_event_link( $anchor = false )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();

		echo $tribe_ecp->get_event_link($post,'previous',$anchor);
	}

	/**
	 * Displays a link to the next post by start date for the given event
	 *
	 * @param string $anchor link text. Use %title% to place the post title in your string.
	 * @return void
	 * @since 2.0
	 */
	function tribe_next_event_link( $anchor = false )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		echo $tribe_ecp->get_event_link($post, 'next',$anchor);
	}

	/**
	 * Returns a link to the upcoming events in list view
	 *
	 * @return string 
	 * @since 2.0
	 */
	function tribe_get_upcoming_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('upcoming');
		return $output;
	}

	/**
	 * Returns a link to the next month's events page
	 *
	 * @return string 
	 * @since 2.0
	 */
	function tribe_get_next_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->nextMonth(tribe_get_month_view_date() ));
		return $output;
	}

	/**
	 * Returns a link to the previous month's events page
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_previous_month_link()  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->previousMonth( tribe_get_month_view_date() ));
		return $output;
	}

	/**
	 * Returns a link to the previous events in list view
	 *
	 * @return string 
	 * @since 2.0
	 */
	function tribe_get_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past');
		return $output;
	}

	/**
	 * Returns a link to the events URL
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_events_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('home');
		return $output;
	}
	
	/**
	 * Returns a link to the general or category calendar gridview
	 *
	 * @param string $term
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_gridview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('month', false, $term);
		return $output;
	}
		
	/**
	 * Returns a link to the general or category upcoming view
	 *
	 * @param string $term
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_listview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('upcoming', false, $term);
		return $output;
	}
	
	/**
	 * Returns a link to the general or category past view
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_listview_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past');
		return $output;
	}
	
	/**
	 * Returns a link to the general or category dropdown view
	 *
	 * @return string
	 * @since 2.0
	 */
	function tribe_get_dropdown_link_prefix()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('dropdown');
		return $output;
	}

	/**
	 * Echo link to a single event
	 *
	 * @param int $post
	 * @return string
	 * @since 2.0
	 */
	function tribe_event_link($post = null) {
		// pass in whole post object to retain start date
		echo tribe_get_event_link($post);
	}	

	/**
	 * Get link to a single event
	 * 
	 * @since 2.0
	 */
	function tribe_get_event_link($post = null) {
		return apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink('single', $post), $post );
	}		

	/**
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string
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