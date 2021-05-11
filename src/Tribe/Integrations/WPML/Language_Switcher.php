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
		global $wp_query;

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
}
