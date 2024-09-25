<?php
/**
 * Event Calendar Link Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_calendar_link
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Calendar_Link extends Abstract_Widget {
	use Traits\With_Shared_Controls;
	use Traits\Has_Preview_Data;

	/**
	 * Widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug = 'event_calendar_link';

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
		return esc_html__( 'Event Calendar Link', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		return [
			'calendar_link_class' => $this->get_link_class(),
			'calendar_link_tag'   => $this->get_calendar_link_tag(),
			'calendar_link_label' => $this->get_calendar_link_text(),
			'calendar_link'       => tribe_get_events_link(),
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
		return $this->template_args();
	}

	/**
	 * Get the HTML tag for the calendar link wrapper.
	 *
	 * @since 6.4.0
	 */
	protected function get_calendar_link_tag(): string {
		$settings = $this->get_settings_for_display();

		return $settings['html_tag'] ?? 'p';
	}

	/**
	 * Get the calendar link text.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function get_calendar_link_text(): string {
		$label = sprintf(
			/* Translators: %s: plural label for events */
			esc_html__( 'All %s', 'the-events-calendar' ),
			tribe_get_event_label_plural()
		);

		return (string) apply_filters( 'tec_events_elementor_event_calendar_link_widget_link_text', $label, $this );
	}

	/**
	 * Get the class for the calendar link.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_link_class(): string {
		return 'tec-events-back';
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
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		$this->content_styling_options();
	}

	/**
	 * Add controls for the content of the calendar link.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'calendar_link_content_section',
			[
				'label' => esc_html__( 'Event Calendar Link', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'      => 'html_tag',
				'label'   => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'default' => 'p',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add styling controls for the content of the calendar link.
	 *
	 * @since 6.4.0
	 */
	protected function content_styling_options(): void {
		$this->start_controls_section(
			'styling_section_header',
			[
				'label' => esc_html__( 'Link Styling', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'link',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class() . ' a',
			]
		);



		$this->add_control(
			'calendar_link_hover_color',
			[
				'label'     => esc_html__( 'Hover Link Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_link_class() . ' a:hover' => 'color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'align_link',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_link_class() ],
			]
		);

		$this->end_controls_section();
	}
}
