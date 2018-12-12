<?php
/**
 * Events Gutenberg Assets
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Assets {
	/**
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function hook() {

	}

	/**
	 * Registers and Enqueues the assets
	 *
	 * @since 4.7
	 *
	 * @param string $key Which key we are checking against
	 *
	 * @return boolean
	 */
	public function register() {

		$plugin = tribe( 'tec.main' );

		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-views',
			'app/views.css',
			array(),
			'wp_enqueue_scripts',
			array(
				'groups'       => array( 'events-views' ),
				'conditionals' => array( $this, 'should_enqueue_frontend' ),
			)
		);
	}

	/**
	 * Checks if we should enqueue frontend assets
	 *
	 * @since 4.7
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend() {
		$should_enqueue = (
			tribe_is_event_query()
			|| tribe_is_event_organizer()
			|| tribe_is_event_venue()
			|| is_active_widget( false, false, 'tribe-events-list-widget' )
		);

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded
		 *
		 * @since 4.7
		 *
		 * @param bool $should_enqueue
		 */
		return apply_filters( 'tribe_events_editor_assets_should_enqueue_frontend', $should_enqueue );
	}
}
