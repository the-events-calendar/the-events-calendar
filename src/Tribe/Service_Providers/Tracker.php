<?php

/**
 * Class Tribe__Events__Service_Providers__Tracker
 *
 * Hooks on the `Tribe__Tracker` functions to make it work for The Events Calendar
 * post types and taxonomies.
 *
 * @since TBD
 */
class Tribe__Events__Service_Providers__Tracker extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		add_filter( 'tribe_tracker_post_types', array( $this, 'filter_tracker_post_types' ) );
		add_filter( 'tribe_tracker_taxonomies', array( $this, 'filter_tracker_taxonomies' ) );
		add_filter( 'tribe_tracker_linked_post_types', array( $this, 'filter_tracker_linked_post_types' ) );
	}

	/**
	 * By default Tribe__Tracker won't track Event Post Types, so we add them here.
	 *
	 * @since  4.5 in Tribe__Events__Main class
	 * @since  TBD in this service provider
	 *
	 * @param  array $post_types The original array of post type slugs.
	 *
	 * @return array An array of post types slugs.
	 */
	public function filter_tracker_post_types( array $post_types ) {
		$post_types[] = Tribe__Events__Main::POSTTYPE;
		$post_types[] = Tribe__Events__Venue::POSTTYPE;
		$post_types[] = Tribe__Events__Organizer::POSTTYPE;

		return $post_types;
	}

	/**
	 * By default Tribe__Tracker won't track our Post Types taxonomies, so we add them here.
	 *
	 * @since  4.5 in Tribe__Events__Main class
	 * @since  TBD in this service provider
	 *
	 * @param  array $taxonomies The original array of taxonomy slugs.
	 *
	 * @return array An array of taxonomy slugs.
	 */
	public function filter_tracker_taxonomies( array $taxonomies ) {
		$taxonomies[] = 'post_tag';
		$taxonomies[] = Tribe__Events__Main::TAXONOMY;

		return $taxonomies;
	}

	/**
	 * Filters the linked post types managed by the tracker to add those relevant
	 * for The Events Calendar.
	 *
	 * @since TBD
	 *
	 * @param array $linked_post_types The original linked post types array.
	 *
	 * @return array An array of linked post types.
	 */
	public function filter_tracker_linked_post_types( array $linked_post_types = array() ) {
		$linked_post_types[ Tribe__Events__Venue::POSTTYPE ]     = array(
			'from_type' => Tribe__Events__Main::POSTTYPE,
			'with_key'  => '_EventVenueID',
		);
		$linked_post_types[ Tribe__Events__Organizer::POSTTYPE ] = array(
			'from_type' => Tribe__Events__Main::POSTTYPE,
			'with_key'  => '_EventOrganizerID',
		);

		return $linked_post_types;
	}
}
