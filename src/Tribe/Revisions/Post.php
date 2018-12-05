<?php


/**
 * Class Tribe__Events__Revisions__Post
 *
 * Handles the saving operations of a generic post revision.
 *
 * @since 4.2.5
 */
class Tribe__Events__Revisions__Post {

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * Tribe__Events__Revisions__Post constructor.
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * @param int|WP_Post $post
	 *
	 * @return Tribe__Events__Revisions__Post
	 */
	public static function new_from_post( $post ) {
		$types_map = array(
			Tribe__Events__Main::POSTTYPE            => 'Tribe__Events__Revisions__Event',
			Tribe__Events__Main::ORGANIZER_POST_TYPE => 'Tribe__Events__Revisions__Organizer',
			Tribe__Events__Main::VENUE_POST_TYPE     => 'Tribe__Events__Revisions__Venue',
			'post'                                   => __CLASS__,
		);

		$parent_post = get_post( wp_is_post_revision( $post ) );

		$class = ! empty( $parent_post ) && isset( $types_map[ $parent_post->post_type ] ) ? $types_map[ $parent_post->post_type ] : $types_map['post'];

		return new $class( $post );
	}

	/**
	 * Saves the revision.
	 */
	public function save() {
	}
}
