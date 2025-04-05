<?php

use Tribe\Events\I18n;
use Tribe__Rewrite as Common_Rewrite;


/**
 * Class Tribe__Events__Integrations__WPML__Rewrites
 *
 * Handles modifications to rewrite rules taking WPML into account.
 */
class Tribe__Events__Integrations__WPML__Rewrites {

	/**
	 * @var Tribe__Events__Integrations__WPML__Linked_Posts
	 */
	protected static $instance;

	/**
	 * @var string The English version of the venue slug.
	 */
	protected $venue_slug = 'venue';

	/**
	 * @var string The English version of the organizer slug.
	 */
	protected $organizer_slug = 'organizer';

	/**
	 * @var array An array of translations for the venue slug
	 */
	protected $venue_slug_translations = [];

	/**
	 * @var array An array of translations for the organizer slug
	 */
	protected $organizer_slug_translations = [];

	/**
	 * @var array An array containing the translated version of each venue and organizer rule
	 */
	protected $translated_rules = [];

	/**
	 * @var array
	 */
	protected $replacement_rules = [];

	/**
	 * A map from language codes to the set of translated bases.
	 *
	 * @since 6.0.13
	 *
	 * @var array<string,array<string,string>>
	 */
	private array $bases_by_language = [];

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
	 * Filters the rewrite rules array to add support for translated versions of
	 * venue and organizer slugs in their rules.
	 *
	 * @since 6.0.9 Moving type check down to safeguard this public filter.
	 *
	 * @param array|mixed $rewrite_rules The rewrite rules associative array from the rewrite_rules_array filter.
	 *
	 * @return array|mixed Translated rewrite rules or what was passed in.
	 */
	public function filter_rewrite_rules_array( $rewrite_rules ) {
		if ( ! is_array( $rewrite_rules ) || empty( $rewrite_rules ) ) {
			return $rewrite_rules;
		}

		return $this->translate_rewrite_rules_array( $rewrite_rules );
	}

	/**
	 * Run translations of the rewrite rules array.
	 *
	 * @since 6.0.9
	 *
	 * @param array $rewrite_rules The rewrite rules to apply translations to.
	 *
	 * @return array The translated rules.
	 */
	public function translate_rewrite_rules_array( array $rewrite_rules ): array {
		$this->prepare_venue_slug_translations();
		$this->prepare_organizer_slug_translations();

		array_walk( $rewrite_rules, [ $this, 'translate_venue_rules' ] );
		array_walk( $rewrite_rules, [ $this, 'translate_organizer_rules' ] );

		return $this->replace_rules_with_translations( $rewrite_rules );
	}

	/**
	 * Translates the venue rewrite rules.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	protected function prepare_venue_slug_translations() {
		$wpml_i18n_strings             = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings(
			[ $this->venue_slug ]
		);
		$post_slug_translations        = Tribe__Events__Integrations__WPML__Utils::get_post_slug_translations_for( Tribe__Events__Venue::POSTTYPE );
		$slug_translations             = array_merge( $wpml_i18n_strings[0], array_values( $post_slug_translations ) );
		$this->venue_slug_translations = array_map( 'esc_attr', array_unique( $slug_translations ) );
	}

	/**
	 * Translates the organizer rewrite rules.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	protected function prepare_organizer_slug_translations() {
		$wpml_i18n_strings                 = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings(
			[ $this->organizer_slug ]
		);
		$post_slug_translations            = Tribe__Events__Integrations__WPML__Utils::get_post_slug_translations_for( Tribe__Events__Organizer::POSTTYPE );
		$slug_translations                 = array_merge( $wpml_i18n_strings[0], array_values( $post_slug_translations ) );
		$this->organizer_slug_translations = array_map( 'esc_attr', array_unique( $slug_translations ) );
	}

	/**
	 * Attempts to replace rules with translations.
	 *
	 * @since 6.0.9 Some safeguard around return value, in case of unexpected rules.
	 *
	 * @param array $rewrite_rules Associative array of rewrite rules to translate.
	 *
	 * @return array Translated rules.
	 */
	protected function replace_rules_with_translations( array $rewrite_rules ): array {
		$keys      = array_keys( $rewrite_rules );
		$values    = array_values( $rewrite_rules );
		$positions = array_flip( $keys );

		$replaced_keys = $keys;
		foreach ( $this->replacement_rules as $original => $replacement ) {
			$original_position                   = $positions[ $original ];
			$replaced_keys[ $original_position ] = $replacement;
		}

		$combined_array = array_combine( $replaced_keys, $values );

		// Something went wrong with our translation merge, return original values.
		if ( ! is_array( $combined_array ) ) {
			return $rewrite_rules;
		}

		return $combined_array;
	}

	/**
	 * Adds support for the translated version of the venue slug in all venues rewrite
	 * rules regular expressions.
	 *
	 * E.g. `venue/then-some` becomes `(?:venue|luogo|lieu)/then-some`; the match uses
	 * non-capturing groups not to mess up the match keys.
	 *
	 * @param string $rule  A rewrite rule scheme assigning pattern matches to vars.
	 * @param string $regex A rewrite rule regular expression.
	 */
	public function translate_venue_rules( $rule, $regex ) {
		if ( ! $this->is_venue_rule( $regex ) ) {
			return;
		}

		$pattern          = '/^(' . $this->venue_slug . ')/';
		$replacement      = '(?:' . implode( '|', $this->venue_slug_translations ) . ')';
		$translated_regex = preg_replace( $pattern, $replacement, $regex );

		$this->replacement_rules[ $regex ] = $translated_regex;
	}

