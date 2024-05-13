<?php
/**
 * The Preview Data Trait for TEC Elementor Widgets.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Traits;

/**
 * Preview_Data Trait
 *
 * @since 6.4.0
 *
 * @package TEC\Event_Automator\Integration\REST\V1\Interfaces
 */
trait Has_Preview_Data {
	/**
	 * Determine if the widget should show mock data.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	public function should_show_mock_data(): bool {
		$template = $this->get_template();
		$type     = get_post_type();

		if ( $type === 'elementor_library' ) {
			return true;
		}

		$post_id = $this->get_event_id();

		return $template->is_preview_mode() && empty( $post_id );
	}

	/**
	 * Get the template args for the widget preview.
	 * Must be implemented by each widget that uses this trait!
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args for the preview.
	 */
	abstract protected function preview_args(): array;

	/**
	 * Get the template arguments.
	 *
	 * This calls the template_args method on the widget and then filters the data.
	 *
	 * @since 6.4.0
	 *
	 * @return array
	 */
	public function get_template_args(): array {
		$preview = $this->should_show_mock_data();
		$args    = $preview ? $this->preview_args() : $this->template_args(); // Defined in each widget instance.
		$slug    = self::get_slug();


		/**
		 * Filters the template data for all Elementor widget templates.
		 *
		 * @param array<string,mixed> $args   The template data.
		 * @param bool                $preview Whether the template is in preview mode.
		 * @param object              $widget The widget object.
		 *
		 * @return array
		 */
		$args = (array) apply_filters( 'tec_events_elementor_widget_template_data', $args, $preview, $this );

		/**
		 * Filters the template data for a specific (by $slug) Elementor widget templates.
		 *
		 * @param array<string,mixed> $args   The template data.
		 * @param bool                $preview Whether the template is in preview mode.
		 * @param object              $widget The widget object.
		 *
		 * @return array
		 */
		$args = (array) apply_filters( "tec_events_elementor_widget_{$slug}_template_data", $args, $preview, $this );

		// Add the widget to the data array.
		$args['widget'] = $this;

		return $args;
	}
}
