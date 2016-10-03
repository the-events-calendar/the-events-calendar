<?php


class Tribe__Events__Integrations__WPML__Filters {

	/**
	 * @var Tribe__Events__Integrations__WPML__Filters
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Filters
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the bases used to generate TEC rewrite rules to use WPML managed translations.
	 *
	 * @param array  $bases
	 * @param string $method
	 * @param array  $domains
	 *
	 * @return array An array of bases each with its (optional) WPML managed translations set.
	 */
	public function filter_tribe_events_rewrite_i18n_slugs_raw( $bases, $method, $domains ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		$tec = Tribe__Events__Main::instance();

		// Grab all languages
		$langs = $sitepress->get_active_languages();

		foreach ( $langs as $lang ) {
			$languages[] = $sitepress->get_locale( $lang['code'] );
		}

		// Prevent Duplicates and Empty langs
		$languages = array_filter( array_unique( $languages ) );

		// Query the Current Language
		$current_locale = $sitepress->get_locale( $sitepress->get_current_language() );

		// Get the strings on multiple Domains and Languages
		// remove WPML filter to avoid the locale being set to the default one
		remove_filter( 'locale', array( $sitepress, 'locale_filter' ) );

		$bases = $tec->get_i18n_strings( $bases, $languages, $domains, $current_locale );

		// re-hook WPML filter
		add_filter( 'locale', array( $sitepress, 'locale_filter' ) );

		return $bases;
	}
}
