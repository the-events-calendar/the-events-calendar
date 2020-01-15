<?php
/**
 * Provides methods for HTML caching for views
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe\Events\Views\V2\View;

/**
 * Trait HTML_Cache
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Views\V2\Views
 *
 * @property Context $context The current View context.
 */
trait HTML_Cache {
	/**
	 * Return cached HTML if enabled and cache is set.
	 *
	 * @since 5.0.0
	 *
	 * @return null|string
	 */
	public function maybe_get_cached_html() {

		if ( ! $this->should_cache_html() ) {
			return;
		}

		$cache_key = $this->get_cache_html_key();

		$cached_html = tribe( 'cache' )->get_transient( $cache_key, $this->cache_html_triggers() );

		if ( ! $cached_html ) {
			return;
		}

		$cached_html = $this->inject_nonces_into_cached_html( $cached_html );

		return $cached_html;
	}

	/**
	 * If caching is enabled, set the cache.
	 *
	 * @since 5.0.0
	 *
	 * @param string $html HTML markup for view.
	 *
	 * @return boolean     If we successfully cached on the transient.
	 */
	public function maybe_cache_html( $html ) {
		/**
		 * Filter the cache TTL
		 *
		 * @since 5.0.0
		 *
		 * @param int      $cache_ttl Cache time to live.
		 * @param Context  $context   The View current context.
		 * @param View     $this      The current View instance.
		 */
		$cache_expiration = apply_filters( 'tribe_events_views_v2_cache_html_expiration', DAY_IN_SECONDS, $this->get_context(), $this );

		$cache_key = $this->get_cache_html_key();

		if ( ! $this->should_cache_html() ) {
			return false;
		}

		$html = $this->extract_nonces_before_cache( $html );

		return tribe( 'cache' )->set_transient( $cache_key, $html, $cache_expiration, $this->cache_html_triggers() );
	}

