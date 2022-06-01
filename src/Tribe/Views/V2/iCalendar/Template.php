<?php
/**
 * Provides a template instance specialized for iCalendar templates.
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

/**
 * Class Template
 *
 * @since  5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Template extends \Tribe__Template {

	/**
	 * Template constructor.
	 *
	 * @since 5.16.0
	 */
	public function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/views/v2' );

		// Setup to look for theme files.
		$this->set_template_folder_lookup( true );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}

	/**
	 * Returns the current template view as null to prevent fatal errors when calling the subscribe link templates in single events.
	 *
	 * @since 5.16.0
	 *
	 * @return null
	 */
	public function get_view() {
		return null;
	}
}
