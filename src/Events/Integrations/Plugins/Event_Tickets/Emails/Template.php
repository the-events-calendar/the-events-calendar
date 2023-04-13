<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use \Tribe__Template;
use Tribe__Events__Main as TEC;

/**
 * Class Template
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Template extends Tribe__Template {
	/**
	 * Building of the Class template configuration.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->set_template_origin( TEC::instance() );
		$this->set_template_folder( 'src/views/v2/emails' );

		// Setup to look for theme files.
		$this->set_template_folder_lookup( true );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}
}