	protected function is_venue_rule( $candidate_rule ) {
		return preg_match( '/^' . $this->venue_slug . '/', $candidate_rule );
	}

	/**
	 * Adds support for the translated version of the organizer slug in all organizers rewrite
	 * rules regular expressions.
	 *
	 * E.g. `organizer/then-some` becomes `(?:organizer|organizzatore|organisateur)/then-some`;
	 * the match uses non-capturing groups not to mess up the match keys.
	 *
	 * @param string $rule  A rewrite rule scheme assigning pattern matches to vars.
	 * @param string $regex A rewrite rule regular expression.
	 */
	public function translate_organizer_rules( $rule, $regex ) {
		if ( ! $this->is_organizer_rule( $regex ) ) {
			return;
		}

		$pattern          = '/^(' . $this->organizer_slug . ')/';
		$replacement      = '(?:' . implode( '|', $this->organizer_slug_translations ) . ')';
		$translated_regex = preg_replace( $pattern, $replacement, $regex );

		$this->replacement_rules[ $regex ] = $translated_regex;
	}

	protected function is_organizer_rule( $candidate_rule ) {
		return preg_match( '/^' . $this->organizer_slug . '/', $candidate_rule );
	}

	/**
	 * Adds translated versions of the events category base slug to the rewrite rules.
	 *
	 * @param array  $bases
	 * @param string $method
	 *
	 * @return array
	 */
	public function filter_tax_base_slug( $bases, $method ) {
		// We only want to make changes if there is a tax key and if the method is 'regex'
		if ( ! isset( $bases['tax'] ) || 'regex' !== $method ) {
			return $bases;
		}

		// Fetch translated versions of the event category slug and append them
		$category_translation = Tribe__Events__Integrations__WPML__Category_Translation::instance();
		$translated_slugs     = $category_translation->get_translated_base_slugs();
		$bases['tax']         = array_merge( $bases['tax'], $translated_slugs );

		return $bases;
	}

	/**
	 * Translate the Event single slugs.
	 *
	 * @param array<string,array<string>> $bases The bases to translate.
	 *
	 * @return array<string,array<string>> The translated bases.
	 */
	protected function translate_single_slugs( array $bases ): array {
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
	 * Translate the Event archive slugs.
	 *
	 * @since 6.0.13
	 *
	 * @param array<string,array<string>> $bases The bases to translate.
	 *
	 *
	 * @return array<string,array<string>> The translated bases.
	 */
	protected function translate_archive_slugs( array $bases ): array {
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

	/**
	 * Filters the bases used to generate TEC rewrite rules to use WPML managed translations.
	 *
	 * @param array<string,string> $bases  An array of bases to translate.
	 * @param string               $method The method used to generate the rewrite rules, unused by this method.
	 * @param array<string>        $domains
	 *
	 * @return array An array of bases each with its (optional) WPML managed translations set.
	 */
	public function filter_tribe_events_rewrite_i18n_slugs_raw( $bases, $method, $domains ): array {
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

		$i18n        = tribe( 'tec.i18n' );
		$flags       = I18n::COMPILE_STRTOLOWER | I18n::RETURN_BY_LANGUAGE | I18n::COMPILE_SLUG;
		$by_language = $i18n->get_i18n_strings( $untranslated_bases, $languages, $domains, $current_locale, $flags );
		// Store this value to use it in the `filter_localized_matchers` method.
		$this->bases_by_language = $by_language;

		// Merge and deduplicate; the `get_i18n_strings` would do this, but the language information would be lost.
		$translated_bases = array_merge_recursive( ...array_values( $by_language ) );
		foreach ( $translated_bases as &$set ) {
			$set = array_unique( $set );
		}
		unset( $set );

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
	 * Filters the localized matcher to use WPML managed translations.
	 *
	 * @since 6.0.13
	 *
	 * @param string|null $localized_slug The matcher localized slug.
	 * @param string      $base           The query var the matcher is for.
	 *
	 * @return string The localized slug.
	 */
	public function localize_matcher( $localized_slug, $base ) {
		if ( ! is_string( $base ) ) {
			return $localized_slug;
		}

		$current_language = get_locale();

		if ( ! empty( $this->bases_by_language[ $current_language ][ $base ] ) ) {
			return end( $this->bases_by_language[ $current_language ][ $base ] );
		}

		return $localized_slug;
	}

	/**
	 * Decodes the bases that have been encoded by default from TEC.
	 *
	 * Bases are encoded by default to avoid issues with special characters
	 * and back-compatibility.
	 *
	 * @since 6.0.13
	 *
	 * @param array<string<array<string>> $bases The bases to decode.
	 *
	 * @return array<string<array<string>> The decoded bases.
	 */
	public function urldecode_base_slugs( $bases ) {
		if ( ! is_array( $bases ) ) {
			return $bases;
		}

		foreach ( $bases as &$base ) {
			foreach ( $base as &$slug ) {
				$slug = urldecode( $slug );
			}
		}

		return $bases;
	}
}
