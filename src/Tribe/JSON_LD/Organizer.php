<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * A JSON-LD class extended of the Abstract that lies on the Common Package
 * Used for generating a Venue/Place JSON-LD markup
 */
class Tribe__Events__JSON_LD__Organizer extends Tribe__JSON_LD__Abstract {

	/**
	 * Which type of element this actually is
	 *
	 * @see https://developers.google.com/structured-data/rich-snippets/
	 * @var string
	 */
	public $type = 'Person';

	/**
	 * On PHP 5.2 the child class doesn't get spawned on the Parent one, so we don't have
	 * access to that information on the other side unless we pass it around as a param
	 * so we throw __CLASS__ to the parent::instance() method to be able to spawn new instance
	 * of this class and save on the parent::$instances variable.
	 *
	 * @return Tribe__Events__JSON_LD__Organizer
	 */
	public static function instance( $name = null ) {
		return parent::instance( __CLASS__ );
	}

	/**
	 * Fetches the JSON-LD data for this type of object
	 *
	 * @param  int|WP_Post|null $post The post/organizer
	 * @param  array  $args
	 * @return array
	 */
	public function get_data( $post = null, $args = [ 'context' => false ] ) {
		$data = parent::get_data( $post, $args );

		// If we have an Empty data we just skip
		if ( empty( $data ) ) {
			return [];
		}

		// Fetch first key
		$post_id = key( $data );

		// Fetch first Value
		$data = reset( $data );

		$data->telephone = tribe_get_organizer_phone( $post_id );
		$data->email     = tribe_get_organizer_email( $post_id );
		$data->url       = tribe_get_organizer_website_url( $post_id );
		$data->sameAs    = tribe_get_organizer_website_url( $post_id );

		$data = $this->apply_object_data_filter( $data, $args, $post );

		return [ $post_id => $data ];
	}

	/**
	 * Get a link to the event
	 *
	 * @since 4.5.10
	 *
	 * @param  int|WP_Post  $post The Post Object or ID
	 *
	 * @return false|string Link to the event or false
	 */
	protected function get_link( $post ) {
		// @todo [BTRIA-591]: Move this logic to Pro once #33734 is handled.
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			$link = tribe_get_organizer_link( $post, false );
		} else {
			$link = false;
		}

		return $link;
	}

}
