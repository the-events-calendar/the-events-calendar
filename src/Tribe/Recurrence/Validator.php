<?php


class Tribe__Events__Pro__Recurrence__Validator {

	/**
	 * Recurrence validation method.  This is checked after saving an event, but before splitting a series out into
	 * multiple occurrences
	 *
	 * @param int   $event_id        The event object that is being saved
	 * @param array $recurrence_meta Recurrence information for this event
	 *
	 * @return bool
	 */
	public static function is_valid( $event_id, array $recurrence_meta ) {
		$valid    = true;
		$errorMsg = '';

		if ( isset( $recurrence_meta['type'] ) && 'Custom' === $recurrence_meta['type'] ) {
			if ( ! isset( $recurrence_meta['custom']['type'] ) ) {
				$valid    = false;
				$errorMsg = __( 'Custom recurrences must have a type selected.', 'tribe-events-calendar-pro' );
			} elseif ( ! isset( $recurrence_meta['custom']['start-time'] ) && ! isset( $recurrence_meta['custom']['day'] ) && ! isset( $recurrence_meta['custom']['week'] ) && ! isset( $recurrence_meta['custom']['month'] ) && ! isset( $recurrence_meta['custom']['year'] ) ) {
				$valid    = false;
				$errorMsg = __( 'Custom recurrences must have all data present.', 'tribe-events-calendar-pro' );
			} elseif ( 'Monthly' === $recurrence_meta['custom']['type'] && ( empty( $recurrence_meta['custom']['month']['day'] ) || empty( $recurrence_meta['custom']['month']['number'] ) || '-' === $recurrence_meta['custom']['month']['day'] || '' === $recurrence_meta['custom']['month']['number'] ) ) {
				$valid    = false;
				$errorMsg = __( 'Monthly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
			} elseif ( 'Yearly' === $recurrence_meta['custom']['type'] && ( empty( $recurrence_meta['custom']['year']['month-day'] ) || '-' === $recurrence_meta['custom']['year']['month-day'] ) ) {
				$valid    = false;
				$errorMsg = __( 'Yearly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
			}
		}

		if ( ! $valid ) {
			do_action( 'tribe_recurring_event_error', $event_id, $errorMsg );
		}

		return $valid;
	}
}