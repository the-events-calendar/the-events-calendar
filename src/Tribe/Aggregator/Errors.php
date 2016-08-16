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
		tribe_register_error( 'core:aggregator:attachment-error', __( 'Unable to create an attachment post for the imported Event Aggregator image', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-json', __( 'Could not convert event JSON to an event array because it is missing required fields', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:http_request-limit', __( 'The Limit of HTTP requests per Cron has been Reached', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-create-record-type', __( 'An invalid Type was used to setup this Record', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-edit-record-type', __( 'Editing can only be done to scheduled import records.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-frequency', __( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-schedule-failed', __( 'Unable to save schedule. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-child-failed', __( 'Unable to save child record. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-response', __( 'An unexpected response was received from the Event Aggregator service', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-failed', __( 'You cannot delete a History Record. ID: "%d"', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:daily-limit-reached', __( 'The Aggregator import limit of %d for the day has already been reached.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:record-not-finalized', __( 'Posts cannot be inserted from an unfinalized import record', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-permissions', __( 'You do not have pessimions for deleting this Record.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-key', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-file', __( 'You must provide a valid CSV file in order to do CSV imports.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-parameters', __( 'Invalid data provided for CSV imports.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:missing-csv-column-map', __( 'CSV imports must map columns for import', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:missing-csv-file', __( 'The file went away. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-origin', __( 'The Import Record is missing the origin meta key', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-import-id', __( 'Unable to find an Import Record with the import_id of %s', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-id', __( 'Invalid Event: %s', 'the-events-calendar' ) );
	}
}
