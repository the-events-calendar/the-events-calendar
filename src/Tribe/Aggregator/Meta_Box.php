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
		$origin = get_post_meta( $post_id, Tribe__Events__Aggregator__Event::$origin_key, true );

		if ( is_wp_error( $record ) && ! $origin ) {
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
		$aggregator = tribe( 'events-aggregator.main' );

		$event_id = get_the_ID();
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_event_id( $event_id );

		$last_import = null;
		$source = null;
		$origin = null;

		if ( is_wp_error( $record ) ) {
			$last_import = get_post_meta( $event_id, Tribe__Events__Aggregator__Event::$updated_key, true );
			$source = get_post_meta( $event_id, Tribe__Events__Aggregator__Event::$source_key, true );
			$origin = get_post_meta( $event_id, Tribe__Events__Aggregator__Event::$origin_key, true );
		} else {
			$last_import = $record->post->post_modified;
			$source_info = $record->get_source_info();
			$source = $source_info['title'];
			$origin = $record->origin;
		}

		$origin = $aggregator->api( 'origins' )->get_name( $origin );
		$datepicker_format = Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );
		$last_import = $last_import ? tribe_format_date( $last_import, true, $datepicker_format . ' h:i a' ) : null;
		$settings_link = Tribe__Settings::instance()->get_url( array( 'tab' => 'imports' ) );
		$import_setting = tribe_get_option( 'tribe_aggregator_default_update_authority', Tribe__Events__Aggregator__Settings::$default_update_authority );

		include Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/meta-box.php';
	}
}
