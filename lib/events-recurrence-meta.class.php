<?php
// WordPress Hooks and Filters for events recurrence meta
class Events_Recurrence_Meta {
	const UPDATE_TYPE_ALL = 1;
	const UPDATE_TYPE_FUTURE = 2;
	const UPDATE_TYPE_SINGLE = 3;
	
	public static function init() {
		add_action('pre_post_update', array( __CLASS__, 'maybeBreakFromSeries' ));
	}
	
	public static function maybeBreakFromSeries( $postId ) {
		// make new series for future events
		if($_POST['recurrence_action'] && $_POST['recurrence_action'] == Events_Recurrence_Meta::UPDATE_TYPE_FUTURE) {
			
			// move recurrence end to the last date of the series before today
			$numOccurrences = self::adjustRecurrenceEnd( $postId );			
			
			// prune future occurrences on original event
			self::removeFutureOccurrences( $postId );
			
			if ($_POST['recurrence']['end-type'] == 'After') {
				// num occurrences for new series is total occurrences minus occurrences still in original series
				$_POST['recurrence']['end-count'] = $_POST['recurrence']['end-count'] - $numOccurrences;
			}

			// redirect form to new event
			$post = self::cloneEvent( $_POST );
			
			// remove past occurrences of new event
			self::removePastOccurrences( $post );
			// actual event end time potentially needs to be adjusted up
			self::adjustEndDate( $post );

			// clear this so no infinite loop - clear after new post is inserted so it can be used in the recurrence logic
			$_POST['recurrence_action'] = null;

			// redirect back to event screen
			wp_redirect('post.php?post=' . $post . '&action=edit&message=1');
			exit();
		// break from series			
		} else if($_POST['recurrence_action'] && $_POST['recurrence_action'] == Events_Recurrence_Meta::UPDATE_TYPE_SINGLE) {
			// new event should have no recurrence
			$_REQUEST['recurrence'] = $_POST['recurrence'] = null;

			// create new event
			$post = self::cloneEvent( $_POST );
			
			// remove this occurrance from the original series
			self::removeOccurrence( $postId, $_POST['EventStartDate'] );

			// the end date on original series will need to be moved if it was the first event in the series removed
			self::adjustEndDate( $postId );

			$_POST['recurrence_action'] = null;

			// redirect back to event screen
			wp_redirect('post.php?post=' . $post . '&action=edit&message=1');		
			exit();
		}
	}
	
	private static function removeOccurrence( $postId, $date ) {
		$startDate = self::getRealStartDate($postId);
		$date = DateUtils::addTimeToDate( $date, DateUtils::timeOnly($startDate) );

		delete_post_meta( $postId, '_EventStartDate', $date );
	}
	
	// sorts the meta to ensure we are getting the real start date
	private static function getRealStartDate( $postId ) {
		$start_dates = get_post_meta( $postId, '_EventStartDate' );
		
		if( is_array( $start_dates ) && sizeof( $star_dates ) > 0 ) {
			sort($start_dates);
			return $start_dates[0];
		}
		
		return null;
	}
	
	private static function removeFutureOccurrences( $postId ) {
		$occurrences = get_post_meta($postId, '_EventStartDate');
		
		foreach($occurrences as $occurrence) {
			if (strtotime($occurrence) > time() ) {
				delete_post_meta($postId, '_EventStartDate', $occurrence);
			}
		}
	}
	
	private static function adjustRecurrenceEnd( $postId ) {
		$occurrences = get_post_meta($postId, '_EventStartDate');
		$occurrenceCount = 0;
		sort($occurrences);
		
		if( is_array($occurrences) && sizeof($occurrences) > 0 ) {
			$prev = $occurrences[0];
		}
				  
		foreach($occurrences as $occurrence) {
			$occurrenceCount++; // keep track of how many we are keeping
			if (strtotime($occurrence) > time() ) {
				$recurrenceMeta = get_post_meta($postId, 'recurrence', true);
				$recurrenceMeta['end'] = date(DateSeriesRules::DATE_ONLY_FORMAT, strtotime($prev));
				update_post_meta($postId, 'recurrence', $recurrenceMeta);
				break;
			}
			
			$prev = $occurrence;
		}
		
		// useful for knowing how many occurrences are needed for new series		
		return $occurrenceCount; 
	}
	
	private static function removePastOccurrences( $postId ) {
		$occurrences = get_post_meta($postId, '_EventStartDate');
		
		foreach($occurrences as $occurrence) {
			if (strtotime($occurrence) < time() ) {
				delete_post_meta($postId, '_EventStartDate', $occurrence);
			}
		}
	}	
	
	private static function adjustEndDate( $postId ) {
		$occurrences = get_post_meta($postId, '_EventStartDate');
		sort($occurrences);

		$duration = get_post_meta($postId, '_EventDuration', true);
		
		if( is_array($occurrences) && sizeof($occurrences) > 0 ) {
			update_post_meta($postId, '_EventEndDate', date(DateSeriesRules::DATE_FORMAT, strtotime($occurrences[0]) + $duration));
		}	
	}	
	
	
	private static function cloneEvent( $data ) {
		global $sp_ecp;
		
		$data['ID'] = null;
		$new_event = wp_insert_post($data);
		return $new_event;
	}		

