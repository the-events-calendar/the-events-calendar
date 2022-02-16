<?php
/**
 * Handles the registration of classes, implementations, and filters for Migration activities.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Dependency as Plugins;
use Tribe__Settings_Tab as Settings_Tab;

class Upgrade_Tab {
	/**
	 * The absolute path, without trailing slash, to the root directory used for the templates.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * A reference to the current migration state handler.
	 *
	 * @since TBD
	 *
	 * @var State
	 */
	private $state;
	/**
	 * A reference to the current plugin dependencies handler.
	 *
	 * @since TBD
	 *
	 * @var Plugins
	 */
	private $plugins;

	/**
	 * Upgrade_Tab constructor.
	 *
	 * since TBD
	 *
	 * @param State   $state   A reference to the current migration state handler.
	 * @param Plugins $plugins A reference to the current plugin dependencies handler.
	 */
	public function __construct( State $state, Plugins $plugins ) {
		$this->state         = $state;
		$this->plugins       = $plugins;
		$this->template_path = TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration';
	}

	/**
	 * Whether or not the upgrade tab in Event Settings should show.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	private function should_show() {
		return $this->state->is_required();
	}

	/**
	 * Get the migration phase content and inject into the admin fields.
	 *
	 * @since TBD
	 *
	 * @param array $upgrade_fields TEC Settings options.
	 *
	 * @return mixed
	 */
	public function add_phase_content( $upgrade_fields ) {
		$phase              = $this->state->get_phase();
		$migration_addendum = $this->get_migration_prompt_addendum();

		ob_start();
		include_once $this->template_path . '/upgrade-box.php';
		$phase_html = ob_get_clean();

		$upgrade_fields['recurrence_migration'] = [
			'type' => 'html',
			'html' => $phase_html,
		];

		return $upgrade_fields;
	}

	/**
	 * Gets the migration prompt trailing message based on plugin activation state.
	 *
	 * Note this code will sense around for both .org and premium plugins: it's by
	 * design and meant to keep the logic lean.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_migration_prompt_addendum() {
		// Free plugins.
		$et_active = $this->plugins->is_plugin_active( 'Tribe__Tickets__Main' );
		$ea_active = tribe( 'events-aggregator.main' )->has_license_key();
		// Premium plugins.
		$ce_active = $this->plugins->is_plugin_active( 'Tribe__Events__Community__Main' );
		$eb_active = $this->plugins->is_plugin_active( 'Tribe__Events__Tickets__Eventbrite__Main' );

		if ( $et_active && $ce_active && ( $ea_active || $eb_active ) ) {
			return __( 'Ticket sales, RSVPs, event submissions, and event imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active && ( $ea_active || $eb_active ) ) {
			return __( 'Ticket sales, RSVPs, and event imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ce_active && ( $ea_active || $eb_active ) ) {
			return __( 'Event submissions and event imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active && $ce_active ) {
			return __( 'Ticket sales, RSVPs, and event submissions will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active ) {
			return __( 'Ticket sales and RSVPs will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ce_active ) {
			return __( 'Event submissions will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ea_active || $eb_active ) {
			return __( 'Event imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		return '';
	}

	/**
	 * Renders the migration/upgrade tab, if required by the current migration state.
	 *
	 * @since TBD
	 *
	 * @return void The method will not return any value, and will have the side effect
	 *              of setting up the Migration tab.
	 */
	public function render() {
		if ( ! $this->should_show() ) {
			return '';
		}

		ob_start();
		$this->template_path . '/tab.php';
		$upgrade_tab_html = ob_get_clean();

		$upgrade_tab = [
			'info-box-description' => [
				'type' => 'html',
				'html' => $upgrade_tab_html,
			],
			'views_v2_enabled'     => [
				'type'            => 'checkbox_bool',
				'default'         => true,
				'value'           => true,
				'validation_type' => 'boolean',
				'conditional'     => true,
			],
		];

		$phase              = $this->state->get_phase();
		$migration_addendum = $this->get_migration_prompt_addendum();

		ob_start();
		$template_path = $this->template_path;
		include_once $this->template_path . '/upgrade-box.php';
		$phase_html = ob_get_clean();

		$upgrade_fields['recurrence_migration'] = [
			'type' => 'html',
			'html' => $phase_html,
		];

		new Settings_Tab(
			'upgrade', esc_html__( 'Upgrade', 'tribe-common' ),
			[
				'priority'      => 100,
				'fields'        => $upgrade_fields,
				'network_admin' => is_network_admin(),
				'show_save'     => true,
			]
		);
	}
}
