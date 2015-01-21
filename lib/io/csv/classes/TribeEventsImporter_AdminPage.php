<?php

/**
 * Class TribeEventsImporter_AdminPage
 */
class TribeEventsImporter_AdminPage {
	private $state = '';
	private $output = '';
	private $messages = array();
	private $errors = array();

	public function register_admin_page() {
		add_submenu_page(
			'edit.php?post_type='.TribeEvents::POSTTYPE,
			__('Import: CSV','tribe-events-calendar'),
			__('Import: CSV','tribe-events-calendar'),
			'import',
			'events-importer',
			array( $this, 'render_admin_page_contents' )
		);
	}

	public function render_admin_page_contents() {
		switch ( $this->state ) {
			case 'map':
				try {
					$file = new TribeEventsImporter_FileReader(TribeEventsImporter_FileUploader::get_file_path());
				} catch ( RuntimeException $e ) {
					$this->errors[] = __('The file went away. Please try again.', 'tribe-events-calendar');
					$this->state = '';
					return $this->render_admin_page_contents();
				}
				$header = $file->get_header();
				if ( get_option( 'tribe_events_importer_has_header', 0 ) == 0 ) {
					$letter = 'A';
					$size = count($header);
					$header = array();
					for ( $i = 0 ; $i < $size ; $i++ ) {
						$header[] = $letter++;
					}
				}
				$import_type = get_option( 'tribe_events_import_type' );
				$messages = $this->errors;
				include( TribeEventsImporter_Plugin::path('admin-views/columns.php') );
				break;
			case 'importing':
				$messages = $this->messages;
				include( TribeEventsImporter_Plugin::path('admin-views/in-progress.php') );
				break;
			case 'complete':
				$log = get_option( 'tribe_events_import_log' );
				$skipped = get_option( 'tribe_events_import_failed_rows', array() );
				include( TribeEventsImporter_Plugin::path('admin-views/result.php') );
				break;
			default:
				$messages = $this->errors;
				include( TribeEventsImporter_Plugin::path('admin-views/import.php') );
				break;
		}
	}

	public function handle_submission() {
		$action = $this->get_action();
		if ( empty($action) ) {
			return;
		}

		ob_start();
		switch ( $action ) {
			case 'map':
				$this->handle_file_submission();
				break;

			case 'import':
				if ( $this->handle_column_mapping() ) {
					$this->begin_import();
				} else {
					$this->state = 'map';
				}
				break;

			case 'continue':
				$this->continue_import();
				break;

			default:
				// Should never get here.
				break;
		}
		$this->output = ob_get_clean();
	}

	private function get_action() {
		$action = '';
		if ( isset( $_POST[ 'ecp_import_action' ] ) ) {
			$action = trim( $_POST[ 'ecp_import_action' ] );
		}
		if ( isset($_GET['action']) ) {
			$action = trim( $_GET[ 'action' ] );
		}
		if ( !empty($action) ) {
			if ( !in_array( $action, array('import', 'map', 'continue') ) ) {
				$action = '';
			}
		}
		return $action;
	}

	private function handle_file_submission() {
		$this->state = 'map';

		if ( empty($_POST['import_type']) || empty($_FILES['import_file']['name']) ) {
			$this->errors[] = __('We were unable to process your request. Please try again.', 'tribe-events-calendar');
			$this->state = '';
			return;
		}

		$import_type = $_POST[ 'import_type' ];
		update_option( 'tribe_events_import_type', $import_type );

		try {
			$file_handler = new TribeEventsImporter_FileUploader($_FILES['import_file']);
			$file_handler->save_file();
		} catch ( RuntimeException $e ) {
			$this->errors[] = $e->getMessage();
			$this->state = '';
			return;
		}

		if( isset($_POST[ 'import_header' ]) && $_POST[ 'import_header' ] ){
			update_option( 'tribe_events_importer_has_header', 1 );
		} else {
			update_option( 'tribe_events_importer_has_header', 0 );
		}
	}

