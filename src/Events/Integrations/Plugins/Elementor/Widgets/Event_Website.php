<?php
/**
 * Event Website Elementor Widget.
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
 * Class Widget_Event_Website
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Website extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_website';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Website', 'tribe-events-calendar-pro' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$settings = $this->get_settings_for_display();
		$event_id = $this->get_event_id();

		// Only add filters if they are needed.
		if ( $settings['link_target'] ) {
			$this->set_template_filter(
				'tribe_get_event_website_link_target',
				[ $this, 'modify_link_target' ],
				10,
				3
			);
		}

		if ( $settings['link_label'] ) {
			$this->set_template_filter(
				'tribe_get_event_website_link_label',
				[ $this, 'modify_link_label' ],
				10,
				2
			);
		}

		$website = tribe_get_event_website_link( $event_id );

		return [
			'align'        => $settings['align'] ?? '',
			'show_heading' => $settings['show_heading'] ?? 'yes',
			'header_tag'   => $settings['header_tag'] ?? 'h3',
			'event_id'     => $event_id,
			'label_class'  => $this->get_label_class(),
			'link_class'   => $this->get_link_class(),
			'website'      => $website,
		];
	}

	/**
	 * Modify the target for the event website link.
	 *
	 * @since TBD
	 *
	 * @param string          $link_target The target attribute string. Defaults to "_self".
	 * @param string          $unused_url  The link URL.
	 * @param null|object|int $post_id     The event the url is attached to.
	 *
	 * @return string The modified target attribute string.
	 */
	public function modify_link_target( $link_target, $unused_url, $post_id ): string {
		$event_id = $this->get_event_id();
		// Not the same event, bail.
		if ( $event_id !== $post_id ) {
			return $link_target;
		}

		$settings        = $this->get_settings_for_display();
		$target_override = $settings['link_target'];

		if ( ! $target_override ) {
			return $link_target;
		}

		return $target_override;
	}

	/**
	 * Modify the label for the event website link.
	 *
	 * @since TBD
	 *
	 * @param string $label   The link label.
	 * @param int    $post_id The event ID.
	 */
	public function modify_link_label( $label, $post_id ) {
		$event_id = $this->get_event_id();
		// Not the same event, bail.
		if ( $event_id !== $post_id ) {
			return $label;
		}

		$settings = $this->get_settings_for_display();
		$text     = $settings['link_label'];

		if ( ! $text ) {
			return $label;
		}

		return $text;
	}

	/**
	 * Get the class used for the website link wrapper.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_link_class() {
		$class = $this->get_widget_class() . '-link';

		/**
		 * Filters the class used for the website link wrapper.
		 *
		 * @since TBD
		 *
		 * @param string          $class The class used for the website link wrapper.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_website_widget_link_class', $class, $this );
	}

	/**
	 * Get the class used for the website link label.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_label_class() {
		$class = $this->get_widget_class() . '-label';

		/**
		 * Filters the class used for the website link label.
		 *
		 * @since TBD
		 *
		 * @param string          $class The class used for the website link label.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_website_widget_label_class', $class, $this );
	}

	/**
	 * Register controls for the widget.
	 *
	 * @since TBD
	 */
	protected function register_controls() {
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
	protected function content_panel() {
		$this->content_options();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel() {
		$this->link_styling();

		$this->heading_styling();
	}

	/**
	 * Add controls for text content of the event website.
	 *
	 * @since TBD
	 */
	protected function content_options() {
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

		$this->add_control(
			'show_heading',
			[
				'label'     => esc_html__( 'Show Heading', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'Hide', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_heading' => 'yes',
				],
			]
		);

		$this->add_control(
			'link_target',
			[
				'label'       => esc_html__( 'Link Target', 'tribe-events-calendar-pro' ),
				'description' => esc_html__( 'Choose whether to open the event website link in the same window or a new window.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '_self',
				'options'     => [
					'_self'  => 'same window',
					'_blank' => 'new window',
				],
			]
		);

		$this->add_control(
			'link_label',
			[
				'label'       => esc_html__( 'Link Text', 'tribe-events-calendar-pro' ),
				'description' => esc_html__( 'Alter the displayed text for the event website link.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the section heading.
	 *
	 * @since TBD
	 */
	protected function heading_styling() {
		$this->start_controls_section(
			'heading_section_title',
			[
				'label'     => esc_html__( 'Section Heading', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_heading' => 'yes',
				],
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'heading_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . '-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'heading_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_control(
			'heading_blend_mode',
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

	/**
	 * Add controls for text styling of the event website.
	 *
	 * @since TBD
	 */
	protected function link_styling() {
		$this->start_controls_section(
			'website_section_title',
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
					'{{WRAPPER}} .' . $this->get_widget_class() . '-link a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . '-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . '-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . '-link a',
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
					'{{WRAPPER}} .' . $this->get_widget_class() . '-link a' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
