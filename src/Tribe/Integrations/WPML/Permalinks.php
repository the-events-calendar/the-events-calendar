<?php


/**
 * Class Tribe__Events__Integrations__WPML__Permalinks
 *
 * Handles permalink generations taking WPML into account.
 */
class Tribe__Events__Integrations__WPML__Permalinks {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @var array
	 */
	protected $supported_post_types;

	/**
	 * Tribe__Events__Integrations__WPML__Permalinks constructor.
	 *
	 * @param array|null $supported_post_types An injectable array of supported post types.
	 */
	public function __construct( array $supported_post_types = null ) {
		$this->supported_post_types = null !== $supported_post_types ? $supported_post_types : array(
			Tribe__Events__Main::ORGANIZER_POST_TYPE,
			Tribe__Events__Main::VENUE_POST_TYPE,
		);
	}

	/**
	 * @return Tribe__Events__Integrations__WPML__Permalinks
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Filters the post type link to remove WPML language query arg on non translated posts.
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 */
	public function filter_post_type_link( $post_link, WP_Post $post ) {
		if ( ! in_array( $post->post_type, $this->supported_post_types ) ) {
			return $post_link;
		}

		/** @var SitePress $sitepress */
		global $sitepress;

		$post_language = $sitepress->get_language_for_element( $post->ID, 'post_' . $post->post_type );
		if ( $post_language !== ICL_LANGUAGE_CODE ) {
			$post_link = remove_query_arg( 'lang', $post_link );
		}

		return $post_link;
	}
}