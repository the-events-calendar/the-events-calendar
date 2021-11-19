<?php
/**
 * Provides a template instance specialized for Event Status templates.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */

namespace Tribe\Events\Event_Status;

use Tribe__Events__Main as Events_Plugin;

/**
 * Class Template
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Template extends \Tribe__Template {

	/**
	 * Template constructor.
	 *
	 * @since 5.11.0
	 */
	public function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/views/v2/event-status' );

		// Setup to look for theme files.
		$this->set_template_folder_lookup( true );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}
}
