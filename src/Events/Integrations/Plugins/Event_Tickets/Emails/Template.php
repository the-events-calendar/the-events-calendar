<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use Tribe__Events__Main as TEC;
use Tribe__Template;

/**
 * Class Template
 *
 * @since 6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */
class Template extends Tribe__Template {
	/**
	 * Building of the Class template configuration.
	 *
	 * @since 6.1.1
	 */
	public function __construct() {
		$this->set_template_origin( TEC::instance() );
		$this->set_template_folder( 'src/views/integrations/event-tickets/emails' );

		// Setup to look for theme files.
		$this->set_template_folder_lookup( true );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}
}
