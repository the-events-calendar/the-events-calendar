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
	public function filter_icl_ls_languages( array $languages = array() ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return $languages;
		}

		if ( is_admin() || ! ( tribe_is_event_query() && is_archive() && ! is_tax( Tribe__Events__Main::TAXONOMY ) ) ) {
			return $languages;
		}

		$root_folder = parse_url( home_url(), PHP_URL_PATH );
		$request_uri = $_SERVER['REQUEST_URI'];

		if ( ! empty( $root_folder ) ) {
			$request_uri = str_replace( $root_folder, '', $request_uri );
		}

		$current_url      = home_url( $request_uri );
		$current_query    = parse_url( $current_url, PHP_URL_QUERY );
		$current_request  = str_replace( $current_query, '', $current_url );
		$current_language = $sitepress->get_current_language();
		$original_url     = $languages[ $current_language ]['url'];

		$endpoint = trim( str_replace( $original_url, '', $current_request ), '/?' );
		if ( ! empty( $endpoint ) ) {
			foreach ( [ 'month', 'list', 'today' ] as $slug ) {
				if ( __( $slug, 'the-events-calendar' ) === $endpoint ) {
					$endpoint = $slug;
					break;
				}
			}

			foreach ( $languages as &$language ) {
				$sitepress->switch_lang( $language['code'] );

				$language['url'] = trailingslashit( $language['url'] . __( $endpoint, 'the-events-calendar' ) );
				if ( ! empty( $current_query ) ) {
					$language['url'] .= '?' . $current_query;
				}
			}
			$sitepress->switch_lang( $current_language );
		}

		return $languages;
	}
}
