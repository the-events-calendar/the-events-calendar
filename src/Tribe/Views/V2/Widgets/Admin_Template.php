<?php
/**
 * Widget Admin Template - handles the presentation on the widgets in the admin.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class Admin_Template
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Admin_Template extends \Tribe__Template {
	/**
	 * Template constructor.
	 *
	 * Sets the correct paths for templates in this plugin (as opposed to The Events Calendar).
	 *
	 * @since 5.3.0
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
