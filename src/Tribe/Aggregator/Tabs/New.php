<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Aggregator__Tabs__New extends Tribe__Events__Aggregator__Tabs__Abstract {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	public $priority = 10;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		// Setup Abstract hooks
		parent::__construct();

		// Configure this tab ajax calls
		add_action( 'wp_ajax_tribe_ea_dropdown_csv_content_type', array( $this, 'ajax_csv_content_type' ) );
		add_action( 'wp_ajax_tribe_ea_dropdown_csv_files', array( $this, 'ajax_csv_files' ) );
		add_action( 'wp_ajax_tribe_ea_dropdown_origins', array( $this, 'ajax_origins' ) );
		add_action( 'wp_ajax_tribe_save_credentials', array( $this, 'ajax_save_credentials' ) );
		add_action( 'wp_ajax_tribe_create_import', array( $this, 'ajax_create_import' ) );
		add_action( 'wp_ajax_tribe_fetch_import', array( $this, 'ajax_fetch_import' ) );

		// We need to enqueue Media scripts like this
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );

		add_action( 'tribe_aggregator_page_submit', array( $this, 'handle_submit' ) );
	}

	public function enqueue_media() {
		if ( ! $this->is_active() ) {
			return;
		}

		wp_enqueue_media();
	}

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'new';
	}

	public function get_label() {
		return esc_html__( 'New Import', 'the-events-calendar' );
	}

	public function handle_submit() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['aggregator'] ) ) {
			return;
		}

		// @todo: validate nonce

		$post_data = $_POST['aggregator'];

		if ( empty( $post_data['origin'] ) || empty( $post_data[ $post_data['origin'] ] ) ) {
			return;
		}

		$data = $post_data[ $post_data['origin'] ];

		$record = Tribe__Events__Aggregator__Record__Factory::get_by_origin( $post_data['origin'] );

		$meta = array(
			'origin'    => $post_data['origin'],
			'type'      => empty( $data['import_type'] ) ? 'manual' : $data['import_type'],
			'frequency' => empty( $data['import_frequency'] ) ? null : $data['import_frequency'],
			'source'    => empty( $data['source'] ) ? null : $data['source'],
		);

		$post = $record->create( $meta['origin'], $meta['type'], $meta );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$result = $record->queue_import();

		return $result;
	}

	public function ajax_csv_content_type() {
		$response = (object) array(
			'results' => array(),
		);

		// Fetch the Objects from Post Types
		$post_types = array_map( 'get_post_type_object', Tribe__Main::get_post_types() );

		// Building the Response for Select2
		foreach ( $post_types as $post_type ) {
			$response->results[] = array(
				'id' => $post_type->name,
				'text' => $post_type->labels->name,
			);
		}

		return wp_send_json_success( $response );
	}

	public function ajax_csv_files() {
		$response = (object) array(
			'results' => array(),
		);

		$query = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'text/csv',
		) );

		if ( ! $query->have_posts() ) {
			return wp_send_json_error( $response );
		}

		foreach ( $query->posts as $k => $post ) {
			$query->posts[ $k ]->text = $post->post_title;
		}

		$response->results = $query->posts;

		if ( $query->max_num_pages >= $request->query['paged'] ) {
			$response->more = false;
		}

		return wp_send_json_success( $response );
	}

	public function ajax_save_credentials() {
		if ( empty( $_GET['which'] ) ) {
			$data = array(
				'message' => __( 'Invalid credential save request', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tribe-save-credentials' ) ) {
			$data = array(
				'message' => __( 'Invalid credential save nonce', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		if ( 'facebook' === $_GET['which'] ) {
			if ( empty( $_POST['fb_api_key'] ) || empty( $_POST['fb_api_secret'] ) ) {
				$data = array(
					'message' => __( 'The Facebook API key and API secret are both required.', 'the-events-calendar' ),
				);

				wp_send_json_error( $data );
			}

			tribe_update_option( 'fb_api_key', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['fb_api_key'] ) ) );
			tribe_update_option( 'fb_api_secret', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['fb_api_secret'] ) ) );

			$data = array(
				'message' => __( 'Credentials have been saved', 'the-events-calendar' ),
			);

			wp_send_json_success( $data );
		}

		$data = array(
			'message' => __( 'Unable to save credentials', 'the-events-calendar' ),
		);

		wp_send_json_error( $data );
	}

	public function ajax_create_import() {
		$result = $this->handle_submit();

		wp_send_json_success( $result );
	}

	public function ajax_fetch_import() {
		$import_id = $_GET['import_id'];

		wp_send_json_success( $result );
	}
}
