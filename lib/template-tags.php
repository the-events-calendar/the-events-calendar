<?php
/**
 * Template Tags
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) && !function_exists( 'tribe_get_option' ) ) {
	
	/**
	 * retrieve specific key from options array, optionally provide a default return value
	 */
	function tribe_get_option($optionName, $default = '')  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getOption($optionName, $default);
	}
	/**
	 * Output function: Prints the gridded calendar table
	 *
	 * @return void
	 */
	function tribe_calendar_grid()  {
		$tribe_ecp = TribeEvents::instance();
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( TribeEventsTemplates::getTemplateHierarchy('table') );
	}
	/**
	 * Output: Prints the mini gridded calendar table
	 *
	 * @return void
	 */
	function tribe_calendar_mini_grid()  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();
		$old_query = $wp_query;

		$wp_query = NEW WP_Query('post_type=sp_events');
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
					$endTime = strtotime(date('Y-m-d') . date('h:i A', strtotime($event->EventEndDate)));
					$cutoffTime = strtotime(date('Y-m-d') . $cutoff_time .  "AM");
					
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
	 * Template function: 
	 * @return boolean
	 */
	function tribe_is_event( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->isEvent($postId);
	}
	/**
	 * Returns a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return string a fully qualified link to http://maps.google.com/ for this event
	 */
	function tribe_get_map_link( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->googleMapLink( $postId ));
	}
	/**
	 * Displays a link to google maps for the given event
	 *
	 * @param string $postId 
	 * @return void
	 */
	function tribe_the_map_link( $postId = null )  {
		echo esc_html(tribe_get_map_link( $postId ));
	}
	
	/**
	 * @return string formatted event address
	 */	
	function tribe_get_full_address( $postId = null, $includeVenue = false )  {
		$postId = tribe_post_id_helper( $postId );
		$address = array();
		if( $includeVenue ) {
			$address[] = tribe_get_venue( $postId );
		}
		if( tribe_get_address( $postId ) ) {
			$address[] = tribe_get_address( $postId );
		}

		$cityregion = '';
		if( tribe_get_city( $postId ) ) {
			$cityregion .= tribe_get_city( $postId );
		}
		if( tribe_get_region( $postId ) ) {
			if( $cityregion != '' ) $cityregion .= ', ';
			$cityregion .= tribe_get_region( $postId );
		}
		if( $cityregion != '' ) $address[] = $cityregion;
		
		if( tribe_get_zip( $postId ) ) {
			$address[] = tribe_get_zip( $postId );
		}
		if( tribe_get_country( $postId ) ) {
			$address[] = tribe_get_country( $postId );
		}
		return join("</br>\n",$address);
	}
	/**
	 * Displays a formatted event address
	 *
	 * @param string $postId 
	 * @return void
	 */
	function tribe_the_full_address( $postId = null )  {
		echo tribe_get_full_address( $postId );
	}
	/**
	 * @return boolean true if any part of an address exists
	 */
	function tribe_address_exists( $postId = null )  {
		$postId = tribe_post_id_helper( $postId );
		return ( tribe_get_address( $postId ) || tribe_get_city( $postId ) || tribe_get_region( $postId ) || tribe_get_country( $postId ) || tribe_get_zip( $postId ) ) ? true : false;
	}
	/**
	 * Returns an embedded google maps for the given event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 */
	function tribe_get_embedded_map( $postId = null, $width = '', $height = '', $force_load = false )  {
		$tribe_ecp = TribeEvents::instance();

		$postId = tribe_post_id_helper( $postId );
		if ( !tribe_is_event( $postId ) ) {
			return false;
		}
		

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
			include($tribe_ecp->pluginPath.'admin-views/event-map.php');
			$google_map = ob_get_contents();
			ob_get_clean();
			return $google_map;
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
	function tribe_the_embedded_map( $postId = null, $width = null, $height = null )  {
		if (tribe_get_option('embedGoogleMaps'))
			echo tribe_get_embedded_map( $postId, $width, $height );
	}
	/**
	 * Prints the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event. 
	 *
	 * @param string a prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @return void
	 */
	function tribe_month_year_dropdowns( $prefix = '' )  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();

		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'] . "-01";
		} else {
			$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
		}
		$monthOptions = TribeEventsViewHelpers::getMonthOptions( $date );
		$yearOptions = TribeEventsViewHelpers::getYearOptions( $date );
		include($tribe_ecp->pluginPath.'admin-views/datepicker.php');
	}

	/**
	 * Returns the event start date
	 *
	 * @param int post id
	 * @param bool display time?
	 * @param string date format
	 * @return string date
	 */
	function tribe_get_start_date( $postId = null, $showtime = true, $dateFormat = '' )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$postId = tribe_post_id_helper( $postId );

		if( tribe_get_all_day( $postId ) )
		    $showtime = false;

		$date = strtotime( $post->EventStartDate ? $post->EventStartDate : tribe_get_event_meta( $postId, '_EventStartDate', true ));

		return tribe_event_format_date($date, $showtime, $dateFormat );
	}

	function tribe_event_format_date($date, $showtime = true,  $dateFormat = '')  {
		$tribe_ecp = TribeEvents::instance();
		
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', TribeDateUtils::DATEONLYFORMAT );

		if ( $showtime )
			$format = $tribe_ecp->getTimeFormat( $format );

		$shortMonthNames = ( strstr( $format, 'M' ) ) ? true : false;
		$date = date ( $format, $date );
		return str_replace( array_keys($tribe_ecp->monthNames( $shortMonthNames )), $tribe_ecp->monthNames( $shortMonthNames ), $date);
	}

	/**
	 * Returns the event end date
	 *
	 * @param int post id
	 * @param bool display time?
	 * @param string date format
	 * @return string date
	 */
	function tribe_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$postId = tribe_post_id_helper( $postId );
	
		if( tribe_get_all_day( $postId ) )
		    $showtime = false;

		$date = strtotime( $post->EventEndDate ? $post->EventEndDate : tribe_get_event_meta( $postId, '_EventEndDate', true ));

		return tribe_event_format_date($date, $showtime, $dateFormat );
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
	function tribe_get_cost( $postId = null)  {
		$tribe_ecp = TribeEvents::instance();
		$postId = tribe_post_id_helper( $postId );
		if( class_exists( 'Eventbrite_for_TribeEvents' ) ) {
			global $spEventBrite;
			$returned = $spEventBrite->tribe_get_cost($postId);
			if($returned) {
				return esc_html($returned);
			}
		}

		$cost = tribe_get_event_meta( $postId, '_EventCost', true );

		if($cost === ''){
			return '';
		}elseif($cost == '0'){
			return "Free";
		}else{
			return esc_html($cost);
		}
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_has_organizer( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		//echo tribe_get_event_meta( $postId, '_EventVenueID', true ).'|';
		return tribe_get_event_meta( $postId, '_EventOrganizerID', true );
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_get_organizer( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( tribe_has_organizer(), '_OrganizerOrganizer', true ));
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_get_organizer_email( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( tribe_has_organizer(), '_OrganizerEmail', true ));
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_get_organizer_website( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( tribe_has_organizer(), '_OrganizerWebsite', true ));
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_get_organizer_link( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );

		$link = tribe_get_organizer($postId);

		if(tribe_get_organizer_website($postId) != ''){
			$link = '<a href="'.esc_attr(tribe_get_organizer_website($postId)).'">'.$link.'</a>';
		}

		return $link;
	}
	/**
	 * Returns the event Organizer
	 *
	 * @return string Organizer
	 */
	function tribe_get_organizer_phone( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( tribe_has_organizer(), '_OrganizerPhone', true ));
	}
	/**
	 * Returns the event venue
	 *
	 * @return string venue
	 */
	function tribe_has_venue( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return tribe_get_event_meta( $postId, '_EventVenueID', true );
	}
	/**
	 * Returns the event venue
	 *
	 * @return string venue
	 */
	function tribe_get_venue( $postId = null, $with_link = false)  {
		$postId = tribe_post_id_helper( $postId );
		$venue = esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueVenue', true ) : tribe_get_event_meta( $postId, '_EventVenue', true ));
		
		if( $with_link && tribe_has_venue( $postId ) )
			return "<a href='" . get_permalink(tribe_has_venue( $postId )) . "'>$venue</a>";
		
		return $venue;
	}
	
	/**
	 * Returns the event venue permalink
	 *
	 * @return string venue
	 */
	function tribe_get_venue_permalink( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ? get_permalink(tribe_has_venue( $postId )) : "");
	}	
	/**
	 * Returns the event country
	 *
	 * @return string country
	 */
	function tribe_get_country( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueCountry', true ) : tribe_get_event_meta( $postId, '_EventCountry', true ));
	}
	/**
	 * Returns the event address
	 *
	 * @return string address
	 */
	function tribe_get_address( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueAddress', true ) : tribe_get_event_meta( $postId, '_EventAddress', true ));
	}
	/**
	 * Returns the event city
	 *
	 * @return string city
	 */
	function tribe_get_city( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueCity', true ) : tribe_get_event_meta( $postId, '_EventCity', true ));
	}
	/**
	 * Returns the event state or Province
	 *
	 * @return string state
	 */
	function tribe_get_stateprovince( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueStateProvince', true ));
	}
	/**
	 * Returns the event state
	 *
	 * @return string state
	 */
	function tribe_get_state( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueState', true ) : tribe_get_event_meta( $postId, '_VenueState', true ));
	}
	/**
	 * Returns the event province
	 *
	 * @return string province
	 */
	function tribe_get_province( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueProvince', true ) : tribe_get_event_meta( $postId, '_EventProvince', true ));
	}
	/**
	 * Returns the event zip code
	 *
	 * @return string zip code 
	 */
	function tribe_get_zip( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenueZip', true ) : tribe_get_event_meta( $postId, '_EventZip', true ));
	}
	/**
	 * Returns the event phone number
	 *
	 * @return string phone number
	 */
	function tribe_get_phone( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html((tribe_has_venue( $postId )) ?  tribe_get_event_meta( tribe_has_venue( $postId ), '_VenuePhone', true ) : tribe_get_event_meta( $postId, '_EventPhone', true ));
	}
	
	function tribe_all_occurences_link( )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();

		echo $tribe_ecp->getLink('all');		
	}
	
	/**
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param string $anchor link text. Use %title% to place the post title in your string.
	 * @return void
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
	 */
	function tribe_next_event_link( $anchor = false )  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		echo $tribe_ecp->get_event_link($post, 'next',$anchor);
	}
	/**
	 * Helper function to determine postId. Pulls from global $post object if null or non-numeric.
	 * 
	 * @return int postId;
	 */
	
	function tribe_post_id_helper( $postId )  {
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
	 * Call this function in a template to query the events
	 *
	 * @param int numResults number of results to display for upcoming or past modes (default 10)
	 * @param string|int eventCat Event Category: use int for term ID, string for name.
	 * @param string metaKey A meta key to query. Useful for sorting by country, venue, etc. metaValue must also be set to use.
	 * @param string metaValue The value of the queried metaKey, which also must be set.
	 * @return array results
	 */
	function tribe_get_events( $args = '' )  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getEvents( $args );
	}
	/**
	 * Returns true if the query is set for past events, false otherwise
	 * 
	 * @return bool
	 */
	function tribe_is_past()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'past') ? true : false;
	}
	/**
	 * Returns true if the query is set for upcoming events, false otherwise
	 * 
	 * @return bool
	 */
	function tribe_is_upcoming()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'upcoming') ? true : false;
	}
	
	function tribe_is_showing_all()  {
		$tribe_ecp = TribeEvents::instance();
		return ($tribe_ecp->displaying == 'all') ? true : false;		
	}
	/**
	 * Returns true if the query is set for month display (as opposed to Upcoming / Past)
	 *
	 * @return bool
	 */
	function tribe_is_month()  {
		$tribe_ecp = TribeEvents::instance();
		return ( $tribe_ecp->displaying == 'month' ) ? true : false;
	}
	/**
	 * Returns a link to the previous events in list view
	 *
	 * @return string 
	 */
	function tribe_get_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('past'));
	}
	/**
	 * Returns a link to the upcoming events in list view
	 *
	 * @return string 
	 */
	function tribe_get_upcoming_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('upcoming'));
	}
	/**
	 * Returns a link to the next month's events page
	 *
	 * @return string 
	 */
	function tribe_get_next_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink( 'month', $tribe_ecp->nextMonth(tribe_get_month_view_date() )));
	}
	/**
	 * Returns a link to the previous month's events page
	 *
	 * @return string
	 */
	function tribe_get_previous_month_link()  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();

		return esc_html($tribe_ecp->getLink( 'month', $tribe_ecp->previousMonth( tribe_get_month_view_date() )));
	}
	
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
	 * Returns an ical feed for a single event. Must be used in the loop.
	 * 
	 * @return string
	 */
	function tribe_get_single_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink( 'ical', 'single' ));
	}

	/**
	 * Returns a link to the events URL
	 *
	 * @return string
	 */
	function tribe_get_events_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('home'));
	}
	
	function tribe_get_gridview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('month', false, $term));
	}
		
	function tribe_get_listview_link($term = null)  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('upcoming', false, $term));
	}
	
	function tribe_get_listview_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('past'));
	}
	
	function tribe_get_dropdown_link_prefix()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('dropdown'));
	}
	function tribe_get_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		return esc_html($tribe_ecp->getLink('ical'));
	}
	// pass in whole post object to retain start date
	function tribe_event_link($post = null) {
		echo tribe_get_event_link($post);
	}	

	function tribe_get_event_link($post = null) {
		return apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink('single', $post), $post );
	}		

	/**
	 * Returns a textual description of the previous month
	 *
	 * @return string
	 */
	function tribe_get_previous_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getDateString( $tribe_ecp->previousMonth( tribe_get_month_view_date() ) );
	}
	/**
	 * Returns a textual description of the current month
	 *
	 * @return string
	 */
	function tribe_get_current_month_text( ) {
		$tribe_ecp = TribeEvents::instance(); 
		return date( 'F', strtotime( tribe_get_month_view_date() ) );
	}
	/**
	 * Returns a textual description of the next month
	 *
	 * @return string
	 */
	function tribe_get_next_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->getDateString( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
	}
	/**
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @return string
	 */
	function tribe_get_displayed_month()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			return $tribe_ecp->getDateString( $tribe_ecp->date );
		}
		return " ";
	}
	/**
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string
	 */
	function tribe_get_this_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			return esc_html($tribe_ecp->getLink( 'month', $tribe_ecp->date ));
		}
		return false;
	}
	/**
	 * Returns the state or province for US or non-US addresses
	 *
	 * @return string
	 */
	function tribe_get_region( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		if(tribe_get_event_meta(tribe_has_venue( $postId ), '_VenueStateProvince', true )){
			return tribe_get_event_meta(tribe_has_venue( $postId ), '_VenueStateProvince', true );
		}else
		if ( tribe_get_country() == __('United States', $tribe_ecp->pluginDomain ) ) {
			return tribe_get_state();
		} else {
			return tribe_get_province(); 
		}
	}
	/**
	 * Returns true if the event is an all day event
	 *
	 * @return bool
	 */
	function tribe_get_all_day( $postId = null )  {
		$postId = tribe_post_id_helper( $postId );
		return !! tribe_get_event_meta( $postId, '_EventAllDay', true );
	}
	
	function tribe_is_multiday( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		$start = strtotime(tribe_get_event_meta( $postId, '_EventStartDate', true ));
		$end = strtotime(tribe_get_event_meta( $postId, '_EventEndDate', true ));
		return date('d-m-Y', $start) != date('d-m-Y', $end);
	}
	
	/**
	 * echos an events title, with pseudo-breadcrumb if on a category
	*/ 
	function tribe_events_title()  {
		echo tribe_get_events_title();
	}
	
	function tribe_get_events_title()  {
		$tribe_ecp = TribeEvents::instance();

		$title = __('Calendar of Events', $tribe_ecp->pluginDomain);
		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
			$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
			$title = '<a href="'.tribe_get_events_link().'">'.$title.'</a>';
			$title .= ' &#8250; ' . $cat->name;
		}

		return $title;
	}

	function tribe_meta_event_cats()  {
		$tribe_ecp = TribeEvents::instance();
		the_terms( get_the_ID(), $tribe_ecp->get_event_taxonomy(), '<dt>'.__('Category:',$tribe_ecp->pluginDomain ).'</dt><dd>', ', ', '</dd>' );
	}

	/**
	 * Get event post meta. If $meta is passed then only return the value for that item.
	 *
	 * @param string $postId 
	 * @param string $meta 
	 * @param string $single 
	 * @return string
	 * @author Peter Chester
	 */
	function tribe_get_event_meta( $postId = null, $meta = false, $single = true ){
	  	$postId = tribe_post_id_helper( $postId );
		if ( $meta ) {
			$tribe_ecp = TribeEvents::instance();
			return $tribe_ecp->getEventMeta( $postId, $meta, $single );
		} else {

		}
	}
	
	/**
	 * r the current event category name
	*/ 
	function tribe_meta_event_category_name() {
		$tribe_ecp = TribeEvents::instance();
		$current_cat = get_query_var('sp_events_cat');
		if($current_cat){
			$term_info = get_term_by('slug',$current_cat,$tribe_ecp->get_event_taxonomy());
			return $term_info->name;
		}
	}
	
	/* is this event recurring? */
	function tribe_is_recurring_event( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		$postId = tribe_post_id_helper( $postId );
		
		return sizeof(get_post_meta($postId, '_EventStartDate')) > 1;
	}

	/**
	 * Returns an add to Google Calendar link. Must be used in the loop
	 * @author Julien Cornic [www.juxy.fr]
	 * @author Matt Wiebe
	*/
	function tribe_get_add_to_gcal_link()  {
		$post_id = get_the_ID();
		$start_date = strtotime(get_post_meta( $post_id, '_EventStartDate', true ));
		$end_date = strtotime(get_post_meta( $post_id, '_EventEndDate', true ) . (tribe_get_all_day() ? " + 1 day" : ""));
		$dates = ( tribe_get_all_day() ) ? date('Ymd', $start_date) . '/' . date('Ymd', $end_date) : date('Ymd', $start_date) . 'T' . date('Hi00', $start_date) . '/' . date('Ymd', $end_date) . 'T' . date('Hi00', $end_date);
		$location = trim( tribe_get_full_address($post_id, true) );
		
		$base_url = 'http://www.google.com/calendar/event';
		$params = array(
			'action' => 'TEMPLATE',
			'text' => strip_tags(get_the_title()),
			'dates' => $dates,
			'details' => strip_tags( get_the_excerpt() ),
			'location' => $location,
			'sprop' => get_option('blogname'),
			'trp' => 'false',
			'sprop' => 'website:' . home_url()
		);
		$url = add_query_arg( $params, $base_url );
		return esc_html($url);
	}
		
	function tribe_get_current_template() {
		return TribeEventsTemplates::get_current_page_template();
	}
	
	
	/* Venue Template Tags */
	
	/*
 	 * Returns the venue name
	 *
	 * @return string country
	 */
	function tribe_venue_get_name( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueVenue', true ));
	}
	/* 
	 * Returns the event country
	 *
	 * @return string country
	 */
	function tribe_venue_get_country( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueCountry', true ));
	}
	/**
	 * Returns the event address
	 *
	 * @return string address
	 */
	function tribe_venue_get_address( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueAddress', true ));
	}
	/**
	 * Returns the event city
	 *
	 * @return string city
	 */
	function tribe_venue_get_city( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueCity', true ));
	}
	/**
	 * Returns the event state or Province
	 *
	 * @return string state
	 */
	function tribe_venue_get_stateprovince( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueStateProvince', true ));
	}
	/**
	 * Returns the event state
	 *
	 * @return string state
	 */
	function tribe_venue_get_state( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueState', true ));
	}
	/**
	 * Returns the event province
	 *
	 * @return string province
	 */
	function tribe_venue_get_province( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueProvince', true ));
	}
	/**
	 * Returns the event zip code
	 *
	 * @return string zip code 
	 */
	function tribe_venue_get_zip( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenueZip', true ));
	}
	/**
	 * Returns the event phone number
	 *
	 * @return string phone number
	 */
	function tribe_venue_get_phone( $postId = null)  {
		$postId = tribe_post_id_helper( $postId );
		return esc_html(tribe_get_event_meta( $postId, '_VenuePhone', true ));
	}	
	
	/**
	 * Returns the state or province for US or non-US addresses
	 *
	 * @return string
	 */
	function tribe_venue_get_region( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		if(tribe_get_event_meta($postId, '_VenueStateProvince', true )){
			return tribe_get_event_meta($postId, '_VenueStateProvince', true );
		}else
		if ( tribe_venue_get_country() == __('United States', $tribe_ecp->pluginDomain ) ) {
			return tribe_venue_get_state();
		} else {
			return tribe_venue_get_province(); 
		}
	}	
	
	/**
	 * @return string formatted event address
	 */
	function tribe_venue_get_full_address( $postId = null, $includeName = false )  {
		$postId = tribe_post_id_helper( $postId );
		$address = array();
		if( $includeVenue ) {
			$address[] = tribe_venue_get_name( $postId );
		}
		if( tribe_venue_get_address( $postId ) ) {
			$address[] = tribe_venue_get_address( $postId );
		}

		$cityregion = '';
		if( tribe_venue_get_city( $postId ) ) {
			$cityregion .= tribe_venue_get_city( $postId );
		}
		if( tribe_venue_get_region( $postId ) ) {
			if( $cityregion != '' ) $cityregion .= ', ';
			$cityregion .= tribe_venue_get_region( $postId );
		}
		if( $cityregion != '' ) $address[] = $cityregion;
		
		if( tribe_venue_get_zip( $postId ) ) {
			$address[] = tribe_venue_get_zip( $postId );
		}
		if( tribe_venue_get_country( $postId ) ) {
			$address[] = tribe_venue_get_country( $postId );
		}
		return join("</br>\n",$address);
	}
	/**
	 * Displays a formatted event address
	 *
	 * @param string $postId 
	 * @return void
	 */
	function tribe_venue_the_full_address( $postId = null )  {
		echo tribe_venue_get_full_address( $postId );
	}
	/**
	 * @return boolean true if any part of an address exists
	 */
	function tribe_venue_address_exists( $postId = null )  {
		$postId = tribe_post_id_helper( $postId );
		return ( tribe_venue_get_address( $postId ) || tribe_venue_get_city( $postId ) || tribe_venue_get_region( $postId ) || tribe_venue_get_country( $postId ) || tribe_venue_get_zip( $postId ) ) ? true : false;
	}	
	
