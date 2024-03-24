<?php
/**
 * Event Tags Elementor Widget.
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
 * Class Widget_Event_Tags
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Tags extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_tags';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Tags', 'tribe-events-calendar-pro' );
	}

	/**
	 * Get the label for the event tags widget.
	 *
	 * @since TBD
	 *
	 * @return string The label for the event tags widget.
	 */
	protected function get_label_text(): string {
		$label_text = sprintf(
			// Translators: %s is the singular lowercase label for an event, e.g., "event".
			__( 'This %s has tags.', 'tribe-events-calendar-pro' ),
			tribe_get_event_label_singular_lowercase()
		);

		/**
		 * Filters the label text for the event tags widget.
		 *
		 * @since TBD
		 *
		 * @param string      $label_text The label text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered label text.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_widget_label_text', $label_text, $this );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$settings   = $this->get_settings_for_display();
		$event_id   = $this->get_event_id();
		$event_tags = get_the_tags( $event_id );
		$tags       = [];

		if ( ! $event_tags || is_wp_error( $event_tags ) ) {
			return [];
		}

		foreach ( $event_tags as $tag ) {
			$tags[ $tag->name ] = get_tag_link( $tag->term_id );
		}

		return [
			'show_heading' => $settings['show_heading'] ?? 'yes',
			'heading_tag'  => $settings['heading_tag'] ?? 'h3',
			'tags'         => $tags,
			'label_text'   => $this->get_label_text(),
			'event_id'     => $event_id,
			'settings'     => $settings,
		];
	}

	/**
	 * Get the class for the event tag label.
	 *
	 * @since TBD
	 *
	 * @return string The label class.
	 */
	public function get_label_class(): string {
		$class = $this->get_widget_class() . '-label';

		/**
		 * Filters the base class for the event tags label section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The label base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_label_class', $class, $this );
	}

	/**
	 * Get the class for the event tag links section.
	 *
	 * @since TBD
	 *
	 * @return string The links class.
	 */
	public function get_links_class(): string {
		$class = $this->get_widget_class() . '-links';

		/**
		 * Filters the base class for the event tags links section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The links base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_links_class', $class, $this );
	}

	/**
	 * Get the class for a single event tag link.
	 *
	 * @since TBD
	 *
	 * @return string The link class.
	 */
	public function get_link_class(): string {
		$class = $this->get_widget_class() . '-link';

		/**
		 * Filters the base class for the event tags link section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The link base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_link_class', $class, $this );
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
		$this->heading_styling();
		$this->tags_styling();
	}

	/**
	 * Add controls for text content of the event tags.
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
			'heading_tag',
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

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the section heading.
	 *
	 * @since TBD
	 */
	protected function heading_styling() {
		$this->start_controls_section(
			'heading_styling_title',
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
					'{{WRAPPER}} .' . $this->get_label_class() => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'heading_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'heading_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_label_class(),
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
					'{{WRAPPER}} .' . $this->get_label_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the tags.
	 *
	 * @since TBD
	 */
	protected function tags_styling() {
		$this->start_controls_section(
			'tags_styling_title',
			[
				'label' => esc_html__( 'Event Tags', 'tribe-events-calendar-pro' ),
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
					'{{WRAPPER}} .' . $this->get_link_class() => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_link_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class(),
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
					'{{WRAPPER}} .' . $this->get_link_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
