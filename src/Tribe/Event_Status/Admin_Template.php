<?php
/**
 * Event Status Admin Template - handles the presentation of event status in the admin.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Event_Status;

/**
 * Class Admin_Template
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Admin_Template extends \Tribe__Template {

	/**
	 * Template constructor.
	 *
	 * Sets the correct paths for templates for event status.
	 *
	 * @since 5.11.0
	 */
	public function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/admin-views' );

		// We specifically don't want to look up template files here.
		$this->set_template_folder_lookup( false );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}
}
