<?php
/**
 * Single Event Document.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template\Documents;

use Elementor\Modules\Library\Documents\Library_Document;

/**
 * Class Event_Single
 *
 * Represents a custom Elementor document for TEC, tailored for users to create single event templates.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */
use Elementor\Core\DocumentTypes\Post;

class Event_Single extends Library_Document {

	/**
	 * Get document properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @since  TBD
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_wp_page_templates'] = true;
		$properties['support_kit']               = true;
		$properties['show_in_finder']            = true;

		return $properties;
	}

	/**
	 * The document name.
	 *
	 * @since TBD
	 *
	 * @return string Document name.
	 */
	public function get_name(): string {
		return static::get_type();
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

	/**
	 * The document title.
	 *
	 * @since TBD
	 *
	 * @return string Document title.
	 */
	public static function get_title(): string {
		return esc_html__( 'Single Event Template', 'the-events-calendar' );
	}

	public static function get_plural_title() {
		return esc_html__( 'Single Event Templates', 'elementor' );
	}

	public static function get_add_new_title() {
		return esc_html__( 'Add New Single Event Template', 'elementor' );
	}

	/**
	 * Modify the CSS wrapper selector.
	 *
	 * @since  TBD
	 *
	 * @return string
	 */
	public function get_css_wrapper_selector() {
		return 'body.elementor-tec-events-single-' . $this->get_main_id();
	}

	/**
	 * Register the global controls for this type of document.
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	protected function register_controls() {
		parent::register_controls();

		Post::register_style_controls( $this );
	}

	/**
	 * Controls where this type of document opens the remote library.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['type']          = static::get_type();
		$config['default_route'] = 'templates/my-templates';

		return $config;
	}
}

