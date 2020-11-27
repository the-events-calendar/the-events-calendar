<?php

class Tribe__Events__Aggregator__CLI__Command {
	/**
	 * @var int The polling interval timeout in seconds.
	 */
	protected $polling_timeout = 30;

	/**
	 * @var int The polling interval in seconds.
	 */
	protected $polling_interval = 2;

	/**
	 * Run an import of the specified type from a specified source.
	 *
	 * The command will use the API and licenses set for the site if required.
	 *
	 * <origin>
	 * : the import origin type
	 * ---
	 * options:
	 *   - ical
	 *   - gcal
	 *   - csv
	 *   - ics
	 *   - facebook
	 *   - meetup
	 *   - url
	 * ---
	 *
	 * <source>
	 * : The source to import events from; a URL or a file path for .ics and CSV files.
	 *
	 * [--keywords=<keywords>]
	 * : Optionally filter events by these keywords.
	 *
	 * [--location=<location>]
	 * : Filter events by this location, not supported by all origin types.
	 *
	 * [--radius=<radius>]
	 * : Only fetch events in this mile radius around the location.
	 * Will be ignored if the `--location` parameter is not set.
	 *
	 * [--start=<start>]
	 * : Only fetch events starting after this date.
	 * This should be a valid date string or a value supported by the `strtotime` PHP function.
	 * Not supported by all origin types.
	 *
	 * [--end=<end>]
	 * : Only fetch events starting before this date.
	 * This should be a valid date string or a value supported by the `strtotime` PHP function.
	 * When using natural language expressions keep in mind that those apply from the current time, not start.
	 * Not supported by all origin types.
	 * Defaults the range set in the import settings for this origin type.
	 *
	 * [--limit_type=<limit_type>]
	 * : The type of limit that should be used to limit the number of fetched events.
	 * ---
	 * options:
	 *   - count
	 *   - range
	 *   - no_limit
	 * ---
	 *
	 * [--limit=<limit>]
	 * : The value of the limit that should be applied; ignored if `--limit_type` is not set or set to `no_limit`.
	 * Either a value in seconds if the `--limit_type` is range or a number if `--limit_type` is set to `count`.
	 * When importing CSV files this limit will NOT apply.
	 *
	 * [--timeout=<timeout>]
	 * : How long should the command wait for the data from EA Service in seconds
	 * ---
	 * default: 30
	 * ---
	 *
	 * [--post_status=<post_status>]
	 * : The post status that should be assigned to the imported events; default to the one set in Import options.
	 * ---
	 * options:
	 *   - publish
	 *   - draft
	 *   - pending
	 *   - private
	 * ---
	 *
	 * [--category=<category>]
	 * : An optional category that should be assigned to the imported events.
	 *
	 * [--content_type=<content_type>]
	 * : The type of import for CSV files.
	 * The column mapping must be defined with the `--column_map` parameter.
	 * ---
	 * default: events
	 * options:
	 *   - events
	 *   - venues
	 *   - organizers
	 * ---
	 *
	 * [--column_map=<column_map>]
	 * : the column mapping that should be used for CSV imports; required when runnin CSV imports. A comma separated
	 * list where the order counts.
	 * For events the available columns are: name, description, excerpt, start_date, start_time, end_date, end_time,
	 * timezone, all_day, hide, sticky, venue_name, organizer_name, show_map_link, show_map, cost, currency_symbol,
	 * currency_position, category, tags, website, comment_status, ping_status, featured_image, feature_event
	 * For venues the available columns are: name, description, country, address, address2, city, state, zip, phone,
	 * url, featured_image
	 * For organizers the available columns are: name, description, email, website, phone, featured_image
	 *
	 * [--format=<format>]
	 * : The results output format
	 * ---
	 *
	 * ## Examples
	 *
	 *      wp event-aggregator import-from ical https://some-ical-source/feed.ics
	 *      wp event-aggregator import-from ical https://some-ical-source/feed.ics --start=tomorrow --end="+3 weeks"
	 *      wp event-aggregator import-from ical https://some-ical-source/feed.ics --limit_type=count --limit=20
	 *      wp event-aggregator import-from ical https://some-ical-source/feed.ics --location="Toronto" --radius=50
	 *      wp event-aggregator import-from ical https://some-ical-source/feed.ics --keywords=Party
	 *      wp event-aggregator import-from meetup https://www.meetup.com/wordpress-ile-de-france/
	 *      wp event-aggregator import-from gcal https://calendar.google.com/calendar/ical/me/public/basic.ics
	 *      wp event-aggregator import-from csv /Users/moi/events.csv --content_type=events --column_map=name,description,start_date,start_time,end_date,end_time
	 *      wp event-aggregator import-from ics /Users/moi/events.ics
	 *
	 *
	 * @since      4.6.15
	 *
	 * @subcommand import-from
	 *
	 * @when       after_wp_load
	 */
	public function import_from_source( array $args, array $assoc_args = [] ) {
		$this->ensure_timeout( $assoc_args );

		list( $origin, $source ) = $args;

		$is_csv = 'csv' === $origin;

		if ( $is_csv ) {
			$this->ensure_column_map( $assoc_args );
		}

		$record = $this->create_record_from( $assoc_args, $origin, $source );

		$this->fetch_and_process( $assoc_args, $record, $is_csv );
	}

