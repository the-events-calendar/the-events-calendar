<?php
class Events_Recurrence_Meta {
	public static function init() {
		add_action( 'save_post', array( 'Events_Recurrence_Meta', 'saveRecurrenceMeta' ), 17, 2 );
	}

	public static function getRecurrenceMeta( $postId ) {
		// TODO: Load these from request if validation failed
		$recurrenceData = get_post_meta($postId, 'recurrence', true);

		$recArray = array();

		$recArray['recType'] = $recurrenceData['type'];
		$recArray['recEndType'] = $recurrenceData['end-type'];
		$recArray['recEnd'] = $recurrenceData['end'];
		
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

		return $recArray;
	}

	public static function saveRecurrenceMeta( $postId, $post ) {
		// only continue if it's an event post
		if ( $post->post_type != Events_Calendar_Pro::POSTTYPE ) {
			return;
		}
		// don't do anything on autosave or auto-draft either or massupdates
		if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ) {
			return;
		}

		$recurrence_meta = $_REQUEST['recurrence'];

		// TODO: Validate
		update_post_meta($postId, 'recurrence', $recurrence_meta);
		Events_Recurrence_Meta::saveEvents();
	}

	public static function saveEvents( $postId, $post )
	{
		
	}
}
?>