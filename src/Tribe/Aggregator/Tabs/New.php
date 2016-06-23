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

		// We need to enqueue Media scripts like this
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );
	}

	public function enqueue_media() {
		if ( ! $this->is_active() ) {
			return;
		}

		wp_enqueue_media();
	}

	public $priority = 10;

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'new';
	}

	public function get_label() {
		return esc_html__( 'New Import', 'the-events-calendar' );
	}

	public function ajax_csv_content_type() {
		$response = (object) array(
			'results' => array()
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

		return wp_send_json( $response );
	}

	public function ajax_csv_files() {
		$response = (object) array(
			'results' => array()
		);

		$query = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'text/csv',
		) );

		if ( ! $query->have_posts() ){
			return wp_send_json( $response );
		}

		foreach ( $query->posts as $k => $post ) {
			$query->posts[ $k ]->text = $post->post_title;
		}

		$response->results = $query->posts;

		if ( $query->max_num_pages >= $request->query['paged'] ){
			$response->more = false;
		}

		return wp_send_json( $response );
	}
}