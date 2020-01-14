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
	CONST SUSPEND_CACHE_KEY = 'tribe_events_suspend_view_html_cache';
	CONST SUSPEND_NONCE_KEY = 'tribe_events_views_v2_suspend_view_html_cache';

	/**
	 * Add toolbar node for suspending transients
	 *
	 * @since 5.0.0
	 *
	 * @param   WP_Admin_Bar $wp_admin_bar Instance of the Admin bar that will be rendered.
	 *
	 * @return  void
	 */
	public function suspend_view_html_cache_button( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$is_suspended = get_user_option( static::SUSPEND_CACHE_KEY, get_current_user_id() );

		$action = $is_suspended ? 'unsuspend_view_cache' : 'suspend_view_cache';
		$label  = $is_suspended ? '<span style="color: red;">' . __( 'Unsuspend View Cache', 'the-events-calendar' ) . '</span>' : __( 'Suspend View Cache', 'the-events-calendar' );

		$args = [
			'id'     => 'tribe-events-views-v2-suspen_view_html_cache',
			'title'  => $label,
			'parent' => 'top-secondary',
			'href'   => wp_nonce_url( add_query_arg( [ 'action' => $action ] ), static::SUSPEND_NONCE_KEY )
		];
		$wp_admin_bar->add_node( $args );

	}
}
