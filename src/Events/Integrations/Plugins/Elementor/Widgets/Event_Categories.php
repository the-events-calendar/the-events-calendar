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
	use Traits\With_Shared_Controls;

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_categories';

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
		return __( 'Event Categories', 'the-events-calendar' );
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
			'show_header' => tribe_is_truthy( $settings['show_categories_header'] ?? true ),
			'header_tag'  => $settings['categories_header_tag'] ?? 'h3',
			'header_text' => $this->get_header_text(),
			'categories'  => get_the_terms( $event_id, $tec_main->get_event_taxonomy() ),
			'settings'    => $settings,
			'event_id'    => $event_id,
		];
	}

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_header_text(): string {
		return _x( 'Categories:', 'The label/header text for the event categories widget', 'the-events-calendar' );
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
				'after'  => '',
				'before' => '',
				'sep'    => ', ',
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
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

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
		return apply_filters( 'tec_events_elementor_event_category_widget_header_class', $class, $this );
	}

	/**
	 * Get the class used for the category list.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_wrapper_class(): string {
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
		$this->header_styling();
		$this->content_styling();
	}

	/**
	 * Add controls for text content of the event categories.
	 *
	 * @since TBD
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'header_content_section',
			[
				'label' => esc_html__( 'Header ', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_categories_header',
				'label' => esc_html__( 'Show Header', 'the-events-calendar' ),
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
	 * Add controls for text styling of the section header.
	 *
	 * @since TBD
	 */
	protected function header_styling(): void {
		$this->start_controls_section(
			'header_style_section',
			[
				'label'     => esc_html__( 'Header Styles', 'the-events-calendar' ),
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

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'header_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event categories.
	 *
	 * @since TBD
	 */
	protected function content_styling(): void {
		$this->start_controls_section(
			'content_style_section',
			[
				'label' => esc_html__( 'Content Styles', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'content',
				'selector' => '{{WRAPPER}} .' . $this->get_wrapper_class() . ' a',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'content_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_wrapper_class() ],
			]
		);

		$this->end_controls_section();
	}
}