	/**
	 * Check the timeout parameter if set.
	 *
	 * @since 4.6.15
	 */
	protected function ensure_timeout( array $assoc_args ) {
		if ( isset( $assoc_args['timeout'] ) && ! is_numeric( $assoc_args['timeout'] ) ) {
			WP_CLI::error( 'The timeout should be a numeric value.' );
		}
	}

	/**
	 * Creates a new record.
	 *
	 * @since 4.6.15
	 *
	 * @param array $assoc_args
	 * @param string $origin
	 * @param string $source
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract
	 */
	protected function create_record_from( array $assoc_args, $origin, $source ) {
		$is_csv = 'csv' === $origin;

		$types = [
			'ical'   => 'Tribe__Events__Aggregator__Record__iCal',
			'gcal'   => 'Tribe__Events__Aggregator__Record__gCal',
			'csv'    => 'Tribe__Events__Aggregator__Record__CSV',
			'ics'    => 'Tribe__Events__Aggregator__Record__ICS',
			'meetup' => 'Tribe__Events__Aggregator__Record__Meetup',
			'url'    => 'Tribe__Events__Aggregator__Record__Url',
		];

		$record_class = Tribe__Utils__Array::get( $types, $origin, reset( $types ) );

		/** @var Tribe__Events__Aggregator__Record__Abstract $record */
		$record = new $record_class;

		$record_args = [];

		$location   = Tribe__Utils__Array::get( $assoc_args, 'location' );
		$limit_type = Tribe__Utils__Array::get( $assoc_args, 'limit_type' );
		$limit      = Tribe__Utils__Array::get( $assoc_args, 'limit' );

		if ( isset( $assoc_args['start'], $assoc_args['end'] ) ) {
			$limit_type = 'no_limit';
			$limit      = 'not_set';
		}

		$category    = Tribe__Utils__Array::get( $assoc_args, 'category', '' );
		$category_id = '';

		if ( is_numeric( $category ) ) {
			$category_id = $category;
		} elseif ( ! empty( $category ) ) {
			$term = get_term_by( 'slug', $category, Tribe__Events__Main::TAXONOMY );
			if ( ! $term instanceof WP_Term ) {
				WP_CLI::error( "$category is not a valid category ID or slug." );
			}
			$category_id = $term->term_id;
		}

		$record_meta = [
			'origin'     => $origin,
			'type'       => 'manual',
			'keywords'   => Tribe__Utils__Array::get( $assoc_args, 'keywords', '' ),
			'location'   => $location,
			'start'      => Tribe__Utils__Array::get( $assoc_args, 'start' ),
			'end'        => Tribe__Utils__Array::get( $assoc_args, 'end' ),
			'radius'     => $location ? Tribe__Utils__Array::get( $assoc_args, 'radius' ) : null,
			'limit_type' => $limit_type,
			'limit'      => $limit,
			'source'     => $source,
			'preview'    => false,
			'category'   => $category_id,
		];

		if ( ! empty( $record_meta['start'] ) ) {
			$record_meta['start'] = Tribe__Date_Utils::reformat( $record_meta['start'], 'Y-m-d H:i:s' );
			if ( empty( $record_meta['start'] ) ) {
				WP_CLI::error( 'The --start parameter could not be parsed; review the argument description.' );
			}
		}

		if ( ! empty( $record_meta['end'] ) ) {
			$record_meta['end'] = Tribe__Date_Utils::reformat( $record_meta['end'], 'Y-m-d H:i:s' );
			if ( empty( $record_meta['end'] ) ) {
				WP_CLI::error( 'The --end parameter could not be parsed; review the argument description.' );
			}
		}

		if ( isset( $record_meta['start'], $record_meta['end'] ) ) {
			if ( strtotime( $record_meta['end'] ) < strtotime( $record_meta['start'] ) ) {
				WP_CLI::error( "End date [{$record_meta['end']}] cannot be before start date [{$record_meta['start']}]; review the argument description." );
			}
		}

		if ( $is_csv ) {
			$record_meta['file']         = $source;
			$record_meta['content_type'] = Tribe__Utils__Array::get( $assoc_args, 'content_type', 'events' );
		}

		if ( 'ics' === $origin ) {
			$record_meta['file'] = [
				'name'     => $source,
				'tmp_name' => $source,
			];
		}

		if ( empty( $assoc_args['post_status'] ) ) {
			$record_meta['post_status'] = tribe( 'events-aggregator.settings' )->default_post_status( $origin );
		} else {
			$record_meta['post_status'] = $assoc_args['post_status'];
		}

		WP_CLI::log( 'Creating record post...' );

		$created = $record->create( 'manual', $record_args, $record_meta );

		if ( $created instanceof WP_Error ) {
			WP_CLI::error( 'There was an error while creating the import: ' . $created->get_error_message() );
		}

		WP_CLI::log( "Record created with post ID {$record->id}." );

		return $record;
	}

