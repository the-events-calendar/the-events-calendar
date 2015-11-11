<?php


class Tribe__Events__Pro__Recurrence__Scripts {


	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * Singleton constructor for the class.
	 *
	 * @return Tribe__Events__Pro__Recurrence__Scripts
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param array  $data          The data to be localized.
	 * @param string $object_name   The localization object var name.
	 * @param        $script_handle The handle the localization should be attached to.
	 *
	 * @return mixed
	 */
	public function localize( $data, $object_name, $script_handle ) {
		if ( ! isset( $data['recurrence'] ) ) {
			$data['recurrence'] = array();
		}
		$data['recurrence'] = array_merge( $data['recurrence'], array(
			'splitAllMessage'               => __( "You are about to split this series in two.\n\nThe event you selected and all subsequent events in the series will be separated into a new series of events that you can edit independently of the original series.\n\nThis action cannot be undone.", 'tribe-events-calendar-pro' ),
			'splitSingleMessage'            => __( "You are about to break this event out of its series.\n\nYou will be able to edit it independently of the original series.\n\nThis action cannot be undone.", 'tribe-events-calendar-pro' ),
			'bulkDeleteConfirmationMessage' => __( 'Are you sure you want to trash all occurrences of these events?', 'tribe-events-calendar-pro' ),
		) );

		return $data;
	}
}