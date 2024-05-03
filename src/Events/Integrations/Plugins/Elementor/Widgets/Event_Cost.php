<?php
/**
 * Event Cost Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Cost
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Cost extends Abstract_Widget {
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
	protected static string $slug = 'event_cost';

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Cost', 'the-events-calendar' );
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

		return [
			'show_header' => tribe_is_truthy( $settings['show_header'] ?? false ),
			'header_tag'  => $this->get_header_tag(),
			'html_tag'    => $this->get_html_tag(),
			'event_id'    => $event_id,
			'cost'        => tribe_get_formatted_cost( $event_id ),
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

		if ( ! empty( $id )
			&& ( ! empty( $args['categories'] ) || tribe_is_event( $id ) )
		) {
			return $args;
		}

		$args['cost'] = '$10';

		return $args;
	}

	/**
	 * Determine the HTML tag to use for the event cost based on settings.
	 *
	 * @since 6.4.0
	 *
	 * @return string The HTML tag to use for the event cost.
	 */
	protected function get_html_tag(): string {
		$settings = $this->get_settings_for_display();

		return $settings['html_tag'] ?? 'p';
	}

	/**
	 * Determine the HTML tag to use for the event cost based on settings.
	 *
	 * @since 6.4.0
	 *
	 * @return string The HTML tag to use for the event cost.
	 */
	protected function get_header_tag() {

		$settings = $this->get_settings_for_display();

		return $settings['header_tag'] ?? 'h3';
	}

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_header_text(): string {
		return _x( 'Cost:', 'The header text for the event cost widget', 'the-events-calendar' );
	}

	/**
	 * Get the class used for the category header.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the class used for the category header.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the category header.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_category_widget_header_class', $class, $this );
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
	protected function content_panel(): void {
		$this->header_options();
		$this->content_options();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		$this->header_styling_options();
		$this->content_styling_options();
	}

	/**
	 * Add controls for the header of the event cost.
	 *
	 * @since 6.4.0
	 */
	protected function header_options(): void {
		$this->start_controls_section(
			'cost_header_section',
			[
				'label' => esc_html__( 'Cost Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'      => 'show_header',
				'label'   => esc_html__( 'Show Header', 'the-events-calendar' ),
				'default' => 'no',
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event cost.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'cost_content_section',
			[
				'label' => esc_html__( 'Cost Content', 'the-events-calendar' ),
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
	 * Add controls for text styling of the event cost header.
	 *
	 * @since 6.4.0
	 */
	protected function header_styling_options(): void {
		$this->start_controls_section(
			'styling_section_header',
			[
				'label'     => esc_html__( 'Header Styling', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_header' => 'yes',
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

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event cost.
	 *
	 * @since 6.4.0
	 */
	protected function content_styling_options(): void {
		$this->start_controls_section(
			'styling_section_content',
			[
				'label' => esc_html__( 'Content Styling', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'cost_content',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'align_content',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() ],
			]
		);

		$this->end_controls_section();
	}
}