	/**
	 * Fetches the data from the Service and processes it.
	 *
	 * @since 4.6.15
	 *
	 * @param array $assoc_args
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 * @param bool $is_csv
	 *
	 * @return array
	 */
	protected function fetch_and_process( array $assoc_args, $record, $is_csv ) {
		$queue_import_args = [];

		// remove anything that cannot be serialized
		foreach ( $record->meta as $key => $value ) {
			if ( is_array( $value ) || is_scalar( $value ) ) {
				$queue_import_args[ $key ] = $value;
			}
		}

		$queue_result = $record->queue_import( $queue_import_args );

		$record->finalize();

		$this->polling_timeout = (float) $assoc_args['timeout'];

		if ( $is_csv ) {
			$column_map = $assoc_args['column_map'];
			$activity   = $this->import_csv_file( $queue_result, $record, $column_map );
		} else {
			$activity = $this->import_from_service( $queue_result, $record );
		}

		if ( ! $activity instanceof Tribe__Events__Aggregator__Record__Activity ) {
			if ( $activity instanceof WP_Error ) {
				WP_CLI::error( $activity->get_error_message() );
			} else {
				WP_CLI::error( 'Something went wrong during the import process.' );
			}
			$record->set_status_as_failed();
		}

		$assoc_args['format'] = ! empty( $assoc_args['format'] ) ? $assoc_args['format'] : 'yaml';

		$items = $activity->get();

		// just a "cosmetic" refinement to make sure integers will be rendered as integers
		foreach ( $items as $type ) {
			foreach ( $type as &$action ) {
				$action = array_map( function ( $entry ) {
					return is_numeric( $entry ) ? (int) $entry : $entry;
				}, $action );
			}
		}

		WP_CLI::print_value( $items, $assoc_args );

		WP_CLI::success( 'Import done!' );

		$record->update_meta( 'activity', $activity );
		$record->delete_meta( 'queue' );
		$record->set_status_as_success();

		return $action;
	}

