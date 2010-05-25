<?php

if( class_exists( 'The_Events_Calendar' ) && !function_exists( 'eventsGetOptionValue' ) ) {
	// fetch the iCal file
	if ( isset($_GET['ical']) ) add_action('init', array( $spEvents, 'iCalFeed') );
	/**
	 * retrieve specific key from options array, optionally provide a default return value
	 */
	function eventsGetOptionValue($optionName, $default = '') {
		global $spEvents;
		if($optionName) {
			if( $spEvents->latestOptions ) return $spEvents->latestOptions[$optionName];
			$options = $spEvents->getOptions();
			return ( $options[$optionName] ) ? $options[$optionName] : $default;
		}
	}
	/**
	 * Output function: Prints the events calendar 'grid view'
	 *
	 * @return void
	 */
	function event_grid_view() {
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( dirname( __FILE__ ) . '/views/table.php' );
	}
	/**
	 * Maps events to days
	 *
	 * @param array of events from get_events()
	 * @param string date of the 
	 * @return array days of the month with events as values
	 */
	function events_by_month( $results, $date ) {
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
	 * Output function: Prints the selected event style
	 *
	 * @param string $post_id 
	 * @return void
	 */
	function event_style( $postId = null ) {	
		echo get_event_style( $postId );
	}

	/**
	 * Template function: 
	 * @return boolean
	 */
	function is_event( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if (get_post_meta( $postId, '_isEvent', true )) {
			return true;
		}
		return false;
	}
	/**
	 * Returns a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return string a fully qualified link to http://maps.google.com/ for this event
	 */
	function get_event_google_map_link( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if ( !is_event( $postId ) ) return false;
		$locationMetaSuffixes = array( 'Address', 'City', 'State', 'Province', 'Zip', 'Country' );
		$toUrlEncode = "";
		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = get_post_meta( $postId, '_Event' . $val, true );
			if( $metaVal ) $toUrlEncode .= $metaVal . " ";
		}
		if( $toUrlEncode ) return "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=" . urlencode( trim( $toUrlEncode ) );
		return "";
	}
	/**
	 * Displays a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return void
	 */
	function event_google_map_link( $postId = null ) {
		echo get_event_google_map_link( $postId );
	}
	/**
	 * @return string formatted event address
	 */
	function tec_get_event_address( $postId = null, $includeVenue = false ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		$address = '';
		if( $includeVenue ) $address .= the_event_venue( $postId );
		if( the_event_address( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= the_event_address( $postId );
		}
		if( the_event_city( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= the_event_city( $postId );
		}
		if( the_event_region( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= the_event_region( $postId );
		}
		if( the_event_country( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= the_event_country( $postId );
		}
		if( the_event_zip( $postId ) ) {
			if( $address ) $address .= ', ';
			$address .= the_event_zip( $postId );
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
	function tec_event_address( $postId = null ) {
		echo tec_get_event_address( $postId );
	}
	/**
	 * @return boolean true if any part of an address exists
	 */
	function tec_address_exists( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return ( the_event_address( $postId ) || the_event_city( $postId ) || the_event_region( $postId ) || the_event_country( $postId ) || the_event_zip( $postId ) ) ? true : false;
	}
	/**
	 * Returns an embedded google maps for the given event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 */
	function get_event_google_map_embed( $postId = null, $width = '', $height = '' ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if ( !is_event( $postId ) ) {
			return false;
		}
		$locationMetaSuffixes = array( 'Address', 'City', 'State', 'Province', 'Zip', 'Country' );
		$toUrlEncode = "";
		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = get_post_meta( $postId, '_Event' . $val, true );
			if( $metaVal ) $toUrlEncode .= $metaVal . " ";
		}
		if (!$height) $height = eventsGetOptionValue('embedGoogleMapsHeight','350');
		if (!$width) $width = eventsGetOptionValue('embedGoogleMapsWidth','100%');
		if( $toUrlEncode ) $googleaddress = urlencode( trim( $toUrlEncode ) );
		if ($googleaddress) {
			$google_iframe = '<div id="googlemaps"><iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q='.$googleaddress.'?>&amp;output=embed"></iframe><br /><small><a href="http://www.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$googleaddress.'" style="color:#0000FF;text-align:left">View Larger Map</a></small></div>';
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
	function event_google_map_embed( $postId = null, $width = null, $height = null ) {
		if (eventsGetOptionValue('embedGoogleMaps') == 'on') echo get_event_google_map_embed( $postId, $width, $height );
	}
	/**
	 * Prints out the javascript required to control the datepicker (onChange of the id='datepicker')
	 *
	 * @param string a prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @return void
	 */
	function get_jump_to_date_calendar( $prefix = '' ) {
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
	function the_event_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		global $spEvents, $post;
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', The_Events_Calendar::DATEONLYFORMAT );
		if( the_event_all_day( $postId ) ) {
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
	function the_event_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		global $spEvents, $post;
		if ( $postId === null || !is_numeric( $postId ) ) {
			$postId = $post->ID;
		}
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', The_Events_Calendar::DATEONLYFORMAT );
		if( the_event_all_day( $postId ) ) {
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
	function the_event_cost( $postId = null) {
		global $spEvents;
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if( class_exists( 'Eventbrite_for_The_Events_Calendar' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->the_event_cost($postId);
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
	function the_event_venue( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventVenue', true ));
	}
	/**
	 * Returns the event country
	 *
	 * @return string country
	 */
	function the_event_country( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventCountry', true ));
	}
	/**
	 * Returns the event address
	 *
	 * @return string address
	 */
	function the_event_address( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventAddress', true ));
	}
	/**
	 * Returns the event city
	 *
	 * @return string city
	 */
	function the_event_city( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventCity', true ));
	}
	/**
	 * Returns the event state
	 *
	 * @return string state
	 */
	function the_event_state( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventState', true ));
	}
	/**
	 * Returns the event province
	 *
	 * @return string province
	 */
	function the_event_province( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventProvince', true ));
	}
	/**
	 * Returns the event zip code
	 *
	 * @return string zip code 
	 */
	function the_event_zip( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventZip', true ));
	}
	/**
	 * Returns the event phone number
	 *
	 * @return string phone number
	 */
	function the_event_phone( $postId = null) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return esc_html(get_post_meta( $postId, '_EventPhone', true ));
	}
	/**
	 * Returns a list of lectures that are associated with this event
	 *
	 * @param int optional post id
	 * @return mixed array of posts or false
	 */
	function the_event_lectures( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if( !is_event( $postId ) ) { 
			return false;
		}
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_lectureEvent' AND meta_value = '{$postId}'";
		$results = $wpdb->get_results( $query );
		if( empty( $results ) ) { 
			return $results;
		}
		$lectures = array();
		foreach ( $results as $lecture ) {
			$lectures[] = $lecture->post_id;
		}
		$lectures = array_unique( $lectures );
		$results = array();
		foreach ( $lectures as $lectureId ) {
			$results[] = get_post( $lectureId ); 
		}
		return $results;
		
	}

	/**
	 * Helper function to load XML using cURL
	 *
	 * @return array with xml data
	 */
	function load_xml($url) {
    
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
	function is_new_event_day( ) {
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
	 * Call this function in a template to query the events and start the loop. Do not
	 * subsequently call the_post() in your template, as this will start the loop twice and then
	 * you're in trouble.
	 * 
	 * http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query#Query_based_on_Custom_Field_and_Category
	 *
	 * @param int number of results to display for upcoming or past modes (default 10)
	 * @param string category name to pull events from, defaults to the currently displayed category
	 * @uses $wpdb
	 * @uses $wp_query
	 * @return array results
	 */
	function get_events( $numResults = null, $catName = null ) {
		if( !$numResults ) $numResults = get_option( 'posts_per_page', 10 );
		global $wpdb, $spEvents;
		$spEvents->setOptions();
		if( $catName ) {
			$categoryId = get_cat_id( $catName );		
		} else {
			$categoryId = get_query_var( 'cat' );
		}		
		$extraSelectClause ='';
		$extraJoinEndDate ='';
		if ( events_displaying_month() ) {
			$extraSelectClause	= ", d2.meta_value as EventEndDate ";
			$extraJoinEndDate	 = " LEFT JOIN $wpdb->postmeta  as d2 ON($wpdb->posts.ID = d2.post_id) ";
			$whereClause = " AND d1.meta_key = '_EventStartDate' AND d2.meta_key = '_EventEndDate' ";
			// does this event start in this month?
			$whereClause .= " AND ((d1.meta_value >= '".$spEvents->date."'  AND  d1.meta_value < '".$spEvents->nextMonth( $spEvents->date )."')  ";
			// Or does it end in this month?
			$whereClause .= " OR (d2.meta_value  >= '".$spEvents->date."' AND d2.meta_value < '".$spEvents->nextMonth( $spEvents->date )."' ) ";
			// Or does the event start sometime in the past and end sometime in the distant future?
			$whereClause .= " OR (d1.meta_value  <= '".$spEvents->date."' AND d2.meta_value > '".$spEvents->nextMonth( $spEvents->date )."' ) ) ";
			$numResults = 999999999;
		}
		if ( events_displaying_upcoming() ) {
			$extraSelectClause	= ", d2.meta_value as EventEndDate ";
			$extraJoinEndDate	 = " LEFT JOIN $wpdb->postmeta  as d2 ON($wpdb->posts.ID = d2.post_id) ";
			$whereClause = " AND d1.meta_key = '_EventStartDate' AND d2.meta_key = '_EventEndDate' ";
			// Is the start date in the future?
			$whereClause .= ' AND ( d1.meta_value > "'.$spEvents->date.'" ';
			// Or is the start date in the past but the end date in the future? (meaning the event is currently ongoing)
			$whereClause .= ' OR ( d1.meta_value < "'.$spEvents->date.'" AND d2.meta_value > "'.$spEvents->date.'" ) ) ';
		}
		$eventsQuery = "
			SELECT $wpdb->posts.*, d1.meta_value as EventStartDate
				$extraSelectClause
			 	FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
			$extraJoinEndDate
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_taxonomy.term_id = $categoryId
			AND $wpdb->term_taxonomy.taxonomy = 'category'
			AND $wpdb->posts.post_status = 'publish'
			$whereClause
			ORDER BY d1.meta_value ".$spEvents->order."
			LIMIT $numResults";
		$return = $wpdb->get_results($eventsQuery, OBJECT);
		return $return;
	}
	/**
	 * Returns true if the query is set for past events, false otherwise
	 * 
	 * @return bool
	 */
	function events_displaying_past() {
		global $spEvents;
		return ($spEvents->displaying == "past") ? true : false;
	}
	/**
	 * Returns true if the query is set for upcoming events, false otherwise
	 * 
	 * @return bool
	 */
	function events_displaying_upcoming() {
		global $spEvents;
		return ($spEvents->displaying == "upcoming") ? true : false;
	}
	/**
	 * Returns true if the query is set for month display (as opposed to Upcoming / Past)
	 *
	 * @return bool
	 */
	function events_displaying_month() {
		global $spEvents;
		return ( $spEvents->displaying == "month" ) ? true : false;
	}
	/**
	 * Returns a link to the previous events in list view
	 *
	 * @return string 
	 */
	function events_get_past_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDisplay'=>'past'), $link );
		} else {
			return trailingslashit( $link ) . 'past';
		}
	}
	/**
	 * Returns a link to the upcoming events in list view
	 *
	 * @return string 
	 */
	function events_get_upcoming_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDisplay'=>'upcoming'), $link );
		} else {
			return trailingslashit( $link ) . 'upcoming';
		}
	}
	/**
	 * Returns a link to the next month's events page
	 *
	 * @return string 
	 */
	function events_get_next_month_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDate'=>$spEvents->nextMonth( $spEvents->date )), $link );
		} else {
			return trailingslashit( $link ) . $spEvents->nextMonth( $spEvents->date );
		}
	}
	/**
	 * Returns a link to the previous month's events page
	 *
	 * @return string
	 */
	function events_get_previous_month_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDate'=>$spEvents->previousMonth( $spEvents->date )), $link );
		} else {
			return trailingslashit( $link ) . $spEvents->previousMonth( $spEvents->date );
		}
	}
	/**
	 * Returns a link to the events category
	 *
	 * @return string
	 */
	function events_get_events_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		return get_category_link( $cat_id );
	}
	
	function events_get_gridview_link( ) {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDisplay'=>'month'), $link );
		} else {
			return trailingslashit( $link ) . 'month';
		}
	}
		
	function events_get_listview_link( ) {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDisplay'=>'upcoming'), $link );
		} else {
			return trailingslashit( $link ) . 'upcoming';
		}
	}
	
	function events_get_listview_past_link( ) {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
			return add_query_arg( array('eventDisplay'=>'past'), $link );
		} else {
			return trailingslashit( $link ) . 'past';
		}
	}

	/**
	 * Returns a textual description of the previous month
	 *
	 * @return string
	 */
	function events_get_previous_month_text() {
		global $spEvents;
		return $spEvents->getDateString( $spEvents->previousMonth( $spEvents->date ) );
	}
	/**
	 * Returns a texual description of the current month
	 *
	 * @return string
	 */
	function events_get_current_month_text( ){
		global $spEvents; 
		return date( 'F', strtotime( $spEvents->date ) );
	}
	/**
	 * Returns a textual description of the next month
	 *
	 * @return string
	 */
	function events_get_next_month_text() {
		global $spEvents;
		return $spEvents->getDateString( $spEvents->nextMonth( $spEvents->date ) );
	}
	/**
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @return string
	 */
	function events_get_displayed_month() {
		global $spEvents;
		if ( $spEvents->displaying == "month" ) {
			return $spEvents->getDateString( $spEvents->date );
		}
		return " ";
	}
	/**
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string
	 */
	function events_get_this_month_link() {
		global $spEvents;
		$cat_id = get_query_var( 'cat' );
		if( !$cat_id ) {
			$cat_id = $spEvents->eventCategory();
		}
		$link = get_category_link( $cat_id );
		if ( $spEvents->displaying == "month" ) {
			return trailingslashit( $link ) . $spEvents->date;
		}
		return false;
	}
	/**
	 * Returns the state or province for US or non-US addresses
	 *
	 * @return string
	 */
	function the_event_region() {
		if (get_post_meta($postId, '_EventCountry', true ) == 'United States') {
			return the_event_state();
		} else {
			return the_event_province(); 
		}
	}
	/**
	 * Returns true if the event is an all day event
	 *
	 * @return bool
	 */
	function the_event_all_day( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		return get_post_meta( $postId, '_EventAllDay', true );
	}
} // end if class_exists('The-Events-Calendar')