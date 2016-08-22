<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs__Scheduled extends Tribe__Events__Aggregator__Tabs__Abstract {
	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * To Order the Tabs on the UI you need to change the priority
	 * @var integer
	 */
	public $priority = 20;

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

	public function __construct() {
		// Setup Abstract hooks
		parent::__construct();

		// Handle Requests to the Tab
		add_action( 'tribe_aggregator_page_request', array( $this, 'handle_request' ) );
	}

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'scheduled';
	}

	public function get_label() {
		return esc_html__( 'Scheduled Imports', 'the-events-calendar' );
	}

	public function handle_request() {
		// If we are on AJAX or not on this Tab, We shall not pass
		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! $this->is_active() ) {
			return;
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->handle_post();
		} elseif ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->handle_get();
		}
	}

	private function handle_post( $data = null ) {
		if ( is_null( $data ) ) {
			if ( ! isset( $_POST['aggregator'] ) ) {
				return false;
			}

			$data = $_POST['aggregator'];
		}

		// Ensure it's an Object
		$data = (object) $data;

		if ( ! isset( $data->action ) ) {
			return false;
		}

		if ( ! isset( $data->nonce ) || ! wp_verify_nonce( $data->nonce, 'aggregator_' . $this->get_slug() . '_request' ) ) {
			return false;
		}

		if ( empty( $data->records ) ) {
			if ( empty( $data->ids ) ) {
				return false;
			}

			$data->records = explode( ',', $data->ids );
		}

		// Ensures Records is an Array
		$data->records = (array) $data->records;

		if ( 'delete' === $data->action ) {
			list( $success, $errors ) = $this->action_delete_record( $data->records );
		} elseif ( 'run-import' === $data->action ) {
			list( $success, $errors ) = $this->action_run_import( $data->records );
		}

		$args = array(
			'tab'    => $this->get_slug(),
			'action' => $data->action,
			'ids'     => implode( ',', array_keys( $success ) ),
		);

		if ( ! empty( $errors ) ) {
			$args['error'] = $data->nonce;

			// Set the errors
			set_transient( $this->get_errors_transient_name( $data->nonce ), $errors, 5 * MINUTE_IN_SECONDS );
		}

		$sendback = Tribe__Events__Aggregator__Page::instance()->get_url( $args );

		wp_redirect( $sendback );
		die;
	}

	public function get_errors_transient_name( $nonce ) {
		return 'tribe-ea-' . $this->get_slug() . '-action-' . $nonce;
	}

	private function handle_get() {
		if ( ! isset( $_GET['action'] ) ) {
			return false;
		}

		switch ( $_GET['action'] ) {
			case 'run-import';
				$action = __( 'queued', 'the-events-calendar' );
			break;
			case 'delete';
				$action = __( 'delete', 'the-events-calendar' );
			break;
			default:
				return false;
		}

		if ( empty( $_GET['ids'] ) ) {
			return false;
		}

		// If it has a Nonce we do a GET2POST request
		if ( isset( $_GET['nonce'] ) ) {
			return $this->handle_post( $_GET );
		}

		$this->action_notice( $action, $_GET['ids'], isset( $_GET['error'] ) ? $_GET['error'] : null );
	}

	/**
	 * Error and success messages for delete
	 *
 	 * @param  string  $action  saved, deleted
	 * @param  array   $statuses  Which status occurred
	 * @return string
	 */
	private function action_notice( $action, $ids = array(), $error = null ) {
		$ids    = explode( ',', $ids );
		$errors = array();

		if ( is_string( $error ) ) {
			$transient = $this->get_errors_transient_name( $error );
			$errors = get_transient( $transient );

			// After getting delete
			delete_transient( $transient );
		}

		$success = count( $ids );
		$message = (object) array(
			'success' => array(),
			'error' => array(),
		);

		if ( ! empty( $errors ) ) {
			$message->error[] = sprintf( esc_html__( 'Error: %d scheduled import was not %s.', 'the-events-calendar' ), $action, count( $errors ) );
			foreach ( $errors as $post_id => $error ) {
				$message->error[] = implode( '<br/>', sprintf( '%d: %s', $post_id, $error->get_error_message() ) );
			}
			tribe_notice( 'tribe-aggregator-action-records-error', '<p>' . implode( '<br/>', $message->error ) . '</p>', 'type=error' );
		}

		if ( 0 < $success ) {
			$message->success[] = sprintf( esc_html__( 'Successfully %s %d scheduled import', 'the-events-calendar' ), $action, $success );
			tribe_notice( 'tribe-aggregator-action-records-success', '<p>' . implode( "\r\n", $message->success ) . '</p>', 'type=success' );
		}
	}

	private function action_delete_record( $records = array() ) {
		$record_obj = Tribe__Events__Aggregator__Records::instance()->get_post_type();
		$records = array_filter( (array) $records, 'is_numeric' );
		$success = array();
		$errors = array();

		foreach ( $records as $record_id ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record_id );

			if ( is_wp_error( $record ) ) {
				$errors[ $record_id ] = $record;
				continue;
			}

			if ( ! current_user_can( $record_obj->cap->delete_post, $record->id ) ) {
				$errors[ $record->id ] = tribe_error( 'core:aggregator:delete-record-permissions', array( 'record' => $record ) );
				continue;
			}

			$status = $record->delete( true );

			if ( is_wp_error( $status ) ) {
				$errors[ $record->id ] = $status;
				continue;
			}

			$success[ $record->id ] = true;
		}

		return array( $success, $errors );
	}

	private function action_run_import( $records = array() ) {
		$record_obj = Tribe__Events__Aggregator__Records::instance()->get_post_type();
		$records = array_filter( (array) $records, 'is_numeric' );
		$success = array();
		$errors = array();

		foreach ( $records as $record_id ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record_id );

			if ( is_wp_error( $record ) ) {
				$errors[ $record_id ] = $record;
				continue;
			}

			$child = $record->create_child_record();
			$status = $child->queue_import();

			if ( is_wp_error( $status ) ) {
				$errors[ $record->id ] = $status;
				continue;
			}

			$success[ $record->id ] = $status;
		}

		return array( $success, $errors );
	}
}