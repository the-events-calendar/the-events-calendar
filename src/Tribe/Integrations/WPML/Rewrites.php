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
	 * @var string The English version of the venue slug
	 */
	protected $venue_slug = 'venue';
	/**
	 * @var string The English version of the organizer slug
	 */
	protected $organizer_slug = 'organizer';
	/**
	 * @var array An array of translations for the venue slug
	 */
	protected $venue_slug_translations = array();
	/**
	 * @var array An array of translations for the organizer slug
	 */
	protected $organizer_slug_translations = array();

	/**
	 * @var array An array containing the translated version of each venue and organizer rule
	 */
	protected $translated_rules = array();

	/**
	 * @var array
	 */
	protected $replacement_rules = array();

	/**
	 * @return Tribe__Events__Integrations__WPML__Linked_Posts
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function filter_rewrite_rules_array( array $rewrite_rules ) {
		$this->prepare_venue_slug_translations();
		$this->prepare_organizer_slug_translations();

		array_walk( $rewrite_rules, array( $this, 'translate_venue_rules' ) );
		array_walk( $rewrite_rules, array( $this, 'translate_organizer_rules' ) );

		return $this->replace_rules_with_translations( $rewrite_rules );
	}

	protected function prepare_venue_slug_translations() {
		$wpml_i18n_strings             = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings( array( $this->venue_slug ) );
		$post_slug_translations        = $this->get_post_slug_translations_for( Tribe__Events__Venue::POSTTYPE );
		$slug_translations             = array_merge( $wpml_i18n_strings[0], $post_slug_translations );
		$this->venue_slug_translations = array_map( 'esc_attr', array_unique( $slug_translations ) );
	}

	protected function prepare_organizer_slug_translations() {
		$wpml_i18n_strings                 = Tribe__Events__Integrations__WPML__Utils::get_wpml_i18n_strings( array( $this->organizer_slug ) );
		$post_slug_translations            = $this->get_post_slug_translations_for( Tribe__Events__Organizer::POSTTYPE );
		$slug_translations                 = array_merge( $wpml_i18n_strings[0], $post_slug_translations );
		$this->organizer_slug_translations = array_map( 'esc_attr', array_unique( $slug_translations ) );
	}

	protected function replace_rules_with_translations( array $rewrite_rules ) {
		$keys      = array_keys( $rewrite_rules );
		$values    = array_values( $rewrite_rules );
		$positions = array_flip( $keys );

		$replaced_keys = $keys;
		foreach ( $this->replacement_rules as $original => $replacement ) {
			$original_position                   = $positions[ $original ];
			$replaced_keys[ $original_position ] = $replacement;
		}


		return array_combine( $replaced_keys, $values );
	}

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

	protected function get_post_slug_translations_for( $type ) {
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
						SELECT t.value
						FROM {$wpdb->prefix}icl_string_translations t
							JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id
						WHERE s.name = %s AND t.status = %d
					", 'URL slug: ' . $type, ICL_TM_COMPLETE ) );

		return wp_list_pluck( $results, 'value' );
	}
}
