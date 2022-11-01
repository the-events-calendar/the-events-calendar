<?php

namespace Tribe\Events\Admin\Notice;

use Tribe\Events\Views\V2\Manager;
use Tribe__Date_Utils as Dates;

/**
 * Class Legacy_Views_Updated.
 *
 * @since   6.0.0
 *
 * @package Tribe\Events\Admin\Notice
 */
class Legacy_Views_Updated {

	/**
	 * Stores the instance of the notice template.
	 *
	 * @since 6.0.0
	 *
	 * @var \Tribe__Template
	 */
	protected $template;

	/**
	 * Register legacy views updated notice.
	 *
	 * @since 6.0.0
	 */
	public function hook(): void {
		tribe_notice(
			'events-legacy-views-updated',
			[ $this, 'notice' ],
			[
				'dismiss' => 1,
				'type'    => 'warning',
				'inline'  => static function () {
					return isset( $_GET['update-message-the-events-calendar'] );
				},
				'wrap'    => false,
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we are in a page we need to display.
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 */
	public function is_valid_screen(): bool {
		/** @var \Tribe__Admin__Helpers $admin_helpers */
		$admin_helpers = tribe( 'admin.helpers' );

		return $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen();
	}

	/**
	 * Checks all methods required for display.
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 */
	public function should_display( $notice = null ): bool {
		if ( ! is_admin() ) {
			return false;
		}

		if ( ! did_action( 'admin_notices' ) && isset( $_GET['update-message-the-events-calendar'] ) ) {
			return false;
		}

		if ( ! $this->is_valid_screen() ) {
			return false;
		}

		return tribe_installed_before( \Tribe__Events__Main::class, '6.0.0-dev' ) && $this->has_views_v2_negative_value();
	}

	/**
	 * Determines that we have a negative value stored, which means this installation was forced into V2.
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 */
	protected function has_views_v2_negative_value(): bool {
		$enabled = tribe_get_option( Manager::$option_enabled, '__doesnt_exist__' );

		return '__doesnt_exist__' === $enabled || false === $enabled || 0 === $enabled || '0' === $enabled;
	}

	/**
	 * Get template object.
	 *
	 * @since 6.0.0
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * HTML for the notice for sites using V1.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function notice(): string {
		if ( ! $this->should_display() ) {
			return '';
		}

		return $this->get_template()->template( 'notices/legacy-views-updated', [], false );
	}
}