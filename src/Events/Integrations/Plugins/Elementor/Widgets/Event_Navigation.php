<?php
/**
 * Event Nav Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Navigation
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Navigation extends Abstract_Widget {
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
	protected static string $slug = 'event_navigation';

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
		return esc_html__( 'Event Navigation', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the event nav widget.
	 *
	 * @since 6.4.0
	 */
	protected function template_args(): array {
		$adjacent_events = tribe( 'tec.adjacent-events' );
		$adjacent_events->set_current_event_id( $this->get_event_id() );
		$next_event = $adjacent_events->get_closest_event( 'next' );
		$prev_event = $adjacent_events->get_closest_event( 'previous' );

		return [
			'prev_event' => $prev_event,
			'prev_link'  => tribe_get_event_link( $prev_event ),
			'next_event' => $next_event,
			'next_link'  => tribe_get_event_link( $next_event ),
			'event_id'   => $this->get_event_id(),
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

		$prev_event             = new \stdClass();
		$next_event             = new \stdClass();
		$prev_event->post_title = 'Previous Event';
		$next_event->post_title = 'Next Event';

		return [
			'prev_event' => $prev_event,
			'prev_link'  => '#',
			'next_event' => $next_event,
			'next_link'  => '#',
			'event_id'   => $this->get_event_id(),
		];
	}

	/**
	 * Get the class for the next link.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class for the element.
	 */
	public function get_next_class(): string {
		return $this->get_widget_class() . '--next';
	}

	/**
	 * Get the class for the pervious link.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class for the element.
	 */
	public function get_prev_class(): string {
		return $this->get_widget_class() . '--previous';
	}

	/**
	 * Get the class for the link list.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class for the element.
	 */
	public function get_list_class(): string {
		return $this->get_widget_class() . '--subnav';
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
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel() {
		$this->content_styling_options();
		$this->content_hover_styling_options();
	}

	/**
	 * Add controls for text styling of the event nav content.
	 *
	 * @since 6.4.0
	 */
	protected function content_styling_options() {
		$this->start_controls_section(
			'content_styling_section',
			[
				'label' => esc_html__( 'Link Styling', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'content',
				'selector' => '{{WRAPPER}} .' . $this->get_list_class() . ' a',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event nav content.
	 *
	 * @since 6.4.0
	 */
	protected function content_hover_styling_options() {
		$this->start_controls_section(
			'content_hover_styling_section',
			[
				'label' => esc_html__( 'Link Hover Styling', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'content_hover',
				'selector' => '{{WRAPPER}} .' . $this->get_list_class() . ' a:hover',
			]
		);

		$this->end_controls_section();
	}
}
