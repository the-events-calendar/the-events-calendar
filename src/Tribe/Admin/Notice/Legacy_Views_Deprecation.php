<?php

namespace Tribe\Events\Admin\Notice;

use Tribe__Date_Utils as Dates;

/**
 * Class Legacy_Views_Deprecation
 *
 * @since 5.5.0
 *
 */
class Legacy_Views_Deprecation {
	/**
	 * Register v1 deprecation notice.
	 *
	 * @since 5.5.0
	 */
	public function hook() {
		tribe_notice(
			'events-legacy-views-deprecation',
			[ $this, 'notice' ],
			[
				'dismiss'            => 1,
				'type'               => 'warning',
				'wrap'               => 'p',
				'recurring'          => true,
				'recurring_interval' => 'P14D',
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we are using a debug constant.
	 *
	 * @since 5.5.0
	 *
	 * @return bool
	 */
	public function is_debug() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Checks if we are in a page we need to display.
	 *
	 * @since 5.5.0
	 *
	 * @return bool
	 */
	public function is_valid_screen() {
		/** @var Tribe__Admin__Helpers $admin_helpers */
		$admin_helpers = tribe( 'admin.helpers' );

		return $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen();
	}

	/**
	 * Checks all methods required for display.
	 *
	 * @since 5.5.0
	 *
	 * @return bool
	 */
	public function should_display() {
		return $this->is_valid_screen() && ! tribe_events_views_v2_is_enabled();
	}

	/**
	 * Get the date in which we are meant to deprecate.
	 *
	 * @since 5.5.0
	 *
	 * @return Tribe\Utils\Date_I18n_Immutable
	 */
	public function get_deprecation_date() {
		return Dates::build_date_object( '2021-08-03' );
	}

	/**
	 * HTML for the notice for sites using V1.
	 *
	 * @since 5.5.0
	 *
	 * @return string
	 */
	public function notice() {
		$link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://evnt.is/1b3c' ),
			esc_html_x( 'test and prepare your site', 'Prepare deprecation of legacy views.', 'the-events-calendar' )
		);
		$link_two = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://evnt.is/v1-removal' ),
			esc_html_x( 'Read more', 'Read more about deprecation of legacy views.', 'the-events-calendar' )
		);

		return sprintf(
			_x( 'The legacy calendar views are deprecated and will be fully removed in an upcoming release. You should %1$s now to prevent issues when The Events Calendar 6.0 is released. %2$s.', 'deprecation of legacy views', 'the-events-calendar' ),
			$link,
			$link_two
		);
	}
}