/**
	 * Returns an embedded google maps for the given event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 */
	function tribe_venue_get_embedded_map( $postId = null, $width = '', $height = '' )  {
		$tribe_ecp = TribeEvents::instance();

		$postId = tribe_post_id_helper( $postId );
		if ( !tribe_is_venue( $postId ) ) return false;
		
		$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
		$toUrlEncode = "";

		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = call_user_func('tribe_venue_get_'.$val);
			if ( $metaVal ) 
				$toUrlEncode .= $metaVal . " ";
		}

		if ( $toUrlEncode ) 
			$address = $toUrlEncode;
		else
			$address = null;		

		if (!$height) $height = tribe_get_option('embedGoogleMapsHeight','350');
		if (!$width) $width = tribe_get_option('embedGoogleMapsWidth','100%');

		if ($address) {
			ob_start();
			include($tribe_ecp->pluginPath.'admin-views/event-map.php');
			$google_map = ob_get_contents();
			ob_get_clean();
			return $google_map;
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
	function tribe_venue_the_embedded_map( $postId = null, $width = null, $height = null )  {
		if (tribe_get_option('embedGoogleMaps'))
			echo tribe_get_embedded_map( $postId, $width, $height );
	}	
	
	/**
	 * Template function: 
	 * @return boolean
	 */
	function tribe_is_venue( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		return $tribe_ecp->isVenue($postId);
	}

   function tribe_events_before_html() {
      echo stripslashes(tribe_get_option('spEventsBeforeHTML'));
   }

   function tribe_events_after_html() {
      echo stripslashes(tribe_get_option('spEventsAfterHTML'));
   }

} // end if class_exists('The-Events-Calendar')
?>
