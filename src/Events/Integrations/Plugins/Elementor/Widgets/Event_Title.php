<?php
/**
 * Event Title Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Title
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Title extends Abstract_Widget {
	use Traits\With_Shared_Controls;
	use Traits\Has_Preview_Data;
	use Traits\Event_Query;

	/**
	 * Widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug = 'event_title';

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since 6.4.0
	 *
	 * @var bool
	 */
	protected static bool $has_styles = true;

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Title', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$event_id = $this->get_event_id();
		$title    = $event_id ? get_the_title( $event_id ) : get_the_title();

		return [
			'event_id'   => $event_id,
			'header_tag' => $this->get_event_title_header_tag(),
			'title'      => $title,
		];
	}

	/**
	 * Get the template args for the widget preview.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args for the preview.
	 */
	protected function preview_args(): array {
		$args     = $this->template_args();
		$event_id = $this->get_event_id();

		if ( tribe_is_event( $event_id ) ) {
			return $args;
		}

		$args['title'] = _x( 'Your Events Calendar Template', 'Mock Title for previewing the Event Title widget', 'the-events-calendar' );

		return $args;
	}

	/**
	 * Determine the HTML tag to use for the event title based on settings.
	 *
	 * @since 6.4.0
	 *
	 * @return string The HTML tag to use for the event title.
	 */
	protected function get_event_title_header_tag(): string {
		$settings = $this->get_settings_for_display();

		return $settings['header_tag'] ?? 'h1';
	}

	/**
	 * Register controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function register_controls(): void {
		// Content tab.
		$this->content_panel();
		// Style tab.
		$this->style_panel();
	}

	/**
	 * Add content controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function content_panel(): void {
		$this->content_options();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		// Styling options.
		$this->styling_options();
	}

	/**
	 * Add controls for text content of the event title.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'content_section',
			[
				'label' => $this->get_title(),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'      => 'header_tag',
				'label'   => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'default' => 'h1',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event title.
	 *
	 * @since 6.4.0
	 */
	protected function styling_options(): void {
		$this->start_controls_section(
			'event_title_styling_section',
			[
				'label' => $this->get_title(),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'event_title',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'event_title_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() ],
			]
		);

		$this->end_controls_section();
	}
}
