<?php
/**
 * Event Title Elementor Widget.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Title
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Title extends Abstract_Widget {
	use Traits\With_Shared_Controls;

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_title';

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static bool $has_styles = true;

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Title', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return array The template args for the preview.
	 */
	protected function preview_args(): array {
		return [
			'header_tag' => $this->get_event_title_header_tag(),
			'title'      => $this->title(),
		];
	}

	/**
	 * Determine the HTML tag to use for the event title based on settings.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 */
	protected function content_panel(): void {
		$this->content_options();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel(): void {
		// Styling options.
		$this->styling_options();
	}

	/**
	 * Add controls for text content of the event title.
	 *
	 * @since TBD
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
	 * @since TBD
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
