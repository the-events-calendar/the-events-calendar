<?php

class Tribe__Events__Integrations__WPML__Language_Switcher {

	/**
	 * @var Tribe__Events__Integrations__WPML__Language_Switcher
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Language_Switcher
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Updates the `url` field in each language information array to preserve correct calendar links.
	 *
	 * While the default view of the calendar will will be served on `/events` non default calendar
	 * views like `list` or `photo` will be served, respectively, at `/events/list`, `/events/photo`
	 * and so on.
	 * For any view that's not the default one the `url` field in the language informtion array has to
	 * be set to the correct one.
	 *
	 * @param array $languages The original languages information array.
	 *
	 * @return array The languages with maybe updated URLs
	 */
	public function filter_icl_ls_languages( array $languages = [] ) {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return $languages;
		}

		if ( is_admin() || ! ( tribe_is_event_query() && is_archive() && ! is_tax( Tribe__Events__Main::TAXONOMY ) ) ) {
			return $languages;
		}

		$view = get_query_var('eventDisplay');
		if ( empty( $view ) ) {
			return $languages;
		}

		$tec = Tribe__Events__Main::instance();

		/** @var SitePress $sitepress */
		global $sitepress;

		$current_language = $sitepress->get_current_language();
		foreach ( $languages as &$language ) {
			$sitepress->switch_lang( $language['code'] );
			$language['url'] = $sitepress->convert_url(
				$tec->getLink( $view, __( $view, 'the-events-calendar' ) ),
				$language['code']
			);
		}
		$sitepress->switch_lang( $current_language );

		return $languages;
	}

	/**
	 * @param array    $translations
	 * @param WP_Query $saved_query
	 *
	 * @return array
	 */
	public function add_ls_to_single_occurrence( $translations, $saved_query ) {
		global $wpdb;

		if ( is_admin() || ! tribe_is_event_query() || is_archive() ) {
			return $translations;
		}

		$provisional_id = apply_filters( 'tec_custom_tables_v1_provisional_post_base_provisional_id', 10000000 );
		if ( $provisional_id && ! empty( $saved_query->post->ID ) && $saved_query->post->ID > $provisional_id ) {

			// Convert the provisional ID to a post ID.
			$post = $wpdb->get_row( $wpdb->prepare(
				"SELECT post_id, start_date_utc, end_date_utc, duration FROM wp_tec_occurrences, (SELECT @row := 0) t WHERE occurrence_id = %s",
				$saved_query->post->ID - $provisional_id
			), ARRAY_A );

			// Get translations of the post ID.
			$post_type    = $saved_query->post->post_type;
			$element_type = apply_filters( 'wpml_element_type', $post_type );
			$element_trid = apply_filters( 'wpml_element_trid', false, $post['post_id'], $element_type );
			$translations = apply_filters( 'wpml_get_element_translations', [], $element_trid, $element_type );

			// Convert the post IDs back to provisional IDs.
			foreach ( $translations as $code => $translation ) {
				$translations[ $code ]->element_id = $wpdb->get_var( $wpdb->prepare(
					"SELECT occurrence_id FROM wp_tec_occurrences WHERE post_id = %s AND start_date_utc = %s AND end_date_utc = %s AND duration = %s",
					apply_filters( 'wpml_object_id', $post['post_id'], $post_type, true, $code ),
					$post['start_date_utc'],
					$post['end_date_utc'],
					$post['duration'],
				) ) + $provisional_id;
			}
		}

		return $translations;
	}

}
