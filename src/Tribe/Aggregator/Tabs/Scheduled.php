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

	private function handle_post() {
		if ( ! isset( $_POST['aggregator'] ) ) {
			return false;
		}

		$data = (object) $_POST['aggregator'];

		if ( ! isset( $data->action ) ) {
			return false;
		}

		if ( ! isset( $data->nonce ) || ! wp_verify_nonce( $data->nonce, 'aggregator_' . $this->get_slug() . '_request' ) ) {
			return false;
		}

		if ( 'delete' === $data->action && ! empty( $data->records ) ) {
			$statuses = $this->action_delete_records( $data->records );
			$message = $this->get_delete_notice( $statuses );
		}
	}

	private function handle_get() {
		if ( ! isset( $_GET['action'] ) ) {
			return false;
		}

		$action = $_GET['action'];

		if ( ! in_array( $action, array( 'tribe-run-now', 'tribe-delete' ) ) ) {
			return false;
		}

		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'aggregator_' . $this->get_slug() . '_request' ) ) {
			return false;
		}

		if ( 'tribe-delete' === $action && ! empty( $_GET['item'] ) ) {
			$status = $this->action_delete_records( $_GET['item'] );
			$this->delete_notice( $status );
		}
	}

	/**
	 * @todo Talk to Leah an Get the Error and success messages for Delete
	 *
	 * @param  array   $statuses  Which status occured
	 * @return string
	 */
	private function delete_notice( $statuses = array() ) {
		$errors   = array();
		$success  = 0;
		$count    = count( $statuses );
		$message  = array();

		foreach ( $statuses as $status ) {
			if ( is_wp_error( $status ) ){
				$errors[] = $status->get_error_message();
			} else {
				$success++;
			}
		}

		if ( 0 !== $success && count( $errors ) > 0 ) {
			$args['type'] = 'warning';
		} elseif (  0 !== $success ) {
			$args['type'] = 'success';
		} else {
			$args['type'] = 'error';
		}

		if ( ! empty( $errors ) ) {
			$message[] = implode( '<br/>', $errors );
		} else {
			$message[] = 'Success';
		}

		Tribe__Admin__Notices::instance()->register( 'tribe-aggregator-delete-records', '<p>' . implode( "\r\n", $message ) . '</p>', $args );
	}

	private function action_delete_records( $records = array() ) {
		$records = array_filter( (array) $records, 'is_numeric' );
		$status = array();
		$record_obj = Tribe__Events__Aggregator__Records::instance()->get_post_type();

		foreach ( $records as $record ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record );

			if ( is_wp_error( $record ) ) {
				$status[] = $record;
				continue;
			}

			if ( ! current_user_can( $record_obj->cap->delete_post, $record->id ) ) {
				$status[] = new WP_Error( 'tribe-aggregator-cant-delete', __( 'You do not have pessimions for deleting this Record.' ), $record );
				continue;
			}

			$status[] = $record->delete( true );
		}

		return $status;
	}
}