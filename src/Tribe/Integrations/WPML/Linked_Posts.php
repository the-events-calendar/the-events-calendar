<?php


/**
 * Class Tribe__Events__Integrations__WPML__Linked_Posts
 *
 * Handles linked posts fetching taking WPML managed translations into account.
 */
class Tribe__Events__Integrations__WPML__Linked_Posts {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Linked_Posts
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param array $results  An array of linked post types results; comes `null` from the filter but other plugins
	 *                        might set it differently.
	 * @param array $args     An array of WP_Query args
	 *
	 * @return array|null An array of linked posts populated taking WPML managed translations into account or `null` if
	 *                    WPML is not active or the current language is the default one.
	 */
	public function filter_tribe_events_linked_posts_query( $results = null, array $args = array() ) {
		if ( isset( $args['post__not_in'] ) ) {
			return null;
		}

		/** @var SitePress $sitepress */
		global $sitepress;

		if ( empty( $sitepress ) || ! is_a( $sitepress, 'SitePress' ) ) {
			return null;
		}

		if ( $sitepress->get_default_language() === ICL_LANGUAGE_CODE ) {
			return null;
		}

		// IDs only and drop the order to avoid wasting time on something we'll account for later
		$sub_query_args = array_merge( $args, array( 'fields' => 'ids', 'order' => false ) );

		$linked_posts_ids = $this->get_curent_language_linked_posts_ids( $sub_query_args );

		$default_lang_linked_posts_ids = $this->get_default_language_linked_post_ids( $sub_query_args );

		$linked_posts_ids = array_merge( $default_lang_linked_posts_ids, $linked_posts_ids );

		// run this query to keep the specified `orderby`
		$linked_posts = get_posts( array_merge( $args, array( 'post__in' => $linked_posts_ids ) ) );

		$sitepress->switch_lang( ICL_LANGUAGE_CODE );

		return $linked_posts;
	}

	/**
	 * @param int $id The post ID
	 *
	 * @return bool `true` if the post lacks a WPML managed translation, `false` if the post has a WPML managed translation.
	 */
	protected function is_not_translated( $id ) {
		/** @var SitePress $sitepress */
		global $sitepress;
		$translation_id = $sitepress->get_object_id( $id, 'post', true, ICL_LANGUAGE_CODE );

		return empty( $translation_id ) || $translation_id == $id;
	}

	/**
	 * @param array $args An array WP_Query arguments
	 *
	 * @return array An array of linked posts filtered by the current language
	 */
	protected function get_curent_language_linked_posts_ids( array $args ) {
		/** @var SitePress $sitepress */
		global $sitepress;
		$sitepress->switch_lang( ICL_LANGUAGE_CODE );

		// run the query using the current language (WPML does it under the hood)
		// the user might have posts that are *only* translated and none in the default language
		$query = new WP_Query( $args );

		return $query->have_posts() ? $query->posts : array();
	}

	/**
	 * @param array $args An array WP_Query arguments
	 *
	 * @return array An array of linked posts filtered by the default language
	 */
	protected function get_default_language_linked_post_ids( array $args ) {
		/** @var SitePress $sitepress */
		global $sitepress;
		$sitepress->switch_lang( $sitepress->get_default_language() );

		$query = new WP_Query( $args );

		$posts = $query->have_posts() ? $query->posts : array();

		return array_filter( $posts, array( $this, 'is_not_translated' ) );
	}
}