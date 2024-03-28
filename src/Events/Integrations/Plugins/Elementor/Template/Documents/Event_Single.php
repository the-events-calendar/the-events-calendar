<?php
/**
 * Single Event Document.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template\Documents;

use Elementor\Modules\Library\Documents\Page;

/**
 * Class Event_Single
 *
 * Represents a custom Elementor document for TEC, tailored for users with the free version of Elementor.
 * It enables the creation and management of custom templates for Single Events.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */
class Event_Single extends Page {

	/**
	 * The document title.
	 *
	 * @since TBD
	 *
	 * @return string Document title.
	 */
	public static function get_title(): string {
		return __( 'Single Event', 'the-events-calendar' );
	}

	/**
	 * The document name.
	 *
	 * @since TBD
	 *
	 * @return string Document name.
	 */
	public function get_name(): string {
		return 'tec_event_single';
	}

	/**
	 * The document type.
	 *
	 * @since TBD
	 *
	 * @return string Document type.
	 */
	public static function get_type(): string {
		return 'tec_event_single';
	}
}
