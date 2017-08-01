<?php
/**
 * Sets up and helps to manage inactive events.
 */
class Tribe__Events__Inactive_Events {
	/**
	 * Slug for the Post Type
	 *
	 * @since  TBD
	 */
	const POST_TYPE = 'tribe_inactive_event';

	/**
	 * A instance of the Registered Post Type
	 *
	 * @since TBD
	 *
	 * @var   WP_Post_Type
	 */
	public $obj;

	/**
	 * Register the Post Type in WordPress
	 *
	 * @since  TBD
	 *
	 * @return WP_Post_Type|WP_Error
	 */
	public function register() {
		$supports = array_keys( get_all_post_type_supports( Tribe__Events__Main::POSTTYPE ) );

		$arguments = array(
			'public'          => false,
			'supports'        => $supports,
			'taxonomies'      => array( Tribe__Events__Main::TAXONOMY, 'post_tag' ),
			'capability_type' => array( 'tribe_event', 'tribe_events' ),
			'map_meta_cap'    => true,
		);

		/**
		 * Filters the register_post_type arguments for Inactive Events
		 *
		 * @since  TBD
		 *
		 * @param  array  $arguments  Information to setup the Inactive Event Post Type
		 */
		$arguments = apply_filters( 'tribe_events_inactive_event_post_type_arguments', $arguments );

		$this->obj = register_post_type( self::POST_TYPE, $arguments );

		return $this->obj;
	}
}