	private function handle_column_mapping() {
		// Deconstruct mapping.
		if ( empty($_POST['column_map']) ) {
			return FALSE;
		}
		$column_mapping = $_POST['column_map'];

		try {
			$importer = $this->get_importer();
		} catch ( RuntimeException $e ) {
			$this->errors[] = __('The file went away. Please try again.', 'tribe-events-calendar');
			return FALSE;
		}
		$required_fields = $importer->get_required_fields();
		$missing = array_diff($required_fields, $column_mapping);
		if ( !empty($missing) ) {
			$mapper = new TribeEventsImporter_ColumnMapper(get_option( 'tribe_events_import_type' ));
			$message = __('<p>The following fields are required for a successful import:</p>', 'tribe-events-calendar');
			$message .= '<ul style="list-style-type: disc; margin-left: 1.5em;">';
			foreach ( $missing as $key ) {
				$message .= '<li>'.$mapper->get_column_label($key).'</li>';
			}
			$message .= '</ul>';
			$this->errors[] = $message;
			return FALSE;
		}

		update_option('tribe_events_import_column_mapping', $column_mapping);
		return TRUE;
	}

	private function begin_import() {
		$this->reset_tracking_options();
		$this->continue_import();
	}

	private function reset_tracking_options() {
		update_option( 'tribe_events_importer_offset', get_option( 'tribe_events_importer_has_header', 0 ) );
		update_option( 'tribe_events_import_log', array( 'updated' => 0, 'created' => 0, 'skipped' => 0 ) );
		update_option( 'tribe_events_import_failed_rows', array() );
	}

	private function continue_import() {
		$importer = $this->get_importer();
		$offset = get_option('tribe_events_importer_offset');
		if ( $offset == -1 ) {
			$this->state = 'complete';
			$this->clean_up_after_import();
		} else {
			$this->state = 'importing';
			$importer->set_offset($offset);
			$this->do_import($importer);
			$this->log_import_results($importer);
		}
	}

	private function do_import( TribeEventsImporter_FileImporter $importer ) {
		$importer->do_import();

		$this->messages = $importer->get_log_messages();

		$new_offset = $importer->import_complete() ? -1 : $importer->get_last_completed_row();
		update_option('tribe_events_importer_offset', $new_offset);

        if ( -1 === $new_offset ) do_action( 'tribe_events_csv_import_complete' );
	}

	private function get_importer() {
		$type = get_option('tribe_events_import_type');
		$file_reader = new TribeEventsImporter_FileReader(TribeEventsImporter_FileUploader::get_file_path());
		$importer = TribeEventsImporter_FileImporter::get_importer($type, $file_reader);
		$importer->set_map(get_option('tribe_events_import_column_mapping', array()));
		$importer->set_type(get_option('tribe_events_import_type'));
		$importer->set_limit( absint( apply_filters( 'tribe_events_csv_batch_size', 100 ) ) );
		$importer->set_offset(get_option('tribe_events_importer_has_header', 0));
		return $importer;
	}

	private function log_import_results( TribeEventsImporter_FileImporter $importer ) {
		$log = get_option( 'tribe_events_import_log' );
		$log['updated'] += $importer->get_updated_post_count();
		$log['created'] += $importer->get_new_post_count();
		$log['skipped'] += $importer->get_skipped_row_count();
		update_option( 'tribe_events_import_log', $log );

		$skipped_rows = $importer->get_skipped_row_numbers();
		$previously_skipped_rows = get_option( 'tribe_events_import_failed_rows', array() );
		$skipped_rows = $previously_skipped_rows + $skipped_rows;
		update_option( 'tribe_events_import_failed_rows', $skipped_rows );
	}

	private function clean_up_after_import() {
		TribeEventsImporter_FileUploader::clear_old_files();
	}
}
