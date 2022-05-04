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
	 * Upgrade_Tab constructor.
	 *
	 * since TBD
	 *
	 * @param State   $state   A reference to the current migration state handler.
	 */
	public function __construct( State $state ) {
		$this->state         = $state;
		$this->template_path = TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration';
	}

	/**
	 * Whether or not the upgrade tab in Event Settings should show.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the upgrade tab should show or not.
	 */
	public function should_show() {
		return $this->state->is_required()
		       || $this->state->is_running()
		       || $this->state->is_completed();
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
		$phase_html = $this->get_phase_html();

		$upgrade_fields['ct1_migration'] = [
			'type' => 'html',
			'html' => $phase_html,
		];

		return $upgrade_fields;
	}



	/**
	 * Renders and returns the current phase HTML code.
	 *
	 * @since TBD
	 *
	 * @return string The current phase HTML code.
	 */
	public function get_phase_html() {
		$phase              = $this->state->get_phase();
		$template_path      = $this->template_path;

		ob_start();
		include_once $this->template_path . '/upgrade-box.php';
		$phase_html = ob_get_clean();

		return (string)$phase_html;
	}
}
