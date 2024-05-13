<?php
/**
 * Template Engine for Elementor Widgets.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use Tribe__Template as Base_Template_Engine;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Template_Engine.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Template_Engine extends Base_Template_Engine {
	/**
	 * Which widget instance is being used for this template engine.
	 *
	 * @since 6.4.0
	 *
	 * @var Abstract_Widget The widget instance.
	 */
	protected Abstract_Widget $widget;

	/**
	 * Stores a potential Event ID associated with this template.
	 *
	 * @since 6.4.0
	 *
	 * @var WP_Post|null The event post object.
	 */
	protected ?WP_Post $event = null;

	/**
	 * Template_Engine constructor, handles configuring which is the base folder, plugin and so on.
	 *
	 * @since 6.4.0
	 */
	protected function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
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
	 * @since 6.4.0
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
	 * @since 6.4.0
	 *
	 * @return Abstract_Widget
	 */
	public function get_widget(): Abstract_Widget {
		return $this->widget;
	}

	/**
	 * Get the event ID associated with this template.
	 *
	 * @since 6.4.0
	 *
	 * @param int|string|WP_Post $event The event ID, post object or slug.
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
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	public function has_event(): bool {
		return null !== $this->get_event();
	}

	/**
	 * Get the event associated with this template.
	 *
	 * @since 6.4.0
	 *
	 * @return WP_Post|null
	 */
	public function get_event(): ?WP_Post {
		return $this->event;
	}

	/**
	 * Determines if Elementor is in Edit Mode.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	public function is_edit_mode(): bool {
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Determines if Elementor is on Preview Mode.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	public function is_preview_mode(): bool {
		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}
}