	/**
	 * Fetch the HTML cache triggers.
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function cache_html_triggers() {
		return [
			Cache_Listener::TRIGGER_SAVE_POST,
			Cache_Listener::TRIGGER_UPDATED_OPTION,
			Cache_Listener::TRIGGER_GENERATE_REWRITE_RULES,
		];
	}

	/**
	 * Determine if HTML of the current view needs to be cached.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function should_cache_html() {
		$context = $this->get_context();

		$cached_views = [
			'month' => true,
			'week' => true,
		];

		$should_cache = isset( $cached_views[ $this->get_slug() ] ) && $this->should_enable_html_cache( $context );

		/**
		 * Should the v2 view HTML be cached?
		 *
		 * @since 5.0.0
		 *
		 * @param bool    $should_cache_html Should the current view have its HTML cached?
		 * @param Context $context           The View current context.
		 * @param View    $this              The current View instance.
		 */
		return (bool) apply_filters( 'tribe_events_views_v2_should_cache_html', $should_cache, $context, $this );
	}

	/**
	 * Determine if HTML of the current view needs to be cached.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function get_cache_html_key() {
		$context = $this->get_context();
		$args    = $context->to_array();

		unset( $args['now'] );

		$cache_key = 'tribe_views_v2_cache_' . substr( sha1( wp_json_encode( $args ) ), 0, 12 ) . ':';

		/**
		 * Filter the cached html key for v2 event views
		 *
		 * @since 5.0.0
		 *
		 * @param string  $cache_html_key Cache HTML key.
		 * @param Context $context        The View current context.
		 * @param View    $this           The current View instance.
		 */
		return apply_filters( 'tribe_events_views_v2_cache_html_key', $cache_key, $context, $this );
	}

	/**
	 * Indicates if HTML cache should be enabled or not.
	 *
	 * If the HTML cache setting itself is not enabled (or not set) then this
	 * method will always return false.
	 *
	 * In other cases, the default rules are to cache everything in the 2 months past
	 * to 12 months in the future range. This policy can be refined or replaced via
	 * the 'tribe_events_enable_month_view_cache' filter hook.
	 *
	 * @since 5.0.0
	 *
	 * @param  Context $context Context object of the request.
	 *
	 * @return bool
	 */
	protected function should_enable_html_cache( $context ) {
		$event_date = $context->get( 'event_date' );

		// Respect the month view caching setting.
		if ( ! tribe_get_option( 'enable_month_view_cache', true ) ) {
			return false;
		}

		// Default to always caching the current month.
		if ( ! $event_date ) {
			return true;
		}

		// If the eventDate argument is not in the expected format then do not cache.
		if ( ! preg_match( '/^[0-9]{4}-[0-9]{1,2}$/', $event_date ) ) {
			return false;
		}

		// If the requested month is more than 2 months in the past, do not cache.
		if ( $event_date < Dates::build_date_object( '-2 months' )->format( Dates::DBYEARMONTHTIMEFORMAT ) ) {
			return false;
		}

		// If the requested month is more than 1yr in the future, do not cache.
		if ( $event_date > Dates::build_date_object( '+1 year' )->format( Dates::DBYEARMONTHTIMEFORMAT ) ) {
			return false;
		}

		// In all other cases, let's cache it!
		return true;
	}

	protected function get_view_nonce_fields() {
		$nonces = [
			'wp_rest' => 'tribe-events-views[_wpnonce]',
		];

		return apply_filters( 'tribe_events_views_v2_get_view_nonce_fields', $nonces, $this->get_context(), $this );
	}

	protected function get_view_nonce_attributes() {
		$nonces = [
			'wp_rest' => 'data-view-rest-nonce',
		];

		return apply_filters( 'tribe_events_views_v2_get_view_nonce_attributes', $nonces, $this->get_context(), $this );
	}

	protected function get_view_nonce_json_properties() {
		$nonces = [
			'wp_rest' => 'rest_nonce',
		];

		return apply_filters( 'tribe_events_views_v2_get_view_nonce_json_properties', $nonces, $this->get_context(), $this );
	}

	protected function extract_nonces_before_cache( $html ) {
		$nonce_fields = $this->get_view_nonce_fields();
		$nonce_attrs  = $this->get_view_nonce_attributes();
		$nonce_props  = $this->get_view_nonce_json_properties();

		foreach ( $nonce_fields as $action => $field ) {
			$html = preg_replace( '!(<input[^>]+name="' . preg_quote( $field ) . '"[^>]+value=")[^"]*("[^>]*>)!', '\1%%NONCE:' . $action . '%%\2', $html );
			$html = preg_replace( '!(<input[^>]+value=")[^"]*("[^>]+name="' . preg_quote( $field ) . '"[^>]*>)!', '\1%%NONCE:' . $action . '%%\2', $html );
		}

		foreach ( $nonce_attrs as $action => $attr ) {
			$html = preg_replace( '!(' . preg_quote( $attr ) . '=")[^"]*(")!', '\1%%NONCE:' . $action . '%%\2', $html );
		}

		foreach ( $nonce_props as $action => $prop ) {
			$html = preg_replace( '!("' . preg_quote( $prop ) . '":")[^"]*(")!', '\1%%NONCE:' . $action . '%%\2', $html );
		}

		return $html;
	}

	protected function inject_nonces_into_cached_html( $html ) {
		$nonce_fields = $this->get_view_nonce_fields();
		$nonce_attrs  = $this->get_view_nonce_attributes();
		$nonce_props  = $this->get_view_nonce_json_properties();

		$nonce_actions = array_merge( array_keys( $nonce_fields ), array_keys( $nonce_attrs ), array_keys( $nonce_props ) );
		$nonce_actions = array_unique( $nonce_actions );

		foreach ( $nonce_actions as $nonce_action ) {
			$nonce = $this->maybe_generate_nonce( $nonce_action );
			$html  = str_replace( "%%NONCE:{$nonce_action}", $nonce );
		}

		return $html;
	}

	protected function maybe_generate_nonce( $action ) {
		$generated_nonces = tribe_get_var( __METHOD__, [] );

		if ( ! isset( $generated_nonces[ $action ] ) ) {
			$generated_nonces[ $action ] = wp_create_nonce( $action );
			tribe_set_var( __METHOD__, $generated_nonces );
		}

		return $generated_nonces[ $action ];
	}
}