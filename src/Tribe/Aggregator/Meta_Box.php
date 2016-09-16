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
		$origin = $aggregator->api( 'origins' )->get_name( $record->origin );
		$source_info = $record->get_source_info();
		$source = $source_info['title'];

		$datepicker_format = Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );
		$last_import = tribe_format_date( $record->post->post_modified, true, $datepicker_format . ' h:i a' );
		$settings_link = Tribe__Settings::instance()->get_url( array( 'tab' => 'imports' ) );
		$import_setting = tribe_get_option( 'tribe_aggregator_default_update_authority', Tribe__Events__Aggregator__Settings::$default_update_authority );

		include Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/meta-box.php';
	}
}
