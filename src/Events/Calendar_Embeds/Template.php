<?php
/**
 * Calendar Embeds Template class.
 *
 * @since TBD
 *
 * @package TEC/Events/Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use Tribe__Template as Base_Template;
use Tribe__Events__Main as TEC_Plugin;

/**
 * Waitlist Template class.
 *
 * @since TBD
 *
 * @package TEC/Tickets_Plus/Waitlist
 */
class Template extends Base_Template {
	/**
	 * Template constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->set_template_origin( tribe( TEC_Plugin::instance() ) );
		$this->set_template_folder( 'src/views/calendar-embeds' );

		// Setup to look for theme files.
		$this->set_template_folder_lookup( true );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}
}
