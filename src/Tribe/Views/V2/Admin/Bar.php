<?php
/**
 * Handles Admin Bar management for Views V2.
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Views\V2\Admin
 */

namespace Tribe\Events\Views\V2\Admin;

use Tribe__Utils__Array as Arr;
use WP_Admin_Bar;

/**
 * Class Bar
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Views\V2\Admin
 */
class Bar {
	/**
	 * Constant to store the Cache key.
	 *
	 * @since 5.0.0
	 *
	 * @var   string
	 */
	const SUSPEND_CACHE_KEY = 'tribe_events_suspend_view_html_cache';

	/**
	 * Constant to store the Nonce key.
	 *
	 * @since 5.0.0
	 *
	 * @var   string
	 */
	const SUSPEND_NONCE_KEY = 'tribe_events_views_v2_suspend_view_html_cache';

	/**
	 * Check if the current user has view html cache suspended.
	 *
	 * @since 5.0.0
	 *
	 * @return  boolean  If the current user has suspended the cache for views.
	 */
	public function is_view_html_cache_suspended() {
		// Check anything if you have WP_DEBUG turned on.
		if ( ! defined( 'WP_DEBUG' ) ) {
			return false;
		}

		// You can only suspend when you can `manage_options`.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Respect the view caching setting.
		if ( ! tribe_get_option( 'enable_month_view_cache', true ) ) {
			return false;
		}

		return get_user_option( static::SUSPEND_CACHE_KEY, get_current_user_id() );
	}

	/**
	 * Process the saving of of suspend cache action from admin bar.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function process_suspending_cache() {
		// Check anything if you have WP_DEBUG turned on.
		if ( ! defined( 'WP_DEBUG' ) ) {
			return;
		}

		$actions_allowed = [
			'unsuspend_view_cache' => 0,
			'suspend_view_cache'   => 1,
		];

		$action = tribe_get_request_var( 'action' );

		if ( ! isset( $actions_allowed[ $action ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( tribe_get_request_var( '_wpnonce' ), static::SUSPEND_NONCE_KEY ) ) {
			return;
		}

		update_user_option( get_current_user_id(), static::SUSPEND_CACHE_KEY, $actions_allowed[ $action ] );
		wp_safe_redirect( remove_query_arg( [ 'action', '_wpnonce' ] ) );
		exit;
	}

	/**
	 * Add toolbar node for suspending transients view html cache.
	 *
	 * @since 5.0.0
	 *
	 * @param   WP_Admin_Bar $wp_admin_bar Instance of the Admin bar that will be rendered.
	 *
	 * @return  void
	 */
	public function suspend_view_html_cache_button( WP_Admin_Bar $wp_admin_bar ) {
		// Check anything if you have WP_DEBUG turned on.
		if ( ! defined( 'WP_DEBUG' ) ) {
			return;
		}

		// Respect the view caching setting.
		if ( ! tribe_get_option( 'enable_month_view_cache', true ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$is_suspended = $this->is_view_html_cache_suspended();
		$action       = $is_suspended ? 'unsuspend_view_cache' : 'suspend_view_cache';
		$label        = $is_suspended ? '<span style="color: red;">' . __( 'Unsuspend View Cache', 'the-events-calendar' ) . '</span>' : __( 'Suspend View Cache', 'the-events-calendar' );

		$args = [
			'id'     => 'tribe-events-views-v2-suspen_view_html_cache',
			'title'  => $label,
			'parent' => 'top-secondary',
			'href'   => wp_nonce_url( add_query_arg( [ 'action' => $action, ] ), static::SUSPEND_NONCE_KEY ),
		];
		$wp_admin_bar->add_node( $args );
	}
}
