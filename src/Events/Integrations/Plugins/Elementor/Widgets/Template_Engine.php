<?php
/**
 * Template Engine for Elementor Widgets.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use TEC\Common\Integrations\Plugins\Elementor\Widgets\Template_Engine as Template_Engine_Contract;
use WP_Post;

/**
 * Class Template_Engine.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Template_Engine extends Template_Engine_Contract {
	/**
	 * Which widget instance is being used for this template engine.
	 *
	 * @since TBD
	 *
	 * @var Abstract_Widget
	 */
	protected $widget;

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
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/views/integrations/elementor' );
		$this->set_template_context_extract( true );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Allows to get the widget instance.
	 *
	 * @since TBD
	 *
	 * @return Abstract_Widget
	 */
	public function get_widget(): Abstract_Widget {
		return $this->widget;
	}

	/**
	 * Get the event ID associated with this template.
	 *
	 * @since TBD
	 *
	 * @param int|string|WP_Post $event The event ID, post object or slug.
	 *
	 * @return void
	 */
	public function set_event( $event ): void {
		parent::set_post( $event );
	}

	/**
	 * Determines if the template has an event associated with it.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function has_event(): bool {
		return null !== $this->get_event()
			&& $this->get_event()->post_type === $this->get_widget()::get_widget_post_type();
	}

	/**
	 * Get the event associated with this template.
	 *
	 * @since TBD
	 *
	 * @return WP_Post|null
	 */
	public function get_event(): ?WP_Post {
		return parent::get_post();
	}

	/**
	 * Determines if Elementor is in Edit Mode.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_edit_mode(): bool {
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Determines if Elementor is on Preview Mode.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_preview_mode(): bool {
		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}
}
