<?php
/**
 * Event Image Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Image
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Image extends Abstract_Widget {
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
	protected static string $slug = 'event_image';

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
		return esc_html__( 'Event Image', 'the-events-calendar' );
	}

	/**
	 * @inheritdoc
	 */
	protected function template_args(): array {
		$event_id = $this->get_event_id();
		$settings = $this->get_settings_for_display();
		$atts     = [ 'class' => 'elementor-image' ];

		if ( ! empty( $settings['tec_event_hover_animation'] ) ) {
			$atts['class'] .= ' elementor-animation-' . $settings['tec_event_hover_animation'];
		}

		$image_size = $settings['event_image_size'] ?? 'large';

		$size = $image_size === 'custom' ? $settings['event_image_custom_dimension'] : $image_size;

		if ( empty( $event_id ) ) {
			return [];
		}

		$image = wp_get_attachment_image(
			get_post_thumbnail_id( $event_id ),
			$size,
			false,
			$atts
		);

		return [
			'image'    => $image,
			'event_id' => $event_id,
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
		$id   = $this->get_event_id();
		$args = $this->template_args();

		if ( tribe_is_event( $id ) ) {
			return $args;
		}

		if ( ! empty( $args['image'] ) ) {
			return $args;
		}

		return [
			'image' => '<img src="' . tribe_resource_url( 'images/placeholder.png' ) . '" class="elementor-image" />',
		];
	}

	/**
	 * Renders the image widget for the editor live preview.
	 *
	 * @since 6.4.0
	 */
	protected function content_template(): void {
		$event_id = $this->get_event_id();

		if ( empty( $event_id ) ) {
			return;
		}
		?>
		<#
		var image = {
			id: <?php echo absint( get_post_thumbnail_id( $event_id ) ); ?>,
			url: "<?php echo esc_url( get_the_post_thumbnail_url( $event_id, 'full' ) ); ?>",
			size: settings.event_image_size,
			dimension: settings.event_image_custom_dimension,
			model: view.getEditModel()
		};
		var image_url = elementor.imagesManager.getImageUrl( image );
		#>
		<div <?php tec_classes( $this->get_element_classes() ); ?>>
			<img src="{{ image_url }}" />
		</div>
		<?php
	}

	/**
	 * Register controls for the widget.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 */
	protected function content_panel() {
		$this->content_options();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel() {
		// Styling options.
		$this->styling_options();
	}

	/**
	 * Add controls for text content of the event image.
	 *
	 * @since 6.4.0
	 */
	protected function content_options() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => $this->get_title(),
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'event_image',
				'default'   => 'large',
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'event_image_align',
			[
				'label'     => esc_html__( 'Alignment', 'the-events-calendar' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() => 'text-align: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event image.
	 *
	 * @since 6.4.0
	 */
	protected function styling_options() {
		$this->start_controls_section(
			'styling_section',
			[
				'label' => $this->get_title(),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'          => esc_html__( 'Width', 'the-events-calendar' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'          => esc_html__( 'Max Width', 'the-events-calendar' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Height', 'the-events-calendar' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'object-fit',
			[
				'label'     => esc_html__( 'Object Fit', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options'   => [
					''        => esc_html__( 'Default', 'the-events-calendar' ),
					'fill'    => esc_html__( 'Fill', 'the-events-calendar' ),
					'cover'   => esc_html__( 'Cover', 'the-events-calendar' ),
					'contain' => esc_html__( 'Contain', 'the-events-calendar' ),
				],
				'default'   => '',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'object-fit: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'object-position',
			[
				'label'     => esc_html__( 'Object Position', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'center center' => esc_html__( 'Center Center', 'the-events-calendar' ),
					'center left'   => esc_html__( 'Center Left', 'the-events-calendar' ),
					'center right'  => esc_html__( 'Center Right', 'the-events-calendar' ),
					'top center'    => esc_html__( 'Top Center', 'the-events-calendar' ),
					'top left'      => esc_html__( 'Top Left', 'the-events-calendar' ),
					'top right'     => esc_html__( 'Top Right', 'the-events-calendar' ),
					'bottom center' => esc_html__( 'Bottom Center', 'the-events-calendar' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'the-events-calendar' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'the-events-calendar' ),
				],
				'default'   => 'center center',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'object-position: {{VALUE}};' ],
				'condition' => [ 'object-fit' => 'cover' ],
			]
		);

		$this->add_control(
			'separator_panel_style',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'image_effects' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => esc_html__( 'Normal', 'the-events-calendar' ),
			]
		);

		$this->add_control(
			'opacity',
			[
				'label'     => esc_html__( 'Opacity', 'the-events-calendar' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'opacity: {{SIZE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . ' img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => esc_html__( 'Hover', 'the-events-calendar' ),
			]
		);

		$this->add_control(
			'opacity_hover',
			[
				'label'     => esc_html__( 'Opacity', 'the-events-calendar' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [ '{{WRAPPER}}:hover .' . $this->get_widget_class() . ' img' => 'opacity: {{SIZE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}}:hover .' . $this->get_widget_class() . ' img',
			]
		);

		$this->add_control(
			'background_hover_transition',
			[
				'label'     => esc_html__( 'Transition Duration', 'the-events-calendar' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'transition-duration: {{SIZE}}s' ],
			]
		);

		$this->add_control(
			'tec_event_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'the-events-calendar' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .' . $this->get_widget_class() . ' img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'the-events-calendar' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .' . $this->get_widget_class() . ' img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class() . ' img',
			]
		);

		$this->end_controls_section();
	}
}
