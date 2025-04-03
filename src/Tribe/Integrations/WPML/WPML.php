<?php

use Tribe\Events\Integrations\WPML\Views\V2\Filters as Views_V2_Filters;
use Tribe\Events\Views\V2\Hooks;

/**
 * Class Tribe__Events__Integrations__WPML__WPML
 *
 * Handles anything relating to The Events Calendar and WPML integration
 *
 * This class is meant to be an entry point hooking specialized classes and not
 * a logic hub per se.
 */
class Tribe__Events__Integrations__WPML__WPML {

	/**
	 * @var Tribe__Events__Integrations__WPML__WPML
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Integrations__WPML__WPML
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hooks into The Events Calendar and WPML hooks to make the plugins play nice.
	 */
	public function hook() {
		// the WPML API is not included by default
		require_once ICL_PLUGIN_PATH . '/inc/wpml-api.php';

		tribe_singleton( 'tec.integrations.wpml.meta', 'Tribe__Events__Integrations__WPML__Meta' );

		$this->hook_actions();
		$this->hook_filters();
	}

	protected function hook_actions() {
		$this->setup_cache_expiration_triggers();
		$defaults = Tribe__Events__Integrations__WPML__Defaults::instance();
		if ( ! $defaults->has_set_defaults() ) {
			add_action( 'wpml_parse_config_file', [ $defaults, 'setup_config_file' ] );
		}
		$linked_posts = Tribe__Events__Integrations__WPML__Linked_Posts::instance();
		add_action( 'wpml_translation_update', [ $linked_posts, 'maybe_translate_linked_posts' ], 10, 1 );
	}

	protected function hook_filters() {
		$linked_posts = Tribe__Events__Integrations__WPML__Linked_Posts::instance();
		add_filter( 'tribe_events_linked_posts_query', [
			$linked_posts,
			'filter_tribe_events_linked_posts_query',
		], 10, 2 );
		add_filter( 'tribe_events_linked_post_create', [
			$linked_posts,
			'filter_tribe_events_linked_post_create',
		], 20, 5 );

		$rewrites = Tribe__Events__Integrations__WPML__Rewrites::instance();
		add_filter( 'rewrite_rules_array', [ $rewrites, 'filter_rewrite_rules_array' ], 20, 1 );
		add_filter( 'tribe_events_rewrite_i18n_slugs_raw', [ $rewrites, 'filter_tax_base_slug' ], 10, 2 );
		add_filter(
			'tribe_events_rewrite_i18n_slugs_raw',
			[
				$rewrites,
				'filter_tribe_events_rewrite_i18n_slugs_raw',
			],
			100,
			3
		);
		add_filter( 'tec_common_rewrite_localize_matcher', [ $rewrites, 'localize_matcher' ], 10, 2 );
		// Ensure the base slugs are urldecoded before they are used to build the rewrite rules, go after all the plugins.
		add_filter( 'tribe_events_rewrite_base_slugs', [ $rewrites, 'urldecode_base_slugs' ], 100 );

		$permalinks = Tribe__Events__Integrations__WPML__Permalinks::instance();
		add_filter( 'post_type_link', [ $permalinks, 'filter_post_type_link' ], 20, 2 );

		$language_switcher = Tribe__Events__Integrations__WPML__Language_Switcher::instance();
		add_filter( 'icl_ls_languages', [ $language_switcher, 'filter_icl_ls_languages' ], 5 );

		$meta = tribe( 'tec.integrations.wpml.meta' );
		add_filter( 'get_post_metadata', tribe_callback( $meta, 'translate_post_id' ), 10, 4 );
		add_filter( 'pre_get_posts', tribe_callback( $meta, 'include_all_languages' ) );

		// Disable month view caching when WPML is activated for now, until we
		// fully implement multilingual support for the month view cache.
		add_filter( 'tribe_events_enable_month_view_cache', '__return_false' );

		if ( ! is_admin() ) {
			$category_translation = Tribe__Events__Integrations__WPML__Category_Translation::instance();
			add_filter( 'tribe_events_category_slug', [
				$category_translation,
				'filter_tribe_events_category_slug',
			], 20, 2 );

			$option = Tribe__Events__Integrations__WPML__Option::instance();
			add_filter( 'tribe_get_single_option', [ $option, 'translate' ], 20, 3 );
		}

		/*
		 * Handle Views v2 URLs in all the places that's required.
		 */
		add_filter( 'tribe_events_views_v2_view_url', [ Views_V2_Filters::class, 'translate_view_url' ] );
		add_filter( 'tribe_events_views_v2_view_template_vars', [ Views_V2_Filters::class, 'translate_template_vars_urls' ] );
		add_filter( 'tribe_events_views_v2_view_public_views', [ Views_V2_Filters::class, 'translate_public_views_urls' ] );
		add_filter( 'tribe_events_views_v2_request_uri', [ Views_V2_Filters::class, 'translate_view_request_uri' ] );

		if ( tribe()->getVar( 'ct1_fully_activated' ) ) {
			// Rewrite bases should not be encoded when using WPML.
			remove_filter( 'tribe_events_rewrite_i18n_slugs_raw', [
				tribe( Hooks::class ),
				'filter_rewrite_i18n_slugs_raw'
			], 50 );
			// Handle the translation of the Events permalinks in Views v2 and Custom Tables V1 context.
			add_filter( 'tribe_events_views_v2_view_template_vars', [ Views_V2_Filters::class, 'translate_events_permalinks' ] );
			// Filter the tracked meta keys to trigger the update of the custom tables when duplicating events.
			add_filter( 'tec_events_custom_tables_v1_tracked_meta_keys', [
				Tribe__Events__Integrations__WPML__Meta::class,
				'filter_ct1_update_meta_keys'
			] );
		}
	}

	protected function setup_cache_expiration_triggers() {
		$cache_listener = Tribe__Cache_Listener::instance();
		add_action( 'wpml_cache_clear', array( $cache_listener, 'wpml_updates' ) );
		add_action( 'wpml_activated', array( $cache_listener, 'wpml_updates' ) );
		add_action( 'wpml_deactivated', array( $cache_listener, 'wpml_updates' ) );
		add_action( 'update_option_icl_sitepress_settings', array( $cache_listener, 'wpml_updates' ) );
		add_action( 'tribe_settings_save', array( $cache_listener, 'wpml_updates' ) );
	}
}
