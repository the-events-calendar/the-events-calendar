<?php


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

	protected function prepare_venue_slug_translations() {
		$wpml_i18n_strings = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings(
			[ $this->venue_slug ]
		);
		$post_slug_translations        = Tribe__Events__Integrations__WPML__Utils::get_post_slug_translations_for( Tribe__Events__Venue::POSTTYPE );
		$slug_translations             = array_merge( $wpml_i18n_strings[0], array_values( $post_slug_translations ) );
		$this->venue_slug_translations = array_map( 'esc_attr', array_unique( $slug_translations ) );
	}

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
		$translated_slugs = $category_translation->get_translated_base_slugs();
		$bases['tax'] = array_merge( $bases['tax'], $translated_slugs );

		return $bases;
	}
}
