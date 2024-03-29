<?php
/**
 * Single Event Pro Document.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template\Documents;

use ElementorPro\Modules\ThemeBuilder\Documents\Single_Base;

/**
 * Class Event_Single_Pro
 *
 * Represents a custom Elementor document for TEC, tailored for Elementor Pro users.
 * It integrates with Elementor's Theme Builder, offering advanced features such as
 * dynamic tags and conditional display settings for Single Event templates.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */
class Event_Single_Pro extends Single_Base {
	/**
	 * Retrieves the properties for the Event_Single_Pro document.
	 *
	 * @since TBD
	 *
	 * @return array The Event_Single_Pro document properties.
	 */
	public static function get_properties(): array {
		$properties              = parent::get_properties();
		$properties['post_type'] = \Tribe__Events__Main::POSTTYPE;

		return $properties;
	}

	/**
	 * The document icon.
	 *
	 * @since TBD
	 *
	 * @return string Document icon.
	 */
	protected static function get_site_editor_icon(): string {
		return 'eicon-calendar';
	}

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
		return 'tec_event_single_base';
	}

	/**
	 * The document type.
	 *
	 * @since TBD
	 *
	 * @return string Document type.
	 */
	public static function get_type(): string {
		return 'tec_event_single_base';
	}
}
