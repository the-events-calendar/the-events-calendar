<?php
/**
 * Widget Admin Templates
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class Admin_Template
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Admin_Template extends \Tribe__Template {

	/**
	 * Template constructor.
	 *
	 * @since TBD
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
