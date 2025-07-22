<?php
/**
 * Event Website Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Website
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Website extends Abstract_Widget {
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
	protected static string $slug = 'event_website';

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
		return esc_html__( 'Event Website', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$settings = $this->get_settings_for_display();
		$event_id = $this->get_event_id();

		$label  = $settings['link_label'] ?? null;
		$target = $settings['website_link_target'] ?? null;

		return [
			'show_website_header' => $settings['show_website_header'] ?? 'yes',
			'header_tag'          => $settings['header_tag'] ?? 'h3',
			'header_class'        => $this->get_header_class(),
			'link_class'          => $this->get_link_class(),
			'website'             => tribe_get_event_website_link( $event_id, $label, $target ),
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
		$args = $this->template_args();

		if ( empty( $args['website'] ) ) {
			$args['website'] = '<a href="http://theeventscalendar.com" target="_self" rel="external">http://theeventscalendar.com</a>';
		}

		return $args;
	}

	/**
	 * Get the class used for the website link header.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the class used for the website header.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the website header .
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_website_widget_header_class', $class, $this );
	}

	/**
	 * Get the class used for the website link wrapper.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_link_class(): string {
		$class = $this->get_widget_class() . '-link';

		/**
		 * Filters the class used for the website link wrapper.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the website link wrapper.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_website_widget_link_class', $class, $this );
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
		$this->header_options();
		$this->link_options();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		$this->header_styling();
		$this->link_styling();
	}

	/**
	 * Add controls for the header content of the event website.
	 *
	 * @since 6.4.0
	 */
	protected function header_options(): void {
		$this->start_controls_section(
			'header_section',
			[
				'label' => esc_html__( 'Header Content', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_website_header',
				'label' => esc_html__( 'Show Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_website_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event website.
	 *
	 * @since 6.4.0
	 */
	protected function link_options(): void {
		$this->start_controls_section(
			'link_section',
			[
				'label' => esc_html__( 'Link Controls', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control( 'link_target', [ 'prefix' => 'website' ] );

		$this->add_control(
			'link_label',
			[
				'label'       => esc_html__( 'Link Text', 'the-events-calendar' ),
				'description' => esc_html__( 'Alter the displayed text for the event website link.', 'the-events-calendar' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the section header.
	 *
	 * @since 6.4.0
	 */
	protected function header_styling(): void {
		$this->start_controls_section(
			'header_styles_section',
			[
				'label'     => esc_html__( 'Header Styles', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_website_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'header',
				'selector' => '{{WRAPPER}} .' . $this->get_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'header_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_header_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event website.
	 *
	 * @since 6.4.0
	 */
	protected function link_styling(): void {
		$this->start_controls_section(
			'link_styles_section',
			[
				'label' => esc_html__( 'Link Styles', 'the-events-calendar' ),
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

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'link_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_link_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event website.
	 *
	 * @since 6.4.0
	 */
	protected function link_hover_styling(): void {
		$this->start_controls_section(
			'link_hover_styles_section',
			[
				'label' => esc_html__( 'Link Hover Styles', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'link_hover',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class() . ' a:hover',
			]
		);

		$this->end_controls_section();
	}
}
