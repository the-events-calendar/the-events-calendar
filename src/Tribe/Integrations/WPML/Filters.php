<?php

use Tribe\Events\I18n;


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
		global $sitepress, $sitepress_settings;

		if ( empty( $sitepress ) || ! $sitepress instanceof SitePress ) {
			return $bases;
		}

		// Grab all languages
		$langs = $sitepress->get_active_languages();

		// Sort the languages to stick w/ the order that will be used to support localized bases.
		ksort( $langs );

		if ( empty( $langs ) ) {
			return $bases;
		}

		foreach ( $langs as $lang ) {
			$languages[] = $sitepress->get_locale( $lang['code'] );
		}

		// Prevent Duplicates and Empty langs
		$languages = array_filter( array_unique( $languages ) );

		// Query the Current Language
		$current_locale = $sitepress->get_locale( $sitepress->get_current_language() );

		// Get the strings on multiple Domains and Languages
		// remove WPML filter to avoid the locale being set to the default one
		remove_filter( 'locale', [ $sitepress, 'locale_filter' ] );

		/*
		 * Translate only the English version of the bases to ensure the order of the translations.
		 */
		$untranslated_bases = array_combine( array_keys( $bases ), array_column( $bases, 0 ) );

		$translated_bases = tribe( 'tec.i18n' )
			->get_i18n_strings( $untranslated_bases, $languages, $domains, $current_locale, I18n::COMPILE_STRTOLOWER );

		// Prepend the WPML-translated bases to the set of bases.
		$bases = array_merge_recursive( $translated_bases, $bases );

		// re-hook WPML filter
		add_filter( 'locale', [ $sitepress, 'locale_filter' ] );

		$string_translation_active = defined( 'WPML_ST_VERSION' );
		$post_slug_translation_on  = ! empty( $sitepress_settings['posts_slug_translation']['on'] );

		if ( $string_translation_active && $post_slug_translation_on ) {
			$bases = $this->translate_single_slugs( $bases );
			$bases = $this->translate_archive_slugs( $bases );
		}

		return $bases;
	}

	/**
	 * @param $bases
	 *
	 * @return array
	 */
	protected function translate_single_slugs( array $bases ) {
		global $sitepress_settings;

		$supported_post_types = [ Tribe__Events__Main::POSTTYPE ];

		foreach ( $supported_post_types as $post_type ) {
			// check that translations are active for this CPT
			$cpt_slug_is_not_translated = empty( $sitepress_settings['posts_slug_translation']['types'][ $post_type ] );

			if ( $cpt_slug_is_not_translated ) {
				continue;
			}

			$event_slug = WPML_Slug_Translation::get_slug_by_type( $post_type );

			$string_id = icl_get_string_id( $event_slug, 'WordPress', 'URL slug: ' . $post_type );

			if ( ! $string_id ) {
				continue;
			}

			$slug_translations = icl_get_string_translations_by_id( $string_id );

			if ( empty( $slug_translations ) ) {
				continue;
			}

			$bases['single'] = array_merge( $bases['single'], wp_list_pluck( $slug_translations, 'value' ) );
		}

		return $bases;
	}

	/**
	 * @param $bases
	 *
	 * @return array
	 */
	protected function translate_archive_slugs( array $bases ) {
		$supported_post_types = array( Tribe__Events__Main::POSTTYPE );

		foreach ( $supported_post_types as $post_type ) {

			$slug = Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' );

			$context   = [ 'domain' => 'the-events-calendar', 'context' => 'Archive Events Slug' ];
			$string_id = icl_get_string_id( $slug, $context );

			if ( ! $string_id ) {
				// If we couldn't find the string, we might need to register it.
				icl_register_string( $context, false, $slug );

				continue;
			}

			$slug_translations = icl_get_string_translations_by_id( $string_id );

			if ( empty( $slug_translations ) ) {
				continue;
			}

			$bases['archive'] = array_merge( $bases['archive'], wp_list_pluck( $slug_translations, 'value' ) );
		}

		return $bases;
	}
}
