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
		$this->supported_post_types = null !== $supported_post_types ? $supported_post_types : [
			Tribe__Events__Organizer::POSTTYPE,
			Tribe__Events__Venue::POSTTYPE,
		];
	}

	/**
	 * @return Tribe__Events__Integrations__WPML__Permalinks
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the post type link to remove WPML language query arg/frags on non translated posts.
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

		if ( $post_language === ICL_LANGUAGE_CODE ) {
			return $post_link;
		}

		// append the language as a query argument
		$language_negotiation_type = $sitepress->get_setting( 'language_negotiation_type' );


		switch ( $language_negotiation_type ) {
			case 1;
			case 2;
				$post_link = $this->get_post_permalink( $post, $post_language );
				break;
			case 3:
				$post_link = $this->update_language_query_arg( $post_link, $post_language );
				break;
			default:
				break;
		}

		return $post_link;
	}

	/**
	 * Returns the post link withe the language query arg removed or updated to the post language.
	 *
	 * @param string  $post_link
	 * @param WP_Post $post
	 * @param string  $post_language The post language code.
	 *
	 * @return string
	 */
	protected function update_language_query_arg( $post_link, $post_language ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		if ( $post_language !== ICL_LANGUAGE_CODE ) {
			if ( $post_language === $sitepress->get_default_language() ) {
				$post_link = remove_query_arg( 'lang', $post_link );

				return $post_link;
			} else {
				$post_link = remove_query_arg( 'lang', $post_link );
				$post_link = add_query_arg( [ 'lang' => $post_language ], $post_link );

				return $post_link;
			}
		}

		return $post_link;
	}

	/**
	 * Returns the post permalink taking the post language into account.
	 *
	 * @param WP_Post      $post
	 * @param       string $post_language The post language code.
	 *
	 * @return string The post permalink.
	 */
	protected function get_post_permalink( WP_Post $post, $post_language ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		$sitepress->switch_lang( $post_language );
		remove_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 20 );

		$post_link = get_permalink( $post->ID );

		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 20, 2 );
		$sitepress->switch_lang( ICL_LANGUAGE_CODE );

		return $post_link;
	}
}
