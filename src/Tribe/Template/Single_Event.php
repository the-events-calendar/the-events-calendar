<?php
/**
 * @for     Single Event Template
 * This file contains the hook logic required to create an effective single event view.
 *
 * @package TribeEventsCalendar
 *
 */
/**
 * Single event template class
 */
class Tribe__Events__Template__Single_Event extends Tribe__Events__Template_Factory {

	protected $body_class = 'events-single';

	public function hooks() {
		parent::hooks();

		// Print JSON-LD markup on the `wp_head`
		add_action( 'wp_head', [ Tribe__Events__JSON_LD__Event::instance(), 'markup' ] );

		// Add hook for body classes.
		add_filter( 'tribe_body_classes_should_add', [ $this, 'body_classes_should_add' ], 10, 2 );
	}

	/**
	 * Set up the notices for this template
	 *
	 **/
	public function set_notices() {
		parent::set_notices();
		$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();

		if ( ! tribe_is_showing_all() && tribe_is_past_event() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), $events_label_singular_lowercase ) );
		}
	}

	/**
	 * Hook into filter and add our logic for adding body classes.
	 *
	 * @since 5.1.5
	 *
	 * @param boolean $add              Whether to add classes or not.
	 * @param string  $queue            The queue we want to get 'admin', 'display', 'all'.
	 *
	 * @return boolean Whether body classes should be added or not.
	 */
	public function body_classes_should_add( $add, $queue ) {
		// If we're on the front end and doing an event query, add classes.
		if ( 'admin' !== $queue && tribe_is_event_query() ) {
			return true;
		}

		return $add;
	}
}