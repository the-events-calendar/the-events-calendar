<?php
/**
 * Event Description Elementor Widget.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Description
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Description extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_description';


	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Description', 'tribe-events-calendar-pro' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$id       = $this->get_event_id();
		$settings = $this->get_settings_for_display();
		$post     = get_post( $id );

		return [
			'content'  => $post instanceof \WP_Post ? $post->post_content_filtered : '',
			'event_id' => $id,
			'settings' => $settings,
		];
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
	 * Add controls for text content of the Event Description.
	 *
	 * @since TBD
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'section_title',
			[
				'label' => $this->get_title(),
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the Event Description.
	 *
	 * @since TBD
	 */
	protected function styling_options(): void {
		$this->start_controls_section(
			'styling_section_title',
			[
				'label' => $this->get_title(),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_control(
			'blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
