<?php

namespace Tribe\Events\Admin\Notice;

use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Admin__Notices;
use Tribe__Template;

/**
 * @internal This class may be removed or changed without notice
 */
class Update {
	/**
	 * Notice
	 *
	 * @since  6.0.0
	 * @var object
	 */
	private $notice;

	/**
	 * Stores the instance of the notice template.
	 *
	 * @since 6.0.0
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Register update notices.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->notice = tribe_notice(
			'event-update-6-0',
			[ $this, 'notice' ],
			[
				'dismiss'            => static function () {
					return ! isset( $_GET['update-message-the-events-calendar'] );
				},
				'type'               => 'warning',
				'wrap'               => false,
				'recurring'          => true,
				'recurring_interval' => 'P1M',
				'inline'             => static function () {
					return isset( $_GET['update-message-the-events-calendar'] );
				},
			],
			[ $this, 'should_display' ]
		);

		tec_asset(
			tribe( 'tec.main' ),
			'tec-update-6-0-0-notice',
			'tec-update-6.0.0-notice.js',
			[],
			'enqueue_block_editor_assets',
			[
				'in_footer'    => true,
				'localize'     => [
					'data' => $this->get_template_data(),
					'name' => 'tecBlocksEditorUpdateNoticeData',
				],
				'conditionals' => [ $this, 'should_display' ],
			]
		);
	}

	/**
	 * Should the notice be displayed?
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 */
	public function should_display() {
		$admin_helpers = tribe( 'admin.helpers' );

		if ( ! is_admin() ) {
			return false;
		}

		if ( ! did_action( 'admin_notices' ) && isset( $_GET['update-message-the-events-calendar'] ) ) {
			return false;
		}

		if ( ! $admin_helpers->is_screen() ) {
			return false;
		}

		if ( ! $admin_helpers->is_post_type_screen() ) {
			return false;
		}

		$tab = tribe_get_request_var( 'tab' );

		// Bail on upgrade tab.
		if ( $tab === 'upgrade' ) {
			return false;
		}

		if ( tribe( State::class )->is_migrated() ) {
			return false;
		}

		if ( Tribe__Admin__Notices::instance()->has_user_dismissed( $this->notice->slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function notice() {
		if ( ! $this->should_display() ) {
			return '';
		}

		return $this->get_template()->template( 'notices/update-6-0-0', $this->get_template_data(), false );
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function get_template_data() {
		$data = [
			'title'               => esc_html__( 'One more thing left to do...', 'the-events-calendar' ),
			'description'         => esc_html__( 'To complete this major calendar upgrade, you need to migrate your events to the new data storage system. Once migration finishes, you can take advantage of all the cool new 6.0 features!', 'the-events-calendar' ),
			'upgrade_link'        => 'edit.php?post_type=tribe_events&page=tec-events-settings&tab=upgrade',
			'learn_link'          => 'https://evnt.is/1b79',
			'events_plural_lower' => tribe_get_event_label_plural_lowercase(),
		];

		return $data;
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
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
