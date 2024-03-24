<?php
/**
 * Template Engine for Elementor Widgets.
 *
 * @since TBD
 *
 * @package Tribe\Events\Pro\Integrations\Plugins\Elementor\Widgets
 */
namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

use Tribe__Template as Base_Template_Engine;
use Tribe__Events__Main as TEC;

use WP_Post;

/**
 * Class Template_Engine.
 *
 * @since TBD
 *
 * @package Tribe\Events\Pro\Integrations\Plugins\Elementor\Widgets
 */
class Template_Engine extends Base_Template_Engine {
	/**
	 * Which widget instance is being used for this template engine.
	 *
	 * @since TBD
	 *
	 * @var Abstract_Widget The widget instance.
	 */
	protected Abstract_Widget $widget;

	/**
	 * Stores a potential Event ID associated with this template.
	 *
	 * @since TBD
	 *
	 * @var WP_Post|null The event post object.
	 */
	protected ?WP_Post $event = null;

	/**
	 * Template_Engine constructor, handles configuring which is the base folder, plugin and so on.
	 *
	 * @since TBD
	 */
	protected function __construct() {
		$this->set_template_origin( tribe( 'events-pro.main' ) );
		$this->set_template_folder( 'src/views/integrations/elementor' );
		$this->set_template_context_extract( true );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Factory method to create a new instance of the Template Engine.
	 *
	 * @param Abstract_Widget $widget The widget instance to set.
	 *
	 * @return Template_Engine
	 */
	public static function with_widget( Abstract_Widget $widget ): Template_Engine {
		$instance = new static();
		$instance->set_widget( $widget );

		return $instance;
	}

	/**
	 * Set the widget internally to these templates.
	 *
	 * @since TBD
	 *
	 * @param Abstract_Widget $widget The widget instance to set.
	 *
	 * @return void
	 */
	protected function set_widget( Abstract_Widget $widget ): void {
		$this->widget = $widget;
	}

	/**
	 * Allows to get the widget instance.
	 *
	 * @since TBD
	 *
	 * @return Abstract_Widget
	 */
	protected function get_widget(): Abstract_Widget {
		return $this->widget;
	}

	/**
	 * Get the event ID associated with this template.
	 *
	 * @since TBD
	 *
	 * @param int|string|WP_Post $event
	 *
	 * @return void
	 */
	public function set_event( $event ): void {
		if ( is_numeric( $event ) ) {
			$event = tribe_get_event( $event );
		}

		if ( ! $event instanceof WP_Post ) {
			return;
		}

		if ( $event->post_type !== TEC::POSTTYPE ) {
			return;
		}

		$this->event = $event;
	}

	/**
	 * Determines if the template has an event associated with it.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function has_event(): bool {
		return null !== $this->get_event();
	}

	/**
	 * Get the event associated with this template.
	 *
	 * @since TBD
	 *
	 * @return WP_Post|null
	 */
	protected function get_event(): ?WP_Post {
		return $this->event;
	}

	/**
	 * Determines if Elementor is in Edit Mode.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function is_edit_mode(): bool {
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Determines if Elementor is on Preview Mode.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function is_preview_mode(): bool {
		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}
}