	/**
	 * Imports a CSV file.
	 *
	 * The logic to handle and import CSV files is different, primarily in it not relying on the Service, from
	 * other imports. Mind that CSV source files should have their columns in exactly the same order and named
	 * exactly as those found in the UI.
	 *
	 * @since 4.6.15
	 *
	 * @param Tribe__Events__Aggregator__Record__CSV $record
	 * @param array $record_meta
	 * @param string|array $column_map The column map that should be used for the import, either a comma-separated list
	 *                                 or an array.
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	protected function import_csv_file( $queue_result, $record, $column_map ) {
		WP_CLI::log( 'Reading the file...' );

		if ( ! is_array( $column_map ) ) {
			$column_map = preg_split( '/\\s*,\\s*/', $column_map );
		}

		if ( empty( $column_map ) ) {
			WP_CLI::error( 'The provided column map is invalid.' );
		}

		$map    = [
			'events'     => 'event',
			'venues'     => 'venue',
			'organizers' => 'organizer',
		];
		$prefix = Tribe__Utils__Array::get( $map, $record->meta['content_type'], 'event' );

		$column_map = array_map( function ( $key ) use ( $prefix ) {
			return $key === 'featured_image' || $key === 'feature_event' ? $key : $prefix . '_' . $key;
		}, $column_map );

		$data = [
			'action'        => 'new',
			'import_id'     => $record->id,
			'origin'        => 'csv',
			'csv'           =>
				[
					'content_type' => 'tribe_' . $record->meta['content_type'],
					'file'         => $record->meta['file'],
				],
			'column_map'    => $column_map,
			'post_status'   => $record->meta['post_status'],
			'category'      => $record->meta['category'],
			'selected_rows' => 'all',
		];

		$response = $queue_result;

		if ( $response instanceof WP_Error ) {
			WP_CLI::error( 'There was an error while reading the file: ' . $response->get_error_message() );
		}

		if ( isset( $response['data']['items'] ) && $response['data']['items'] instanceof WP_Error ) {
			WP_CLI::error( $response['data']['items']->get_error_message() );
		}

		$item_name = rtrim( $record->meta['content_type'], 's' );
		$items     = $response['data']['items'];

		if ( empty( $items ) ) {
			WP_CLI::success( 'No items found matching the query.' );
		}

		$items_count = count( $items );

		WP_CLI::log( "Read data for {$items_count} {$item_name}(s) from the file." );

		add_filter( 'tribe_aggregator_batch_size', function () use ( $items_count ) {
			return $items_count + 1;
		} );

		/** @var Tribe__Events__Aggregator__Record__Queue_Interface $result */
		$result = $record->process_posts( $data );

		if ( $result instanceof WP_Error ) {
			WP_CLI::error( $result->get_error_message() );
		}

		$activity = $result->activity();

		return $activity;
	}

	/**
	 * Imports the data for a record from the Service.
	 *
	 * This is a full end-to-end handling of the request; the method will queue the import on the Service,
	 * fetch the data from it and import the returned data (if any).
	 *
	 * @param array $assoc_args
	 * @param object|WP_Error $queue_result The result of the queue operation on the Service
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	protected function import_from_service( $queue_result, $record ) {
		$item_name = 'event';
		WP_CLI::log( 'Creating import on the service...' );

		if ( $queue_result instanceof WP_Error ) {
			WP_CLI::error( 'There was an error while queueing the import on the service: ' . $queue_result->get_error_message() );
		}

		WP_CLI::log( "Import was assigned ID  {$record->meta['import_id']} from the service." );

		$start_time = microtime( true );
		$response   = null;

		if ( $record->is_polling() ) {

			WP_CLI::log( "Data will be fetched polling the service, timeout is {$this->polling_timeout} seconds." );

			$first = true;
			do {
				if ( ! $first ) {
					sleep( $this->polling_interval );
				}

				WP_CLI::log( 'Polling service to get data...' );

				$response = $record->get_import_data();
				$first    = false;
			} while (
				( $response instanceof stdClass && 'fetching' === $response->status )
				&& microtime( true ) - $start_time <= $this->polling_timeout
			);
		} else {
			WP_CLI::error( 'Batch data pushing is not supported in the CLI yet!' );
		}

		if ( null === $response ) {
			WP_CLI::error( 'Run out of time  while waiting for the data, check the source or explicitly set the `--timeout` parameter to a higher value and retry.' );
		}

		if ( $response instanceof WP_Error ) {
			WP_CLI::error( 'There was an error while fetching the data from the service: ' . $response->get_error_message() );
		}

		if ( ! property_exists( $response->data, 'events' ) ) {
			WP_CLI::error( 'Empty event data; response was ' . wp_json_encode( $response ) );
		}

		$items = $response->data->events;

		if ( empty( $items ) ) {
			WP_CLI::success( "No {$item_name}s found matching the query." );
		}

		$events_count = count( $items );

		WP_CLI::log( "Received data for {$events_count} event(s) from the service." );

		if ( empty( $response->data ) ) {
			WP_CLI::error( 'Empty data; response was ' . wp_json_encode( $response ) );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Inserting posts', $events_count, $interval = 100 );

		// here we use the filter as an action to tick the progress
		add_action( 'tribe_aggregator_before_save_event', function ( array $event ) use ( $progress ) {
			$progress->tick();

			return $event;
		} );

		/** @var Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $record->insert_posts( $items );

		$progress->finish();

		return $activity;
	}

	/**
	 * Run a schuduled import.
	 *
	 * The command will use the API and licenses set for the site if required.
	 *
	 * <import_id>
	 * : the import ID, i.e. the import post ID in the site database
	 *
	 * [--timeout=<timeout>]
	 * : How long should the command wait for the data from EA Service in seconds
	 * ---
	 * default: 30
	 * ---
	 *
	 * [--format=<format>]
	 * : The results output format
	 * ---
	 *
	 * ## Examples
	 *
	 *      wp event-aggregator run-import 2389
	 *      wp event-aggregator run-import 2389 --timeout=180
	 *
	 * @since      4.6.15
	 *
	 * @subcommand run-import
	 *
	 * @when       after_wp_load
	 */
	public function run_import( array $args, array $assoc_args = [] ) {
		$this->ensure_timeout( $assoc_args );

		$record_id = $args[0];

		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );

		$parent_record = $records->get_by_post_id( $record_id );

		if ( ! $parent_record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			WP_CLI::error( "No scheduled record with a post ID of {$record_id} was found." );
		}

		// this should not be possible, yet let's take the possibility into account
		$is_csv = 'csv' === $parent_record->meta['origin'];

		if ( $is_csv ) {
			$this->ensure_column_map( $assoc_args );
		}

		WP_CLI::log( 'Creating child import post...' );

		$record = $parent_record->create_child_record();

		if ( ! $record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			if ( $record instanceof WP_Error ) {
				WP_CLI::error( "Could not create child record for record {$record_id}: " . $record->get_error_message() );
			} else {
				WP_CLI::error( "Could not create child record for record {$record_id}." );
			}
		}

		WP_CLI::log( "Created child import post with ID {$record->id}" );

		$record->update_meta( 'interactive', true );

		$this->fetch_and_process( $assoc_args, $record, $is_csv );
	}

	/**
	 * Checks the associative arguments to make sure the column map is provided for CSV imports.
	 *
	 * @since 4.6.15
	 *
	 * @param array $assoc_args
	 */
	protected function ensure_column_map( array $assoc_args = [] ) {
		if ( ! isset( $assoc_args['column_map'] ) ) {
			WP_CLI::error( 'the --column_map argument is required when importing CSV files.' );
		}
		$split = preg_split( '/\\s*,\\s*/', $assoc_args['column_map'] );

		if ( empty( $split ) ) {
			WP_CLI::error( 'the --column_map argument should contain a comma-separated list of column names for CSV imports.' );
		}
	}
}
