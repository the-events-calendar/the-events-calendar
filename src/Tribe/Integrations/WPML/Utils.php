<?php


/**
 * Class Tribe__Events__Integrations__WPML__Utils
 *
 * A utility class offering WPML related convenience methods.
 */
class Tribe__Events__Integrations__WPML__Utils {

	/**
	 * Returns the translation of an array of strings using WPML supported languages to do so.
	 *
	 * @param array  $strings
	 *
	 * @param string $locale    Optional; the locale the strings should be translated to;
	 *                          should be in the "fr_FR" format.
	 *
	 * @return array
	 */
	public static function get_wpml_i18n_strings( array $strings, $locale = null, array $domains = null ) {
		array_multisort( $strings );
		$cache     = new Tribe__Cache();
		$cache_key = 'wpml-i18n-strings_' . serialize( $strings );

		$cached_translations = $cache->get_transient( $cache_key, 'wpml_updates' );

		if ( ! empty( $cached_translations ) ) {
			return $cached_translations;
		}

		$tec     = Tribe__Events__Main::instance();
		$domains = apply_filters( 'tribe_events_rewrite_i18n_domains', array(
			'default'             => true, // Default doesn't need file path
			'the-events-calendar' => $tec->pluginDir . 'lang/',
		) );

		/** @var SitePress $sitepress */
		global $sitepress;

		if ( null === $locale ) { // Grab all languages
			$langs = $sitepress->get_active_languages();

			$languages = array();

			foreach ( $langs as $lang ) {
				$languages[] = $sitepress->get_locale( $lang['code'] );
			}
		} else {
			$languages = array( $locale );
		}

		// Prevent Duplicates and Empty langs
		$languages = array_filter( array_unique( $languages ) );

		// Query the Current Language
		$current_locale = $sitepress->get_locale( $sitepress->get_current_language() );

		// Get the strings on multiple Domains and Languages
		// WPML filter is unhooked to avoid the locale being set to the default one
		remove_filter( 'locale', array( $sitepress, 'locale_filter' ) );
		$translations = $tec->get_i18n_strings_for_domains( $strings, $languages, $domains, $current_locale );
		add_filter( 'locale', array( $sitepress, 'locale_filter' ) );

		// once an option is updated this cache is deprecated
		$cache->set_transient( $cache_key, $translations, 0, 'wpml_updates' );

		return $translations;
	}


	/**
	 * Fetches the optional post slug translations for a post type.
	 *
	 * WPML allows translating a custom post type slug  when the String Translation
	 * accessory plugin is active.
	 *
	 * @param string $type The custom post type slug.
	 *
	 * @return array An associative array in the format [ <language> => <translation> ] of
	 *               translations for the slug or an empty array if String Translation is not active or
	 *               the post type slug is not translated. Please note that the translation does not
	 *               include the original slug.
	 */
	public static function get_post_slug_translations_for( $type ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		$post_slug_translation_settings = $sitepress->get_setting( 'posts_slug_translation', array() );

		if ( empty( $post_slug_translation_settings['types'][ $type ] )
		     || ! $sitepress->is_translated_post_type( $type )
		) {
			return array();
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare( "
						SELECT t.language, t.value
						FROM {$wpdb->prefix}icl_string_translations t
							JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id
						WHERE s.name = %s AND t.status = %d
					", 'URL slug: ' . $type, ICL_TM_COMPLETE ) );

		if ( empty( $results ) ) {
			return array();
		}

		return array_combine( wp_list_pluck( $results, 'language' ), wp_list_pluck( $results, 'value' ) );
	}
}
