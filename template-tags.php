<?php

if( class_exists( 'The_Events_Calendar' ) && !function_exists( 'sp_get_option' ) ) {
	/**
	 * retrieve specific key from options array, optionally provide a default return value
	 */
	function sp_get_option($optionName, $default = '') {
		global $spEvents;
		if($optionName) {
			if( $spEvents->latestOptions ) return $spEvents->latestOptions[$optionName];
			$options = $spEvents->getOptions();
			return ( $options[$optionName] ) ? $options[$optionName] : $default;
		}
	}
	/**
	 * Output function: Prints the gridded calendar table
	 *
	 * @return void
	 */
	function sp_calendar_grid() {
		global $spEvents;
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( $spEvents->getTemplateHierarchy('table') );
	}
	/**
	 * Maps events to days
	 *
	 * @param array of events from sp_get_events()
	 * @param string date of the 
	 * @return array days of the month with events as values
	 */
	function sp_sort_by_month( $results, $date ) {
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
			list( $startYear, $startMonth, $startDay, $garbage ) = explode( '-', $event->EventStartDate );
			list( $endYear, $endMonth, $endDay, $garbage ) = explode( '-', $event->EventEndDate );
			list( $startDay, $garbage ) = explode( ' ', $startDay );
			list( $endDay, $garbage ) = explode( ' ', $endDay );
			for( $i = 1; $i <= 31 ; $i++ ) {
				if ( ( $i == $startDay && $startMonth == $queryMonth ) ||  strtotime( $startYear.'-'.$startMonth ) < strtotime( $queryYear.'-'.$queryMonth ) ) {
					$started = true;
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
	 * Template function: 
	 * @return boolean
	 */
	function sp_is_event( $postId = null ) {
		global $spEvents;
		return $spEvents->isEvent($postId);
	}
	/**
	 * Returns a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return string a fully qualified link to http://maps.google.com/ for this event
	 */
	function sp_get_map_link( $postId = null ) {
		global $spEvents;
		return $spEvents->googleMapLink( $postId );
	}
	/**
	 * Displays a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return void
	 */
	function sp_the_map_link( $postId = null ) {
		echo sp_get_map_link( $postId );
	}
	/**
	 * @return string formatted event address
	 */
	function sp_get_full_address( $postId = null, $includeVenue = false ) {
		$postId = sp_post_id_helper( $postId );
		$address = '';
		if( $includeVenue ) $address .= sp_get_venue( $postId );
		if( sp_get_address( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= sp_get_address( $postId );
		}
		if( sp_get_city( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= sp_get_city( $postId );
		}
		if( sp_get_region( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= sp_get_region( $postId );
		}
		if( sp_get_country( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= sp_get_country( $postId );
		}
		if( sp_get_zip( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= sp_get_zip( $postId );
		}
		$address = str_replace(' ,', ',', $address);
		return $address;
	}
	/**
	 * Displays a formatted event address
	 *
	 * @param string $postId 
	 * @return void
	 */
	function sp_the_full_address( $postId = null ) {
		echo sp_get_full_address( $postId );
	}
	/**
	 * @return boolean true if any part of an address exists
	 */
	function sp_address_exists( $postId = null ) {
		$postId = sp_post_id_helper( $postId );
		return ( sp_get_address( $postId ) || sp_get_city( $postId ) || sp_get_region( $postId ) || sp_get_country( $postId ) || sp_get_zip( $postId ) ) ? true : false;
	}
	/**
	 * Returns an embedded google maps for the given event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 */
	function sp_get_embedded_map( $postId = null, $width = '', $height = '' ) {
		$postId = sp_post_id_helper( $postId );
		if ( !sp_is_event( $postId ) ) {
			return false;
		}
		$locationMetaSuffixes = array( 'Address', 'City', 'State', 'Province', 'Zip', 'Country' );
		$toUrlEncode = "";
		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = get_post_meta( $postId, '_Event' . $val, true );
			if( $metaVal ) $toUrlEncode .= $metaVal . " ";
		}
		if (!$height) $height = sp_get_option('embedGoogleMapsHeight','350');
		if (!$width) $width = sp_get_option('embedGoogleMapsWidth','100%');
		if( $toUrlEncode ) $googleaddress = urlencode( trim( $toUrlEncode ) );
		if ($googleaddress) {
			$google_iframe = '<div id="googlemaps"><iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q='.$googleaddress.'?>&amp;output=embed"></iframe><div class="view-larger-map"><a href="http://www.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$googleaddress.'">View Larger Map</a></div></div>';
			return $google_iframe;
		}
		else return '';
	}
	/**
	 * Displays an embedded google map for the given event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return void
	 */
	function sp_the_embedded_map( $postId = null, $width = null, $height = null ) {
		if (sp_get_option('embedGoogleMaps') == 'on')
			echo sp_get_embedded_map( $postId, $width, $height );
	}
	/**
	 * Prints out the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event. 
	 *
	 * @param string a prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @return void
	 */
	function sp_month_year_dropdowns( $prefix = '' ) {
		global $spEvents, $wp_query;
		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'] . "-01";
		} else {
			$date = date_i18n( The_Events_Calendar::DBDATEFORMAT );
		}
		$monthOptions = $spEvents->getMonthOptions( $date );
		$yearOptions = $spEvents->getYearOptions( $date );
		include('views/datepicker.php');
	}
	/**
	 * Returns the event start date
	 *
	 * @param int post id
	 * @param bool display time?
	 * @param string date format
	 * @return string date
	 */
	function sp_get_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		global $spEvents, $post;
		$postId = sp_post_id_helper( $postId );
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', The_Events_Calendar::DATEONLYFORMAT );
		if( sp_get_all_day( $postId ) ) {
		    $showtime = false;
		}
		if ( $showtime ) {
			$format = $spEvents->getTimeFormat( $format );
		}
		$shortMonthNames = ( strstr( $format, 'M' ) ) ? true : false;
		$date = date ( $format, strtotime( get_post_meta( $postId, '_EventStartDate', true ) ) );
		return str_replace( array_keys($spEvents->monthNames( $shortMonthNames )), $spEvents->monthNames( $shortMonthNames ), $date);
	}
	/**
	 * Returns the event end date
	 *
	 * @param int post id
	 * @param bool display time?
	 * @param string date format
	 * @return string date
	 */
	function sp_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		global $spEvents;
		$postId = sp_post_id_helper( $postId );
		if ( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', The_Events_Calendar::DATEONLYFORMAT );
		if( sp_get_all_day( $postId ) ) {
		    $showtime = false;
		}
		if ( $showtime ) {
			$format = $spEvents->getTimeFormat( $format );
		}
		$date = date ( $format, strtotime( get_post_meta( $postId, '_EventEndDate', true ) ) );
		return str_replace( array_keys($spEvents->monthNames()), $spEvents->monthNames(), $date);
	}
	/**
	* If EventBrite plugin is active
	* 	If the event is registered in eventbrite, and has one ticket.  Return the cost of that ticket.
	* 	If the event is registered in eventbrite, and there are many tickets, return "Varies"
	* If the event is not registered in eventbrite, and there is meta, return that.
	* If the event is not registered in eventbrite, and there is no meta, return ""
	*
	* @param mixed post id or null if used in the loop
	* @return string
	*/
	function sp_get_cost( $postId = null) {
		global $spEvents;
		$postId = sp_post_id_helper( $postId );
		if( class_exists( 'Eventbrite_for_The_Events_Calendar' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->sp_get_cost($postId);
			if($returned) {
				return esc_html($returned);
			}
		}
		if ( $cost = get_post_meta( $postId, '_EventCost', true ) ) {
			return esc_html($cost);
		} else {
			return "";
		}
	}
	/**
	 * Returns the event venue
	 *
	 * @return string venue
	 */
	function sp_get_venue( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventVenue', true ));
	}
	/**
	 * Returns the event country
	 *
	 * @return string country
	 */
	function sp_get_country( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventCountry', true ));
	}
	/**
	 * Returns the event address
	 *
	 * @return string address
	 */
	function sp_get_address( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventAddress', true ));
	}
	/**
	 * Returns the event city
	 *
	 * @return string city
	 */
	function sp_get_city( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventCity', true ));
	}
	/**
	 * Returns the event state
	 *
	 * @return string state
	 */
	function sp_get_state( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventState', true ));
	}
	/**
	 * Returns the event province
	 *
	 * @return string province
	 */
	function sp_get_province( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventProvince', true ));
	}
	/**
	 * Returns the event zip code
	 *
	 * @return string zip code 
	 */
	function sp_get_zip( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventZip', true ));
	}
	/**
	 * Returns the event phone number
	 *
	 * @return string phone number
	 */
	function sp_get_phone( $postId = null) {
		$postId = sp_post_id_helper( $postId );
		return esc_html(get_post_meta( $postId, '_EventPhone', true ));
	}
	/**
	 * Helper function to determine postId. Pulls from global $post object if null or non-numeric.
	 * 
	 * @return int postId;
	 */
	
	function sp_post_id_helper( $postId ) {
		if ( $postId === null || ! is_numeric( $postId ) ) {
			global $post;
			return $post->ID;
		}
		return (int) $postId;
	}

	/**
	 * Helper function to load XML using cURL
	 *
	 * @return array with xml data
	 */
	function load_xml($url) {/*
		TODO remove and use built-in WP functions. Used by eventbrite plugin.
	*/
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = simplexml_load_string(curl_exec($ch));

        curl_close($ch);

        return $data;
    }
		
	/**
	 * Called inside of the loop, returns true if the current post's meta_value (EventStartDate)
	 * is different than the previous post. Will always return true for the first event in the loop.
	 *
	 * @return bool
	 */
	function sp_is_new_event_day() {
		global $spEvents, $post;
		$retval = false;
		$now = time();
		$postTimestamp = strtotime( $post->EventStartDate, $now );
		$postTimestamp = strtotime( date( The_Events_Calendar::DBDATEFORMAT, $postTimestamp ), $now); // strip the time
		if ( $postTimestamp != $spEvents->currentPostTimestamp ) { 
			$retval = true;
		}
		$spEvents->currentPostTimestamp = $postTimestamp; 
		return $retval;
	}
	/**
	 * Call this function in a template to query the events
	 *
	 * @param int number of results to display for upcoming or past modes (default 10)
	 * @param string deprecated: used when events were determined by category. category name to pull events from.
	 * @return array results
	 */
	function sp_get_events( $numResults = null, $catName = null ) {
		global $spEvents;
		return $spEvents->getEvents( $numResults, $catName );
	}
	/**
	 * Returns true if the query is set for past events, false otherwise
	 * 
	 * @return bool
	 */
	function sp_is_past() {
		global $spEvents;
		return ($spEvents->displaying == 'past') ? true : false;
	}
	/**
	 * Returns true if the query is set for upcoming events, false otherwise
	 * 
	 * @return bool
	 */
	function sp_is_upcoming() {
		global $spEvents;
		return ($spEvents->displaying == 'upcoming') ? true : false;
	}
	/**
	 * Returns true if the query is set for month display (as opposed to Upcoming / Past)
	 *
	 * @return bool
	 */
	function sp_is_month() {
		global $spEvents;
		return ( $spEvents->displaying == 'month' ) ? true : false;
	}
	/**
	 * Returns a link to the previous events in list view
	 *
	 * @return string 
	 */
	function sp_get_past_link() {
		global $spEvents;
		return $spEvents->getLink('past');
	}
	/**
	 * Returns a link to the upcoming events in list view
	 *
	 * @return string 
	 */
	function sp_get_upcoming_link() {
		global $spEvents;
		return $spEvents->getLink('upcoming');
	}
	/**
	 * Returns a link to the next month's events page
	 *
	 * @return string 
	 */
	function sp_get_next_month_link() {
		global $spEvents;
		return $spEvents->getLink( 'month', $spEvents->nextMonth( $spEvents->date ) );
	}
	/**
	 * Returns a link to the previous month's events page
	 *
	 * @return string
	 */
	function sp_get_previous_month_link() {
		global $spEvents;
		return $spEvents->getLink( 'month', $spEvents->previousMonth( $spEvents->date ) );
	}

	/**
	 * Returns a link to the events URL
	 *
	 * @return string
	 */
	function sp_get_events_link() {
		global $spEvents;
		return $spEvents->getLink('home');
	}
	
	function sp_get_gridview_link() {
		global $spEvents;
		return $spEvents->getLink('month');
	}
		
	function sp_get_listview_link() {
		global $spEvents;
		return $spEvents->getLink('upcoming');
	}
	
	function sp_get_listview_past_link() {
		global $spEvents;
		return $spEvents->getLink('past');
	}
	
	function sp_get_dropdown_link_prefix() {
		global $spEvents;
		return $spEvents->getLink('dropdown');
	}
	function sp_get_ical_link() {
		global $spEvents;
		return $spEvents->getLink('ical');
	}

	/**
	 * Returns a textual description of the previous month
	 *
	 * @return string
	 */
	function sp_get_previous_month_text() {
		global $spEvents;
		return $spEvents->getDateString( $spEvents->previousMonth( $spEvents->date ) );
	}
	/**
	 * Returns a textual description of the current month
	 *
	 * @return string
	 */
	function sp_get_current_month_text( ){
		global $spEvents; 
		return date( 'F', strtotime( $spEvents->date ) );
	}
	/**
	 * Returns a textual description of the next month
	 *
	 * @return string
	 */
	function sp_get_next_month_text() {
		global $spEvents;
		return $spEvents->getDateString( $spEvents->nextMonth( $spEvents->date ) );
	}
	/**
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @return string
	 */
	function sp_get_displayed_month() {
		global $spEvents;
		if ( $spEvents->displaying == 'month' ) {
			return $spEvents->getDateString( $spEvents->date );
		}
		return " ";
	}
	/**
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string
	 */
	function sp_get_this_month_link() {
		global $spEvents;
		if ( $spEvents->displaying == 'month' ) {
			return $spEvents->getLink( 'month', $spEvents->date );
		}
		return false;
	}
	/**
	 * Returns the state or province for US or non-US addresses
	 *
	 * @return string
	 */
	function sp_get_region() {
		if (get_post_meta($postId, '_EventCountry', true ) == 'United States') {
			return sp_get_state();
		} else {
			return sp_get_province(); 
		}
	}
	/**
	 * Returns true if the event is an all day event
	 *
	 * @return bool
	 */
	function sp_get_all_day( $postId = null ) {
		$postId = sp_post_id_helper( $postId );
		return get_post_meta( $postId, '_EventAllDay', true );
	}
	
	include_once 'deprecated-template-tags.php';
	
} // end if class_exists('The-Events-Calendar')