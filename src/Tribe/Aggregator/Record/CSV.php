<?php

class Tribe__Events__Aggregator__Record__CSV extends Tribe__Events__Aggregator__Record__Abstract {
	private $state    = '';
	private $output   = '';
	private $messages = array();
	private $errors   = array();

	public $origin = 'csv';

	protected $importer;

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - import or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		$defaults = array(
			'file' => empty( $this->meta['file'] ) ? null : $this->meta['file'],
		);

		$meta = wp_parse_args( $meta, $defaults );

		return parent::create( $type, $args, $meta );
	}

	public function queue_import( $args = array() ) {
		$is_previewing = (
			! empty( $_GET['action'] )
			&& (
				'tribe_aggregator_create_import' === $_GET['action']
				|| 'tribe_aggregator_preview_import' === $_GET['action']
			)
		);

		$data = $this->get_csv_data();

		$result = array(
			'status'       => 'success',
			'message_code' => 'success',
			'data'         => array(
				'import_id' => $this->id,
				'items'     => $data,
			),
		);

		$first_row = reset( $data );
		$columns   = array_keys( $first_row );

		$result['data']['columns'] = $columns;

		// store the import id
		update_post_meta( $this->id, self::$meta_key_prefix . 'import_id', $this->id );

		// only set as pending if we aren't previewing the record
		if ( ! $is_previewing ) {
			// if we get here, we're good! Set the status to pending
			$this->set_status_as_pending();
		}

		return $result;
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'CSV', 'the-events-calendar' );
	}

	public function get_csv_data() {
		if (
			empty( $this->meta['file'] )
			|| ! $file_path = $this->get_file_path()
		) {
			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-csv-file' ) );
		}

		$content_type = str_replace( 'tribe_', '', $this->meta['content_type'] );

		$file_reader = new Tribe__Events__Importer__File_Reader( $file_path );
		$importer    = Tribe__Events__Importer__File_Importer::get_importer( $content_type, $file_reader );

		$this->update_meta( 'source_name', basename( $file_path ) );

		$rows    = $importer->do_import_preview();

		$headers = array_shift( $rows );

		/*
		 * To avoid empty columns from collapsing onto each other we provide
		 * each column without an header a generated one.
		 */
		$empty_counter = 1;
		foreach ( $headers as $key => &$header ) {
			if ( empty( $header ) ) {
				$header = __( 'Unknown Column ', 'the-events-calendar' ) . $empty_counter ++;
			}
		}

		$data    = array();

		foreach ( $rows as $row ) {
			$item = array();

			foreach ( $headers as $key => $header ) {
				$item[ $header ] = $row[ $key ];
			}

			$data[] = $item;
		}

		return $data;
	}

	/**
	 * Queues events, venues, and organizers for insertion
	 *
	 * @param array $data   Import data
	 * @param bool $ignored This parameter is, de facto, ignored when processing CSV files: all
	 *                      imports are immediately started.
	 *
	 * @return array|WP_Error
	 */
	public function process_posts( $data = array(), $ignored = false ) {
		if (
			'csv' !== $data['origin']
			|| empty( $data['csv']['content_type'] )
		) {
			return tribe_error( 'core:aggregator:invalid-csv-parameters' );
		}

		if ( $this->has_queue() ) {
			$queue = Tribe__Events__Aggregator__Record__Queue_Processor::build_queue( $this->post->ID );
			return $queue->process();
		}

		$importer = $this->prep_import_data( $data );

		if ( tribe_is_error( $importer ) ) {
			return $importer;
		}

		$queue = Tribe__Events__Aggregator__Record__Queue_Processor::build_queue( $this->post->ID, $importer );

		return $queue->process();
	}

	/**
	 * Handles import data before queuing
	 *
	 * Ensures the import record source name is accurate, checks for errors, and limits import items
	 * based on selection
	 *
	 * @param array $data Import data
	 *
	 * @return array|WP_Error
	 */
	public function prep_import_data( $data = array() ) {
		if ( empty( $this->meta['finalized'] ) ) {
			return tribe_error( 'core:aggregator:record-not-finalized' );
		}

		// if $data is an object already, don't attempt to manipulate it into an importer object
		if ( is_object( $data ) ) {
			return $data;
		}

		// if $data is empty, grab the data from meta
		if ( empty( $data ) ) {
			$data = $this->meta;
		}

		if ( empty( $data['column_map'] ) ) {
			return tribe_error( 'core:aggregator:missing-csv-column-map' );
		}

		$content_type = $this->get_csv_content_type();
		update_option( 'tribe_events_import_column_mapping_' . $content_type, $data['column_map'] );

		try {
			$importer = $this->get_importer();
		} catch ( RuntimeException $e ) {
			return tribe_error( 'core:aggregator:missing-csv-file' );
		}

		if ( ! empty( $data['category'] ) ) {
			$importer = $this->maybe_set_default_category( $importer );
		}

		if ( ! empty( $data['post_status'] ) ) {
			$importer = $this->maybe_set_default_post_status( $importer );
		}

		$required_fields = $importer->get_required_fields();
		$missing         = array_diff( $required_fields, $data['column_map'] );

		if ( ! empty( $missing ) ) {
			$mapper = new Tribe__Events__Importer__Column_Mapper( $content_type );

			/**
			 * @todo  allow to overwrite the default message
			 */
			$message = '<p>' . esc_html__( 'The following fields are required for a successful import:', 'the-events-calendar' ) . '</p>';
			$message .= '<ul style="list-style-type: disc; margin-left: 1.5em;">';
			foreach ( $missing as $key ) {
				$message .= '<li>' . $mapper->get_column_label( $key ) . '</li>';
			}
			$message .= '</ul>';
			return new WP_Error(
				'csv-invalid-column-mapping',
				$message
			);
		}

		update_option( 'tribe_events_import_column_mapping_' . $content_type, $data['column_map'] );

		return $importer;
	}

	public function get_importer() {
		if ( ! $this->importer ) {
			$content_type = $this->get_csv_content_type();

			$file_path      = $this->get_file_path();
			$file_reader    = new Tribe__Events__Importer__File_Reader( $file_path );
			$this->importer = Tribe__Events__Importer__File_Importer::get_importer( $content_type, $file_reader );

			$this->importer->set_map( get_option( 'tribe_events_import_column_mapping_' . $content_type, array() ) );
			$this->importer->set_type( $content_type );
			$this->importer->set_limit( absint( apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size ) ) );
			$this->importer->set_offset( 1 );
		}

		return $this->importer;
	}

	public function get_content_type() {
		return str_replace( 'tribe_', '', $this->meta['content_type'] );
	}

	/**
	 * Translates the posttype-driven content types to content types that the CSV importer knows
	 *
	 * @param string $content_type Content Type
	 *
	 * @return string CSV Importer compatible content type
	 */
	public function get_csv_content_type( $content_type = null ) {

		if ( ! $content_type ) {
			$content_type = $this->get_content_type();
		}

		$lowercase_content_type = strtolower( $content_type );

		$map = array(
			'event'      => 'events',
			'events'     => 'events',
			'organizer'  => 'organizers',
			'organizers' => 'organizers',
			'venue'      => 'venues',
			'venues'     => 'venues',
		);

		if ( isset( $map[ $lowercase_content_type ] ) ) {
			return $map[ $lowercase_content_type ];
		}

		return $content_type;
	}

	/**
	 * Gets the available post types for importing
	 *
	 * @return array Array of Post Type Objects
	 */
	public function get_import_post_types() {
		$post_types = array(
			get_post_type_object( Tribe__Events__Main::POSTTYPE ),
			get_post_type_object( Tribe__Events__Organizer::POSTTYPE ),
			get_post_type_object( Tribe__Events__Venue::POSTTYPE ),
		);

		/**
		 * Filters the available CSV post types for the event aggregator form
		 *
		 * @param array $post_types Array of post type objects
		 */
		return apply_filters( 'tribe_aggregator_csv_post_types', $post_types );
	}

	/**
	 * Returns the path to the CSV file.
	 *
	 * @since 4.6.15
	 *
	 * @return bool|false|string Either the absolute path to the CSV file or `false` on failure.
	 */
	protected function get_file_path() {
		if ( is_numeric( $this->meta['file'] ) ) {
			$file_path = get_attached_file( absint( $this->meta['file'] ) );
		} else {
			$file_path = realpath( $this->meta['file'] );
		}

		return $file_path && file_exists( $file_path ) ? $file_path : false;
	}

	private function begin_import() {
		$this->reset_tracking_options();
		return $this->continue_import();
	}

	public function reset_tracking_options() {
		update_option( 'tribe_events_importer_offset', 1 );
		update_option( 'tribe_events_import_log', array( 'updated' => 0, 'created' => 0, 'skipped' => 0, 'encoding' => 0 ) );
		update_option( 'tribe_events_import_failed_rows', array() );
		update_option( 'tribe_events_import_encoded_rows', array() );
	}

	public function continue_import() {
		$importer                    = $this->get_importer();
		$importer->is_aggregator     = true;
		$importer->aggregator_record = $this;
		$importer                    = $this->maybe_set_default_category( $importer );
		$importer                    = $this->maybe_set_default_post_status( $importer );
		$offset                      = (int) get_option( 'tribe_events_importer_offset', 1 );

		if ( -1 === $offset ) {
			$this->state = 'complete';
			$this->clean_up_after_import();
		} else {
			$this->state = 'importing';
			$importer->set_offset( $offset );
			$this->do_import( $importer );
			$this->log_import_results( $importer );
		}

		return $this->meta['activity'];
	}

	/**
	 * If a custom category has been specified, set it in the importer
	 *
	 * @param Tribe__Events__Importer__File_Importer $importer Importer object
	 *
	 * @return Tribe__Events__Importer__File_Importer
	 */
	public function maybe_set_default_category( $importer ) {
		if ( ! empty( $this->meta['category'] ) ) {
			$importer->default_category = (int) $this->meta['category'];
		}

		return $importer;
	}

	/**
	 * If a custom post_status has been specified, set it in the importer
	 *
	 * @param Tribe__Events__Importer__File_Importer $importer Importer object
	 *
	 * @return Tribe__Events__Importer__File_Importer
	 */
	public function maybe_set_default_post_status( $importer ) {
		if ( ! empty( $this->meta['post_status'] ) ) {
			$importer->default_post_status = $this->meta['post_status'];
		}

		return $importer;
	}

	protected function do_import( Tribe__Events__Importer__File_Importer $importer ) {
		$importer->do_import();

		$this->messages = $importer->get_log_messages();

		$new_offset = $importer->import_complete() ? -1 : $importer->get_last_completed_row();
		update_option( 'tribe_events_importer_offset', $new_offset );

		if ( -1 === $new_offset ) {
			do_action( 'tribe_events_csv_import_complete' );
		}
	}

	protected function log_import_results( Tribe__Events__Importer__File_Importer $importer ) {
		$log = get_option( 'tribe_events_import_log' );
		if ( empty( $log['encoding'] ) ) {
			$log['encoding'] = 0;
		}

		$updated = $importer->get_updated_post_count();
		$created = $importer->get_new_post_count();
		$skipped = $importer->get_skipped_row_count();

		if ( $updated ) {
			$this->meta['activity']->add( 'updated', $this->meta['content_type'], array_fill( 0, $updated, 1 ) );
		}

		if ( $created ) {
			$this->meta['activity']->add( 'created', $this->meta['content_type'], array_fill( 0, $created, 1 ) );
		}

		if ( $skipped ) {
			$this->meta['activity']->add( 'skipped', $this->meta['content_type'], array_fill( 0, $skipped, 1 ) );
		}

		$log['updated']  += $updated;
		$log['created']  += $created;
		$log['skipped']  += $skipped;
		$log['encoding'] += $importer->get_encoding_changes_row_count();
		update_option( 'tribe_events_import_log', $log );

		$skipped_rows            = $importer->get_skipped_row_numbers();
		$previously_skipped_rows = get_option( 'tribe_events_import_failed_rows', array() );
		$skipped_rows            = $previously_skipped_rows + $skipped_rows;
		update_option( 'tribe_events_import_failed_rows', $skipped_rows );

		$encoded_rows            = $importer->get_encoding_changes_row_numbers();
		$previously_encoded_rows = get_option( 'tribe_events_import_encoded_rows', array() );
		$encoded_rows            = $previously_encoded_rows + $encoded_rows;
		update_option( 'tribe_events_import_encoded_rows', $encoded_rows );
	}

	private function clean_up_after_import() {
		Tribe__Events__Importer__File_Uploader::clear_old_files();
	}
}
