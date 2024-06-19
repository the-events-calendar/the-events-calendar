<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use Tribe__Template;
use Tribe__Events__Main as TEC;

/**
 * Class Template
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 */
class Template extends Tribe__Template {
	/**
	 * Building of the Class template configuration.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->set_template_origin( TEC::instance() );
		$this->set_template_folder( 'src/Events/Custom_Tables/V1/admin-views' );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );

		// Admin Templates are not theme available.
		$this->set_template_folder_lookup( false );
	}
}
