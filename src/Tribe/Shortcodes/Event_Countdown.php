<?php
/**
 * Implements a shortcode that wraps the existing event coundtown widget. Basic usage
 * is as follows (using an event's post ID):
 *
 *     [tribe_event_countdown id="123"]
 *
 * If preferred, the event slug can be used:
 *
 *     [tribe_event_countdown slug="some-event"]
 *
 * Display of seconds is optional but can be enabled by adding a show_seconds="1"
 * attribute. To specify the text that should display once the event time rolls round
 * a complete attribute is available.
 *
 *     [tribe_event_countdown slug="party-time" show_seconds="1" complete="The party is on!"]
 */
class Tribe__Events__Pro__Shortcodes__Event_Countdown {
	public $output = '';

	/**
	 * Default arguments expected by the countdown widget.
	 *
	 * @var array
	 */
	protected $default_args = array(
		// General widget properties
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		// Widget specific properties
		'id'           => '',
		'slug'         => '',
		'show_seconds' => '',
		'complete'     => '',
	);

	protected $arguments = array();


	public function __construct( $attributes ) {
		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->parse_args();
		$this->set_date();

		// If we don't have an event date we cannot display the timer
		if ( ! isset( $this->arguments['event_date'] ) ) {
			return;
		}

		// If the ECP CSS has not already been added lets lazily add it now
		Tribe__Events__Pro__Template_Factory::asset_package( 'events-pro-css' );

		ob_start();
		the_widget( 'Tribe__Events__Pro__Countdown_Widget', $this->arguments, $this->arguments );
		$this->output = ob_get_clean();
	}

	protected function parse_args() {
		if ( ! empty( $this->arguments['id'] ) ) {
			$this->arguments['event_ID'] = (int) $this->arguments['id'];
		} elseif ( ! empty( $this->arguments['slug'] ) ) {
			$this->set_by_slug();
		}
	}

	/**
	 * Facilitates specifying the event by providing its slug.
	 */
	protected function set_by_slug() {
		$events = get_posts( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'name' => $this->arguments['slug'],
			'posts_per_page' => 1,
		) );

		if ( empty( $events ) ) {
			return;
		}

		$event = array_shift( $events );
		$this->arguments['event_ID'] = (int) $event->ID;
	}

	/**
	 * The countdown widget requires the date of the event to be passed in
	 * as an argument.
	 */
	protected function set_date() {
		if ( ! isset( $this->arguments['event_ID'] ) ) {
			return;
		}

		$this->arguments['event_date'] = tribe_get_start_date( $this->arguments['event_ID'], false, Tribe__Events__Date_Utils::DBDATEFORMAT );
	}
}
