<?php
/**
 * Sets up and helps to manage inactive events.
 */
class Tribe__Events__Inactive_Events {
	const POST_TYPE = 'tribe_inactive_event';

	public function hook() {
		add_action( 'init', array( $this, 'register' ), 20 );
	}

	public function register() {
		$supports = array_keys( get_all_post_type_supports( Tribe__Events__Main::POSTTYPE ) );

		/**
		 * Defines the inactive event post type.
		 *
		 * @var array $arguments
		 */
		register_post_type( self::POST_TYPE, apply_filters( 'tribe_events_register_inactive_event_type_args', array(
			'public'          => false,
			'supports'        => $supports,
			'taxonomies'      => array( Tribe__Events__Main::TAXONOMY, 'post_tag' ),
			'capability_type' => array( 'tribe_event', 'tribe_events' ),
			'map_meta_cap'    => true,
		) ) );
	}
}