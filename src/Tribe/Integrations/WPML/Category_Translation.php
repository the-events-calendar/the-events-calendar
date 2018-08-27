<?php


/**
 * Class Tribe__Events__Integrations__WPML__Category_Translation
 *
 * Translates category links on the site front-end.
 */
class Tribe__Events__Integrations__WPML__Category_Translation {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Category_Translation
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the `tribe_events_category_slug` to return the category slug that's WPML aware.
	 *
	 * WPML does not currently support translation of custom taxonomies root ,e.g. `category` in
	 * The Events Calendar case. But we do take WPML-managed translations of the `category` slug
	 * into account in our rewrite rules and try to show a localized version of the `category` slug
	 * in the permalinks.
	 *
	 * @param string $slug The original, possibily translated, category slug.
	 *
	 * @return string The category slug in its ENG form if the Events Category translation is not active
	 *                or in a translation that The Events Calendar supports.
	 */
	public function filter_tribe_events_category_slug( $slug ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		$tax_sync_options     = $sitepress->get_setting( 'taxonomies_sync_option' );
		$translate_event_cat  = ! empty( $tax_sync_options[ Tribe__Events__Main::TAXONOMY ] );
		$using_lang_query_var = $sitepress->get_setting( 'language_negotiation_type' ) == 3;

		if ( ! $translate_event_cat || $using_lang_query_var ) {
			$slug = 'category';
		} else {
			$lang            = $sitepress->get_locale( ICL_LANGUAGE_CODE );
			$tec_translation = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings( array( 'category' ), $lang );
			$slug            = ! empty( $tec_translation[0] ) ? end( $tec_translation[0] ) : $slug;

			$remove_accents = true;

			/**
			 * Whether accents should be removed from the translated slug or not.
			 *
			 * Returning a falsy value here will prevent accents from being removed.
			 * E.g. "catégorie" would become "categorie" if this filter value is truthy;
			 * it would instead remain unchanged if this filters returns a falsy value.
			 *
			 * @param bool $remove_accents Defaults to `true`.
			 */
			$remove_accents = apply_filters( 'tribe_events_integrations_category_slug_remove_accents', $remove_accents );

			if ( $remove_accents ) {
				$slug = remove_accents( urldecode( $slug ) );
			}
		}

		return $slug;
	}

	/**
	 * Supplies an array containing all translated forms of the events category slug.
	 *
	 * The default (English) slug will not be containied in the resulting array.
	 * Example: [ 'categorie', 'kategorie', 'categoria' ] // French, German, Italian
	 *
	 * @return array
	 */
	public function get_translated_base_slugs() {
		/** @var SitePress $sitepress */
		global $sitepress;

		$translations     = array();
		$tax_sync_options = $sitepress->get_setting( 'taxonomies_sync_option' );
		$should_translate = ! empty( $tax_sync_options[ Tribe__Events__Main::TAXONOMY ] );

		// If the event category slug shouldn't be translated, return an empty list
		if ( ! $should_translate ) {
			return array();
		}

		// Determine the translated form of the category slug for each active locale
		foreach ( Tribe__Events__Integrations__WPML__Utils::get_active_locales() as $lang ) {
			$slugs = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings( array( 'category' ), $lang );

			// We expect an array of arrays with at least one element to come back
			if ( empty( $slugs[0] ) ) {
				continue;
			}

			// Following the strategy used elsewhere, use the final translation if there are multiple
			$translations[] = end( $slugs[0] );
		}

		$translations = array_map( 'urldecode', $translations );
		$translations = array_map( 'remove_accents', $translations );
		return array_unique( $translations );
	}
}
