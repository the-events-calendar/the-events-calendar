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
	 * : Filter events by this location, not supported by all origin types; not supported by all origin types.
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
	 * The column mapping must match exactly, in order and naming, the one used by the UI to import this content type.
	 * ---
	 * default: events
	 * options:
	 *   - events
	 *   - venues
	 *   - organizers
	 * ---
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
	 *      wp event-aggregator import-from facebook https://www.facebook.com/ModernTribeInc/
	 *      wp event-aggregator import-from meetup https://www.meetup.com/wordpress-ile-de-france/
	 *      wp event-aggregator import-from gcal https://calendar.google.com/calendar/ical/me/public/basic.ics
	 *      wp event-aggregator import-from csv /Users/moi/events.csv
	 *      wp event-aggregator import-from ics /Users/moi/events.ics
	 *
	 *
	 * @since      TBD
	 *
	 * @subcommand import-from
	 *
	 * @when       after_wp_load
	 */
	public function import_from_source( array $args, array $assoc_args = array() ) {
		if ( isset( $assoc_args['timeout'] ) && ! is_numeric( $assoc_args['timeout'] ) ) {
			WP_CLI::error( 'The timeout should be a numeric value.' );
		}

		list( $origin, $source ) = $args;

		$types = array(
			'ical' => 'Tribe__Events__Aggregator__Record__iCal',
			'gcal' => 'Tribe__Events__Aggregator__Record__gCal',
			'csv' => 'Tribe__Events__Aggregator__Record__CSV',
			'ics' => 'Tribe__Events__Aggregator__Record__ICS',
			'facebook' => 'Tribe__Events__Aggregator__Record__Facebook',
			'meetup' => 'Tribe__Events__Aggregator__Record__Meetup',
			'url' => 'Tribe__Events__Aggregator__Record__Url',
		);

		$record_class = Tribe__Utils__Array::get( $types, $origin, reset( $types ) );

		/** @var Tribe__Events__Aggregator__Record__Abstract $record */
		$record = new $record_class;

		$record_args = array();

		$location   = Tribe__Utils__Array::get( $assoc_args, 'location' );
		$limit_type = Tribe__Utils__Array::get( $assoc_args, 'limit_type' );

		$category = Tribe__Utils__Array::get( $assoc_args, 'category', '' );
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

		$record_meta = array(
			'origin' => $origin,
			'type' => 'manual',
			'keywords' => Tribe__Utils__Array::get( $assoc_args, 'keywords', '' ),
			'location' => $location,
			'start' => Tribe__Utils__Array::get( $assoc_args, 'start' ),
			'end' => Tribe__Utils__Array::get( $assoc_args, 'end' ),
			'radius' => $location ? Tribe__Utils__Array::get( $assoc_args, 'radius' ) : null,
			'limit_type' => $limit_type,
			'limit' => $limit_type ? Tribe__Utils__Array::get( $assoc_args, 'limit' ) : null,
			'source' => $source,
			'preview' => false,
			'category' => $category_id
		);

		$is_csv = 'csv' === $origin;

		if ( $is_csv ) {
			$record_meta['file']         = $source;
			$record_meta['content_type'] = Tribe__Utils__Array::get( $assoc_args, 'content_type', 'events' );
		}

		if ( 'ics' === $origin ) {
			$record_meta['file'] = array(
				'name' => $source,
				'tmp_name' => $source,
			);
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

		$queue_result = $record->queue_import();

		$record->finalize();

		$this->polling_timeout = (float) $assoc_args['timeout'];

		if ( $is_csv ) {
			$activity = $this->import_csv_file( $queue_result, $record );
		} else {
			$activity = $this->import_from_service( $queue_result, $record );
		}

		$assoc_args['format'] = ! empty( $assoc_args['format'] ) ? $assoc_args['format'] : 'yaml';

		WP_CLI::print_value( $activity->get(), $assoc_args );

		WP_CLI::success( 'Import done!' );
	}

	/**
	 * @param $record
	 * @param $record_meta
	 * @param $category
	 * @param $queue_result
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	protected function import_csv_file( $queue_result, $record ): Tribe__Events__Aggregator__Record__Activity {
		WP_CLI::log( "Reading the file..." );

		$data = array(
			'action' => 'new',
			'import_id' => $record->id,
			'origin' => 'csv',
			'csv' =>
				array(
					'content_type' => 'tribe_' . $record->meta['content_type'],
					'file' => $record->meta['file'],
				),
			'column_map' =>
				array(
					0 => 'event_name',
					1 => 'event_description',
					2 => 'event_start_date',
					3 => 'event_start_time',
					4 => 'event_end_date',
					5 => 'event_end_time',
					6 => '',
					7 => 'event_venue_name',
					8 => 'event_organizer_name',
					9 => 'event_show_map_link',
					10 => 'event_show_map',
					11 => 'event_cost',
					12 => 'event_category',
					13 => 'event_website',
				),
			'post_status' => $record->meta['post_status'],
			'category' => $record->meta['category'],
			'selected_rows' => 'all',
		);

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

		/** @var Tribe__Events__Aggregator__Record__Queue $result */
		$result = $record->process_posts( $data );

		if ( $result instanceof WP_Error ) {
			WP_CLI::error( $result->get_error_message() );
		}

		$activity = $result->activity();

		return $activity;
	}

	/**
	 * @param array $assoc_args
	 * @param $queue_result
	 * @param $record
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	protected function import_from_service( $queue_result, $record ): Tribe__Events__Aggregator__Record__Activity {
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

				WP_CLI::log( "Polling service to get data..." );

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

		WP_CLI::log( "Inserting posts..." );

		/** @var Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $record->insert_posts( $items );

		return $activity;
	}
}
