<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Errors {
	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		tribe_register_error( 'core:aggregator:attachment-error', __( 'The image associated with your event could not be attached to the event.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:daily-limit-reached', __( 'The daily limit of %d import requests to the Event Aggregator service has been reached. Please try again later.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-failed', __( 'You cannot delete a history record (ID: "%d"). ', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-permissions', __( 'You do not have permission to delete this record.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:http_request-limit', __( 'During scheduled import, the limit of HTTP requests was reached and the import was rescheduled.', 'the-events-calendar' ) );

		tribe_register_error( 'core:aggregator:invalid-create-record-type', __( 'An invalid import type was used when trying to create this import record.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-file', __( 'You must provide a valid CSV file to perform a CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-parameters', __( 'Invalid data provided for CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-edit-record-type', __( 'Only scheduled import records can be edited.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-id', __( 'Unable to find an event with the ID of %s.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-json', __( 'The Event Aggregator API responded with bad data. Please <a href="https://theeventscalendar.com/support/post/" target="_blank">contact support</a>.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-facebook-token', __( 'We received an invalid Facebook Token from the Service.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-import-id', __( 'Unable to find an import record with the ID of %s.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-image-path', __( 'Unable to attach an image to the event', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-frequency', __( 'An invalid frequency was used when trying to create this scheduled import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-object', __( 'Unable to find a matching post.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-origin', __( 'The import record is missing the origin.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-post_type', __( 'Unable to get a post of the correct type.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-key', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses before using this service.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-response', __( 'There may be an issue with the Event Aggregator server. Please try your import again later.', 'the-events-calendar' ) );

		tribe_register_error( 'core:aggregator:missing-csv-column-map', __( 'You must map columns from the CSV file to specific fields in order to perform a CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:missing-csv-file', __( 'The CSV file cannot be found. You may need to re-upload the file.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:record-not-finalized', __( 'Import records must be finalized before posts can be inserted.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-child-failed', __( 'Unable to save scheduled import instance. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-schedule-failed', __( 'Unable to save scheduled import. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:queue-pending-events', __( 'The records you were attempting to import were still not available when this queue was processed. Please try again.', 'the-events-calendar' ) );
	}

	/**
	 * Maybe build message from args
	 *
	 * @param string $message
	 * @param array $args Message args
	 *
	 * @return string
	 */
	public static function build( $message, $args = array() ) {
		return vsprintf( $message, $args );
	}
}
