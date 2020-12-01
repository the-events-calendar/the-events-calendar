<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs__History extends Tribe__Events__Aggregator__Tabs__Abstract {

	/**
	 * This Tab Ordering priority
	 * @var integer
	 */
	public $priority = 30;

	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

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

		// Handle Screen Options
		add_action( 'current_screen', [ $this, 'action_screen_options' ] );
		add_filter( 'set-screen-option', [ $this, 'filter_save_screen_options' ], 10, 3 );
	}

	/**
	 * Adds Screen Options for This Tab
	 *
	 * @return void
	 */
	public function action_screen_options( $screen ) {
		if ( ! $this->is_active() ) {
			return;
		}

		$record_screen = WP_Screen::get( Tribe__Events__Aggregator__Records::$post_type );

		$args = [
			'label'   => esc_html__( 'Records per page', 'the-events-calendar' ),
			'default' => 10,
			'option'  => 'tribe_records_history_per_page',
		];

		// We need to Add on both because of a WP Limitation on WP_Screen
		$record_screen->add_option( 'per_page', $args );
		$screen->add_option( 'per_page', $args );
	}

	/**
	 * Allows the saving for our created Page option
	 *
	 * @param mixed  $status Which value should be saved, if false will not save
	 * @param string $option Name of the option
	 * @param mixed  $value  Which value was saved
	 *
	 * @return mixed
	 */
	public function filter_save_screen_options( $status, $option, $value ) {
		if ( 'tribe_records_history_per_page' === $option ) {
			return $value;
		}

		return $status; // or return false;
	}

	public function is_visible() {
		$records = Tribe__Events__Aggregator__Records::instance();

		return $records->has_scheduled() || $records->has_history();
	}

	public function get_slug() {
		return 'history';
	}

	public function get_label() {
		return esc_html__( 'History', 'the-events-calendar' );
	}

}
