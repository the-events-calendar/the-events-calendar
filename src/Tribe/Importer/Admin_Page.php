<?php

/**
 * Class Tribe__Events__Importer__Admin_Page
 */
class Tribe__Events__Importer__Admin_Page {
	private $state = '';
	private $output = '';
	private $messages = array();
	private $errors = array();

	/**
	 * Static Singleton Holder
	 * @var Tribe__Settings|null
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Settings
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Admin page for the importer URL, relative to `admin_url()`
	 * @var null|string
	 */
	public $admin_page_url = null;

	/**
	 * The actual Page Slug used
	 * @var null|string
	 */
	public $admin_page_slug = null;

	public function __construct() {
		$this->admin_page_url = 'edit.php?post_type=' . Tribe__Events__Main::POSTTYPE;
		$this->admin_page_slug = 'events-importer';
	}

	/**
	 * Returns the main admin settings URL.
	 *
	 * @return string
	 */
	public function get_url( array $args = array() ) {
		$defaults = array(
			'page' => $this->admin_page_slug,
		);

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );
		$url = admin_url( $this->admin_page_url );

		return esc_url( apply_filters( 'tribe_importer_url', add_query_arg( $args, $url ), $args, $url ) );
	}

	public function register_admin_page() {
		add_submenu_page(
			$this->admin_page_url,
			esc_html__( 'Import', 'the-events-calendar' ),
			esc_html__( 'Import', 'the-events-calendar' ),
			'import',
			$this->admin_page_slug,
			array( $this, 'render_admin_page_contents' )
		);
	}

	public function add_settings_fields( $fields = array() ) {
		$newfields = array(
			'csv-title'                     => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'CSV Import Settings', 'the-events-calendar' ) . '</h3>',
			),
			'csv-form-content-start'        => array(
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">',
			),
			'imported_post_status[csv]'     => array(
				'type'            => 'dropdown',
				'label'           => __( 'Default status to use for imported events', 'the-events-calendar' ),
				'options'         => Tribe__Events__Importer__Options::get_possible_stati(),
				'validation_type' => 'options',
				'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			),
			'imported_encoding_status[csv]' => array(
				'type'            => 'dropdown',
				'label'           => __( 'Default encoding for imported csv file', 'the-events-calendar' ),
				'options'         => Tribe__Events__Importer__Options::get_encoding_status(),
				'validation_type' => 'options',
				'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			),
			'csv-form-content-end'          => array(
				'type' => 'html',
				'html' => '</div>',
			),
		);
		return array_merge( $fields, $newfields );
	}

	public function render_admin_page_contents() {
		$tab = $this->get_active_tab();

		switch ( $tab ) {
			case 'general' :
				$this->render_general_tab();
				break;

			case 'csv-importer':
				$this->render_csv_tab();
				break;

			default:
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/header.php' );
				if ( has_action( 'tribe-import-render-tab-' . $tab ) ) {
					/**
					 * Remove this Action on 4.3
					 * @deprecated
					 */
					_doing_it_wrong(
						'tribe-import-render-tab-' . $tab,
						sprintf(
							esc_html__( 'This Action has been deprecated, to comply with WordPress Standards we are now using Underscores (_) instead of Dashes (-). From: "%s" To: "%s"', 'the-events-calendar' ),
							'tribe-import-render-tab-' . $tab,
							'tribe_import_render_tab_' . $tab
						),
						'4.0'
					);
					do_action( 'tribe-import-render-tab-' . $tab );
				}

				do_action( 'tribe_import_render_tab_' . $tab );
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/footer.php' );
				break;
		}

	}

	public function render_general_tab() {
		include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/general.php' );
	}

	public function render_csv_tab() {
		switch ( $this->state ) {
			case 'map':
				try {
					$file = new Tribe__Events__Importer__File_Reader( Tribe__Events__Importer__File_Uploader::get_file_path() );
				} catch ( RuntimeException $e ) {
					$this->errors[] = esc_html__( 'The file went away. Please try again.', 'the-events-calendar' );
					$this->state = '';
					return $this->render_admin_page_contents();
				}
				$header = $file->get_header();
				if ( get_option( 'tribe_events_importer_has_header', 0 ) == 0 ) {
					$letter = 'A';
					$size = count( $header );
					$header = array();
					for ( $i = 0 ; $i < $size ; $i++ ) {
						$header[] = $letter++;
					}
				}
				$import_type = get_option( 'tribe_events_import_type' );

				$import_type_titles_map = array();

				/**
				 * Allows filtering the import type titles to go from a slug to a pretty title.
				 *
				 * @param array $import_type_titles_map
				 */
				$import_type_titles_map = apply_filters( 'tribe_events_import_type_titles_map', $import_type_titles_map );
				$import_type_title      = isset( $import_type_titles_map[ $import_type ] ) ? $import_type_titles_map[ $import_type ] : ucwords( $import_type );
				$messages               = $this->errors;
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/columns.php' );
				break;
			case 'importing':
				$messages = $this->messages;
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/in-progress.php' );
				break;
			case 'complete':
				$log = get_option( 'tribe_events_import_log' );
				$skipped = get_option( 'tribe_events_import_failed_rows', array() );
				$encoded = get_option( 'tribe_events_import_encoded_rows', array() );
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/result.php' );
				break;
			default:
				$messages = $this->errors;
				$import_options = array(
					'venues'     => esc_html__( 'Venues', 'the-events-calendar' ),
					'organizers' => esc_html__( 'Organizers', 'the-events-calendar' ),
					'events'     => esc_html__( 'Events', 'the-events-calendar' ),
				);

				/**
				 * Filters the CSV import options available to the user.
				 *
				 * @param array $import_options An associative array of option values and labels.
				 */
				$import_options = apply_filters( 'tribe_events_import_options_rows', $import_options );

				$default_selected_import_option = 'events';

				/**
				 * Filters the default selected option for the import options.
				 *
				 * @param string $default_selected_import_option
				 */
				$default_selected_import_option = apply_filters( 'tribe_events_import_options_default_selected', $default_selected_import_option );
				include Tribe__Events__Importer__Plugin::path( 'src/io/csv/admin-views/import.php' );
				break;
		}
	}

	public function get_active_tab() {
		$tabs = (array) $this->get_available_tabs();
		$default = array_shift( $tabs );
		return empty( $_REQUEST[ 'tab' ] ) ? $default : $_REQUEST[ 'tab' ];
	}

	public function get_available_tabs() {
		$tabs = array(
			esc_html__( 'Import Settings', 'the-events-calendar' ) => 'general',
			esc_html__( 'CSV', 'the-events-calendar' ) => 'csv-importer',
		);

		if ( has_filter( 'tribe-import-tabs' ) ) {
			/**
			 * Remove this Filter on 4.3
			 * @deprecated
			 */
			_doing_it_wrong(
				'tribe-import-tabs',
				sprintf(
					esc_html__( 'This Filter has been deprecated, to comply with WordPress Standards we are now using Underscores (_) instead of Dashes (-). From: "%s" To: "%s"', 'the-events-calendar' ),
					'tribe-import-tabs',
					'tribe_import_tabs'
				),
				'4.0'
			);
			$tabs = apply_filters( 'tribe-import-tabs', $tabs );
		}

		return apply_filters( 'tribe_import_tabs', $tabs );
	}

	public function handle_submission() {
		$action = $this->get_action();
		if ( empty( $action ) ) {
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
				// This test guards against a fatal error that can occur if the action=continue
				// screen is refreshed after the import completes
				if ( Tribe__Events__Importer__File_Uploader::has_valid_csv_file() ) {
					$this->continue_import();
				}
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
		if ( isset( $_GET['action'] ) ) {
			$action = trim( $_GET[ 'action' ] );
		}
		if ( ! empty( $action ) ) {
			if ( ! in_array( $action, array( 'import', 'map', 'continue' ) ) ) {
				$action = '';
			}
		}
		return $action;
	}

	private function handle_file_submission() {
		$this->state = 'map';

		if ( empty( $_POST['import_type'] ) || empty( $_FILES['import_file']['name'] ) ) {
			$this->errors[] = esc_html__( 'We were unable to process your request. Please try again.', 'the-events-calendar' );
			$this->state = '';
			return;
		}

		$import_type = $_POST[ 'import_type' ];
		update_option( 'tribe_events_import_type', $import_type );

		try {
			$file_handler = new Tribe__Events__Importer__File_Uploader( $_FILES['import_file'] );
			$file_handler->save_file();
		} catch ( RuntimeException $e ) {
			$this->errors[] = $e->getMessage();
			$this->state = '';
			return;
		}

		if ( isset( $_POST[ 'import_header' ] ) && $_POST[ 'import_header' ] ) {
			update_option( 'tribe_events_importer_has_header', 1 );
		} else {
			update_option( 'tribe_events_importer_has_header', 0 );
		}
	}

	private function handle_column_mapping() {
		// Deconstruct mapping.
		if ( empty( $_POST['column_map'] ) ) {
			return false;
		}
		$column_mapping = $_POST['column_map'];

		try {
			$importer = $this->get_importer();
		} catch ( RuntimeException $e ) {
			$this->errors[] = esc_html__( 'The file went away. Please try again.', 'the-events-calendar' );
			return false;
		}
		$required_fields = $importer->get_required_fields();
		$missing = array_diff( $required_fields, $column_mapping );
		if ( ! empty( $missing ) ) {
			$mapper = new Tribe__Events__Importer__Column_Mapper( get_option( 'tribe_events_import_type' ) );
			$message = '<p>' . esc_html__( 'The following fields are required for a successful import:', 'the-events-calendar' ) . '</p>';
			$message .= '<ul style="list-style-type: disc; margin-left: 1.5em;">';
			foreach ( $missing as $key ) {
				$message .= '<li>' . $mapper->get_column_label( $key ) . '</li>';
			}
			$message .= '</ul>';
			$this->errors[] = $message;
			return false;
		}

		update_option( 'tribe_events_import_column_mapping_' . $importer->get_type(), $column_mapping );
		return true;
	}

	private function begin_import() {
		$this->reset_tracking_options();
		$this->continue_import();
	}

	private function reset_tracking_options() {
		update_option( 'tribe_events_importer_offset', get_option( 'tribe_events_importer_has_header', 0 ) );
		update_option( 'tribe_events_import_log', array( 'updated' => 0, 'created' => 0, 'skipped' => 0, 'encoding' => 0 ) );
		update_option( 'tribe_events_import_failed_rows', array() );
		update_option( 'tribe_events_import_encoded_rows', array() );
	}

	private function continue_import() {
		$importer = $this->get_importer();
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
	}

	private function do_import( Tribe__Events__Importer__File_Importer $importer ) {
		$importer->do_import();

		$this->messages = $importer->get_log_messages();

		$new_offset = $importer->import_complete() ? -1 : $importer->get_last_completed_row();
		update_option( 'tribe_events_importer_offset', $new_offset );

        if ( -1 === $new_offset ) do_action( 'tribe_events_csv_import_complete' );
	}

	private function get_importer() {
		$type = get_option( 'tribe_events_import_type' );
		$file_reader = new Tribe__Events__Importer__File_Reader( Tribe__Events__Importer__File_Uploader::get_file_path() );
		$importer = Tribe__Events__Importer__File_Importer::get_importer( $type, $file_reader );
		$importer->set_map( get_option( 'tribe_events_import_column_mapping_' . $type, array() ) );
		$importer->set_type( get_option( 'tribe_events_import_type' ) );
		$importer->set_limit( absint( apply_filters( 'tribe_events_csv_batch_size', 100 ) ) );
		$importer->set_offset( get_option( 'tribe_events_importer_has_header', 0 ) );
		return $importer;
	}

	private function log_import_results( Tribe__Events__Importer__File_Importer $importer ) {
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