	public static function getRecurrenceMeta( $postId ) {
		// TODO: Load these from request if validation failed
		$recurrenceData = get_post_meta($postId, '_EventRecurrence', true);

		$recArray = array();

		if ( $recurrenceData ) {
			$recArray['recType'] = $recurrenceData['type'];
			$recArray['recEndType'] = $recurrenceData['end-type'];
			$recArray['recEnd'] = $recurrenceData['end'];
			$recArray['recEndCount'] = $recurrenceData['end-count'];

			$recArray['recCustomType'] = $recurrenceData['custom-type'];
			$recArray['recCustomInterval'] = $recurrenceData['custom-interval'];
			$recArray['recCustomTypeText'] = $recurrenceData['custom-type-text'];

			$recArray['recCustomWeekDay'] = $recurrenceData['custom-week-day'];

			$recArray['recCustomMonthType'] = $recurrenceData['custom-months-type'];
			$recArray['recCustomMonthDayOfMonth'] = $recurrenceData['custom-month-day-of-month'];
			$recArray['recCustomMonthNumber'] = $recurrenceData['custom-month-number'];
			$recArray['recCustomMonthDay'] = $recurrenceData['custom-month-day'];

			$recArray['recCustomYearMonth'] = $recurrenceData['custom-year-month'] ?  $recurrenceData['custom-year-month'] : array();
			$recArray['recCustomYearFilter'] = $recurrenceData['custom-year-filter'];
			$recArray['recCustomYearMonthNumber'] = $recurrenceData['custom-year-month-number'];
			$recArray['recCustomYearMonthDay'] = $recurrenceData['custom-year-month-day'];
		}

		return $recArray;
	}

	public static function saveRecurrenceMeta( $postId, $post ) {	
		
		// only continue if it's an event post
		if ( $post->post_type != Events_Calendar_Pro::POSTTYPE ) {
			return;
		}

  	   if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;			

		// don't do anything on autosave or auto-draft either or massupdates
		if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ) {
			return;
		}

		$recurrence_meta = $_REQUEST['recurrence'];

		// TODO: Validate
		update_post_meta($postId, '_EventRecurrence', $recurrence_meta);
		Events_Recurrence_Meta::saveEvents($postId, $post);
	}

	public static function saveEvents( $postId, $post) {

		extract(Events_Recurrence_Meta::getRecurrenceMeta($postId));
		$rules = Events_Recurrence_Meta::getSeriesRules($postId);

		// use the recurrence start meta if necessary because we can't guarantee which order the start date will come back in
		$recStart = strtotime(get_post_meta($postId, '_EventStartDate', true));
		$eventEnd = strtotime(get_post_meta($postId, '_EventEndDate', true));
		$duration = $eventEnd - $recStart;
		$recEnd = $recEndType == "On" ? $recEnd : $recEndCount - 1; // subtract one because event is first occurrence

		// different update types
		delete_post_meta($postId, '_EventStartDate');
		delete_post_meta($postId, '_EventEndDate');
		delete_post_meta($postId,'_EventDuration');

		// add back original start and end date
		add_post_meta($postId,'_EventStartDate', date(DateSeriesRules::DATE_FORMAT, $recStart));
		add_post_meta($postId,'_EventEndDate', date(DateSeriesRules::DATE_FORMAT, $eventEnd));
		add_post_meta($postId,'_EventDuration', $duration);

		if ( $recType != "None") {
			$recurrence = new Recurrence($recStart, $recEnd, $rules);
			$dates = $recurrence->getDates();

			// add meta for all dates in recurrence
			foreach($dates as $date) {
				add_post_meta($postId,'_EventStartDate', date(DateSeriesRules::DATE_FORMAT, $date));
			}
		}
	}

	public static function getSeriesRules($postId) {
		extract(Events_Recurrence_Meta::getRecurrenceMeta($postId));
		$rules = null;

		if(!$recCustomInterval)
			$recCustomInterval = 1;

		if($recType == "Every Day" || ($recType == "Custom" && $recCustomType == "Daily")) {
			$rules = new DaySeriesRules($recType == "Every Day" ? 1 : $recCustomInterval);
		} else if($recType == "Every Week") {
			$rules = new WeekSeriesRules(1);
		} else if ($recType == "Custom" && $recCustomType == "Weekly") {
			$rules = new WeekSeriesRules($recType == "Every Week" ? 1 : $recCustomInterval, $recCustomWeekDay);
		} else if($recType == "Every Month") {
			$rules = new MonthSeriesRules(1);
		} else if($recType == "Custom" && $recCustomType == "Monthly") {
			$rules = new MonthSeriesRules($recType == "Every Month" ? 1 : $recCustomInterval, $recCustomMonthDayOfMonth, $recCustomMonthNumber, $recCustomMonthDay);
		} else if($recType == "Every Year") {
			$rules = new YearSeriesRules(1);
		} else if($recType == "Custom" && $recCustomType == "Yearly") {
			$rules = new YearSeriesRules($recType == "Every Year" ? 1 : $recCustomInterval, $recCustomYearMonth, $recCustomYearFilter ? $recCustomYearMonthNumber : null, $recCustomYearFilter ? $recCustomYearMonthDay : null);
		}

		return $rules;
	}
}
?>