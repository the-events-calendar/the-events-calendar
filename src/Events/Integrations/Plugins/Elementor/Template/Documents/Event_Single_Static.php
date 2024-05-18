<?php
/**
 * Single Event Document.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template\Documents;

use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\Core\DocumentTypes\Post;

/**
 * Class Event_Single_Static
 *
 * Represents a custom Elementor document for TEC, tailored for users to create single event templates.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Documents
 */
class Event_Single_Static extends Library_Document {

	/**
	 * Get document properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 *
	 * @return string Document name.
	 */
	public function get_name(): string {
		return static::get_type();
	}

	/**
	 * The document type.
	 *
	 * @since 6.4.0
	 *
	 * @return string Document type.
	 */
	public static function get_type(): string {
		return 'tec_event_single_static';
	}

	/**
	 * The document title.
	 *
	 * @since 6.4.0
	 *
	 * @return string Document title.
	 */
	public static function get_title(): string {
		return esc_html__( 'Single Event Template', 'the-events-calendar' );
	}

	/**
	 * The document plural title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_plural_title() {
		return esc_html__( 'Single Event Templates', 'elementor' );
	}

	/**
	 * The document add new title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_add_new_title() {
		return esc_html__( 'Add New Single Event Template', 'elementor' );
	}

	/**
	 * Modify the CSS wrapper selector.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_css_wrapper_selector() {
		return 'body.elementor-tec-events-single-' . $this->get_main_id();
	}

	/**
	 * Register the global controls for this type of document.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 *
	 * @return array
	 */
	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['type']          = static::get_type();
		$config['default_route'] = 'templates/my-templates';

		return $config;
	}

	/**
	 * Modify the template data before importing.
	 *
	 * @since 6.4.0
	 *
	 * @param array $data The template data.
	 *
	 * @return array
	 */
	public static function prepare_template_data( $data ): array {
		$widgets = $data['content'][0]['elements'][0]['elements'];

		// Remove the theme-post-content from widget.
		$widgets = wp_filter_object_list( $widgets, [ 'widgetType' => 'theme-post-content' ], 'NOT' );

		$data['content'][0]['elements'][0]['elements'] = $widgets;

		return $data;
	}
}
