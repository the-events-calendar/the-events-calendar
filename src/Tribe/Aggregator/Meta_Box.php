<?php

class Tribe__Events__Aggregator__Meta_Box {
	/**
	 * @var Tribe__Events__Aggregator Event Aggregator bootstrap class
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
	}

	public function add() {
		$post_id = get_the_ID();

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_event_id( $post_id );

		if ( is_wp_error( $record ) ) {
			return;
		}

		add_meta_box(
			'tribe-aggregator-import-info',
			esc_html__( 'Imported Event', 'the-events-calendar' ),
			array( $this, 'render' ),
			Tribe__Events__Main::POSTTYPE,
			'side',
			'default'
		);
	}

	public function render() {
		$aggregator = Tribe__Events__Aggregator::instance();

		$event_id = get_the_ID();
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_event_id( $event_id );
		$origin = empty( $aggregator->api( 'origins' )->origin_names[ $record->origin ] ) ? __( 'Event Aggregator', 'the-events-calendar' ) : $aggregator->api( 'origins' )->origin_names[ $record->origin ];
		$source = $record->meta['source_name'];
		$last_import = tribe_format_date( $record->post->post_modified );
		$settings_link = Tribe__Settings::instance()->get_url( array( 'tab' => 'imports' ) );
		$import_setting = tribe_get_option( 'tribe_aggregator_default_update_authority', 'retain' );

		include Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/meta-box.php';
	}
}
