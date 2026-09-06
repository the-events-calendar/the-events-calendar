<?php
/**
 * Reports Pro availability without treating a license or historical data as activation.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use TEC\Events\Recurrence\Authoring_Guard;
use TEC\Events\Recurrence\Controller;
use TEC\Events\Recurrence\Pro_History;

/** Read-only availability and permitted recovery action. @since TBD */
class Pro_Status {

	/**
	 * Builds the current status; activation always goes through WordPress itself.
	 *
	 * @since TBD
	 * @param bool $has_rules Whether the current screen contains rule-based schedules.
	 * @return array<string,mixed> Status, explanation and optional action.
	 */
	public function get( bool $has_rules = false ): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$file = '';
		$data = [];
		foreach ( get_plugins() as $candidate => $plugin ) {
			if ( 'events-calendar-pro.php' === basename( $candidate ) ) {
				$file = $candidate;
				$data = $plugin;
				break;
			}
		}
		$loaded          = class_exists( 'Tribe__Events__Pro__Main', false );
		$active          = $loaded || ( '' !== $file && is_plugin_active( $file ) );
		$compatible      = '' === $file || ( version_compare( $data['Version'], Controller::MINIMUM_PRO_VERSION, '>=' )
			&& ! is_wp_error( validate_plugin_requirements( $file ) ) );
		$available       = $active && $compatible && tribe( Authoring_Guard::class )->has_external_updates();
		$state           = $available ? 'active' : ( ! $compatible ? 'incompatible' : ( $active ? 'unavailable' : ( '' !== $file ? 'inactive' : 'missing' ) ) );
		$history         = $has_rules || (bool) get_option( Pro_History::MEMO_OPTION, false ) || (bool) get_option( Pro_History::SERIES_SCHEMA_OPTION, false );
		$result          = [
			'state'   => $state,
			'show'    => ! $available && ( '' !== $file || $active || $history ),
			'title'   => '',
			'message' => __( 'Existing scheduled dates are preserved. Reactivate Pro to edit recurrence rules and generate further dates.', 'the-events-calendar' ),
			'label'   => '',
			'url'     => '',
		];
		$titles          = [
			'active'       => __( 'Events Calendar Pro is active', 'the-events-calendar' ),
			'inactive'     => __( 'Events Calendar Pro is inactive', 'the-events-calendar' ),
			'missing'      => __( 'Events Calendar Pro is not installed', 'the-events-calendar' ),
			'incompatible' => __( 'Events Calendar Pro needs an update', 'the-events-calendar' ),
			'unavailable'  => __( 'Events Calendar Pro recurrence is unavailable', 'the-events-calendar' ),
		];
		$result['title'] = $titles[ $state ];
		if ( 'missing' === $state ) {
			$result['message'] = __( 'Existing scheduled dates are preserved. Install and activate Events Calendar Pro to edit recurrence rules and generate further dates.', 'the-events-calendar' );
		} elseif ( in_array( $state, [ 'incompatible', 'unavailable' ], true ) ) {
			$result['message'] = __( 'Existing scheduled dates are preserved. Check Events Calendar Pro and its requirements in Plugins to restore recurrence editing and date generation.', 'the-events-calendar' );
		}
		if ( 'inactive' === $state && current_user_can( 'activate_plugin', $file ) ) {
			$result['label'] = __( 'Reactivate Events Calendar Pro', 'the-events-calendar' );
			$result['url']   = wp_specialchars_decode(
				wp_nonce_url(
					add_query_arg(
						[
							'action' => 'activate',
							'plugin' => $file,
						],
						self_admin_url( 'plugins.php' ) 
					),
					'activate-plugin_' . $file
				),
				ENT_QUOTES 
			);
		} elseif ( 'missing' === $state && current_user_can( 'install_plugins' ) ) {
			$result['label'] = __( 'Install Events Calendar Pro', 'the-events-calendar' );
			$result['url']   = self_admin_url( 'plugin-install.php?tab=upload' );
		} elseif ( current_user_can( 'activate_plugins' ) ) {
			$result['label'] = __( 'Manage plugins', 'the-events-calendar' );
			$result['url']   = self_admin_url( 'plugins.php' );
		} elseif ( $result['show'] ) {
			$result['message'] .= ' ' . __( 'Ask your site administrator to restore Events Calendar Pro.', 'the-events-calendar' );
		}
		return $result;
	}
}
