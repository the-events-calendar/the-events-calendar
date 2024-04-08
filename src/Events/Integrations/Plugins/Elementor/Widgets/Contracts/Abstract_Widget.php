<?php
/**
 * List View Elementor Widget.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts;

use TEC\Events\Integrations\Plugins\Elementor\Assets_Manager;
use TEC\Common\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget as Common_Abstract_Widget;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
use Tribe__Events__Main as TEC;

/**
 * Abstract Widget class
 *
 * All template widgets should extend this class.
 */
abstract class Abstract_Widget extends Common_Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug;

	/**
	 * Widget slug prefix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug_prefix = 'tec_events_elementor_widget_';

	/**
	 * Widget asset prefix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $asset_prefix = 'tec-events-elementor-widget-';

	/**
	 * Widget template prefix.
	 *
	 * This holds the base path to the widget templates.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $template_prefix = 'events/integrations/elementor/widgets';

	/**
	 * Template engine class.
	 *
	 * @since TBD
	 *
	 * @var string The template engine class to use.
	 */
	protected string $template_engine_class = Template_Engine::class;

	/**
	 * Get local widget slug.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return static::$slug;
	}

	/**
	 * Get the post type associated with the widget.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_widget_post_type(): string {
		return TEC::POSTTYPE;
	}

	/**
	 * Get the event ID.
	 *
	 * @since TBD
	 *
	 * @return int|null
	 */
	public function get_event_id() {
		return $this->get_post_id();
	}

	/**
	 * Determines if the widget has a valid event ID associated with it.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function has_event_id(): bool {
		return $this->get_event_id() !== null;
	}

	/**
	 * Get the asset source for the widget.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function get_asset_source() {
		return tribe( 'tec.main' );
	}

	/**
	 * Register the styles for the widget.
	 *
	 * @since TBD
	 */
	public function register_style(): void {
		static::$group_key = Assets_Manager::$group_key;

		parent::register_style();
	}
}
