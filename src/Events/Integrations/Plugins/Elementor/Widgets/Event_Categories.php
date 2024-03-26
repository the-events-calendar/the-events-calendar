<?php
/**
 * Event Categories Elementor Widget.
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
use Tribe__Events__Main;

/**
 * Class Widget_Event_Categories
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Categories extends Abstract_Widget {
	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_categories';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return __( 'Event Categories', 'tribe-events-calendar-pro' );
	}

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_label_text(): string {
		return _x( 'Event Categories:', 'The label/header text for the event categories widget', 'tribe-events-calendar-pro' );
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
		$settings = $this->get_settings_for_display();
		$tec_main = Tribe__Events__Main::instance();

		return [
			'show_heading' => tribe_is_truthy( $settings['show_categories_heading'] ?? true ),
			'heading_tag'  => $settings['categories_heading_tag'] ?? 'h3',
			'categories'   => get_the_terms( $event_id, $tec_main->get_event_taxonomy() ),
			'settings'     => $settings,
			'event_id'     => $event_id,
		];
	}

	/**
	 * Renders the categories list for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The HTML for the categories list. Empty string if no categories are found.
	 */
	public function do_categories(): string {
		$event_id   = $this->get_event_id();
		$categories = tribe_get_event_taxonomy(
			$event_id,
			[
				'before' => '',
				'sep'    => ', ',
				'after'  => '',
			]
		);

		if ( empty( $categories ) ) {
			return '';
		}

		$html = $categories;

		/**
		 * Applies filters from the tribe_get_event_categories() function,
		 * as this is essentially a stripped-down version of said function.
		 *
		 * @since TBD
		 *
		 * @param string $html       The HTML output for the event categories.
		 * @param int    $event_id   The event ID.
		 * @param array  $categories The HTML output for the event categories. Identical to $html.
		 *                           Included to match the original filter signature.
		 */
		$html = apply_filters( 'tribe_get_event_categories', $html, $event_id, $categories );

		/**
		 * Allows filtering of the HTML output for the event categories widget.
		 *
		 * @since TBD
		 *
		 * @param string $html       The HTML output for the event categories.
		 * @param int    $event_id   The event ID.
		 */
		$html = apply_filters( 'tec_events_elementor_event_categories_widget_event_categories_html', $html, $event_id );

		return $html;
	}

	/**
	 * Get the class used for the category label.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_label_class() {
		$class = $this->get_widget_class() . '-label';

		/**
		 * Filters the class used for the category label.
		 *
		 * @since TBD
		 *
		 * @param string          $class The class used for the category label.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_category_widget_label_class', $class, $this );
	}

	/**
	 * Get the class used for the category list.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_wrapper_class() {
		$class = $this->get_widget_class() . '-link-wrapper';

		/**
		 * Filters the class used for the category list wrapper.
		 *
		 * @since TBD
		 *
		 * @param string          $class The class used for the category list wrapper.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_category_widget_link_wrapper_class', $class, $this );
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
		$this->categories_styling();
	}

	/**
	 * Add controls for text content of the event categories.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Event Categories', 'tribe-events-calendar-pro' ),
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
			'show_categories_heading',
			[
				'label'     => esc_html__( 'Show Heading', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'Hide', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'categories_heading_tag',
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
					'show_categories_heading' => 'yes',
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
			'heading_section_title',
			[
				'label'     => esc_html__( 'Section Heading', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_categories_heading' => 'yes',
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
				'selector' => '{{WRAPPER}} .' . $this->get_wrapper_class(),
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
	 * Add controls for text styling of the event categories.
	 *
	 * @since TBD
	 */
	protected function categories_styling() {
		$this->start_controls_section(
			'categories_section_title',
			[
				'label' => esc_html__( 'Event Categories', 'tribe-events-calendar-pro' ),
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
					'{{WRAPPER}} .' . $this->get_wrapper_class() . ',{{WRAPPER}} .' . $this->get_wrapper_class() . ' a' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_wrapper_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_wrapper_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_wrapper_class(),
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
					'{{WRAPPER}} .' . $this->get_wrapper_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
