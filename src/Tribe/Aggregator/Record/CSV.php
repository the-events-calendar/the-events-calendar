<?php

class Tribe__Events__Aggregator__Record__CSV extends Tribe__Events__Aggregator__Record__Abstract {
	private $state = '';
	private $output = '';
	private $messages = array();
	private $errors = array();

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
			'file'   => empty( $this->meta['file'] ) ? null : $this->meta['file'],
		);

		$meta = wp_parse_args( $meta, $defaults );

		return parent::create( $type, $args, $meta );
	}

	public function queue_import( $args = array() ) {
		$data = $this->get_csv_data();
		$result = array(
			'message_code' => 'success',
			'data' => array(
				'import_id' => $this->id,
				'items' => $data,
			),
		);

		$first_row = reset( $data );
		$columns = array_keys( $first_row );

		$result['data']['columns'] = $columns;

		// store the import id
		update_post_meta( $this->id, self::$meta_key_prefix . 'import_id', $this->id );

		// if we get here, we're good! Set the status to pending
		$this->set_status_as_pending();

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
			|| ! is_numeric( $this->meta['file'] )
		) {
			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-csv-file' ) );
		}

		$content_type = str_replace( 'tribe_', '', $this->meta['content_type'] );

		$file_path = get_attached_file( absint( $this->meta['file'] ) );
		$file_reader = new Tribe__Events__Importer__File_Reader( $file_path );
		$importer = Tribe__Events__Importer__File_Importer::get_importer( $content_type, $file_reader );

		$rows = $importer->do_import_preview();
		$headers = array_shift( $rows );
		$data = array();

		foreach( $rows as $row ) {
			$item = array();
			foreach ( $headers as $key => $header ) {
				$item[ $header ] = $row[ $key ];
			}

			$data[] = $item;
		}

		return $data;
	}

	public function insert_posts( $data = array() ) {
		if (
			'csv' !== $data['origin']
			|| empty( $data['csv']['content_type'] )
		) {
			return tribe_error( 'core:aggregator:invalid-csv-parameters' );
		}

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $data['import_id'] );

		if ( empty( $data['column_map'] ) ) {
			return tribe_error( 'core:aggregator:missing-csv-column-map' );
		}

		$content_type = $this->get_content_type();
		update_option( 'tribe_events_import_column_mapping_' . $content_type, $data['column_map'] );

		try {
			$importer = $this->get_importer();
		} catch ( RuntimeException $e ) {
			return tribe_error( 'core:aggregator:missing-csv-file' );
		}

		if ( ! empty( $this->data['category'] ) ) {
			$importer->default_category = (int) $this->data['category'];
		}

		$required_fields = $importer->get_required_fields();
		$missing = array_diff( $required_fields, $data['column_map'] );

		if ( ! empty( $missing ) ) {
			$mapper = new Tribe__Events__Importer__Column_Mapper( $this->get_content_type() );

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

		$results = $this->begin_import();

		if ( is_wp_error( $results ) ) {
			$this->set_status_as_failed( $results );
		} else {
			$this->complete_import( $results );
		}

		return $results;
	}

	public function get_importer() {
		if ( ! $this->importer ) {
			$content_type = $this->get_content_type();

			$file_path = get_attached_file( absint( $this->meta['file'] ) );
			$file_reader = new Tribe__Events__Importer__File_Reader( $file_path );
			$this->importer = Tribe__Events__Importer__File_Importer::get_importer( $content_type, $file_reader );
			$this->importer->set_map( get_option( 'tribe_events_import_column_mapping_' . $content_type, array() ) );
			$this->importer->set_type( $content_type );
			$this->importer->set_limit( absint( apply_filters( 'tribe_events_csv_batch_size', 100 ) ) );
			$this->importer->set_offset( get_option( 'tribe_events_importer_has_header', 0 ) );
		}

		return $this->importer;
	}

	public function get_content_type() {
		return str_replace( 'tribe_', '', $this->meta['content_type'] );
	}

	private function begin_import() {
		$this->reset_tracking_options();
		return $this->continue_import();
	}

	protected function reset_tracking_options() {
		update_option( 'tribe_events_importer_offset', get_option( 'tribe_events_importer_has_header', 0 ) );
		update_option( 'tribe_events_import_log', array( 'updated' => 0, 'created' => 0, 'skipped' => 0, 'encoding' => 0 ) );
		update_option( 'tribe_events_import_failed_rows', array() );
		update_option( 'tribe_events_import_encoded_rows', array() );
	}

	protected function continue_import() {
		$importer = $this->get_importer();
		$importer->is_aggregator = true;
		$offset = get_option( 'tribe_events_importer_offset' );
		if ( $offset == -1 ) {
			$this->state = 'complete';
			$this->clean_up_after_import();
		} else {
			$this->state = 'importing';
			$importer->set_offset( $offset );
			$this->do_import( $importer );
			$this->log_import_results( $importer );
		}

		return get_option( 'tribe_events_import_log', array( 'updated' => 0, 'created' => 0, 'skipped' => 0, 'encoding' => 0 ) );
	}

	protected function do_import( Tribe__Events__Importer__File_Importer $importer ) {
		$importer->do_import();

		$this->messages = $importer->get_log_messages();

		$new_offset = $importer->import_complete() ? -1 : $importer->get_last_completed_row();
		update_option( 'tribe_events_importer_offset', $new_offset );

		if ( -1 === $new_offset ) do_action( 'tribe_events_csv_import_complete' );
	}

	protected function log_import_results( Tribe__Events__Importer__File_Importer $importer ) {
		$log = get_option( 'tribe_events_import_log' );
		$log['updated'] += $importer->get_updated_post_count();
		$log['created'] += $importer->get_new_post_count();
		$log['skipped'] += $importer->get_skipped_row_count();
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
