<?php
/**
 * Event Tags Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Tags
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Tags extends Abstract_Widget {
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
	protected static string $slug = 'event_tags';

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
		return esc_html__( 'Event Tags', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$event_id   = $this->get_event_id();
		$event_tags = get_the_tags( $event_id );
		$settings   = $this->get_settings_for_display();
		$tags       = [];

		if ( empty( $event_tags ) || is_wp_error( $event_tags ) ) {
			$event_tags = [];
		}

		foreach ( $event_tags as $tag ) {
			$tags[ $tag->name ] = get_tag_link( $tag->term_id );
		}

		return [
			'show_tags_header' => tribe_is_truthy( $settings['show_tags_header'] ?? true ),
			'header_tag'       => $settings['header_tag'] ?? 'h3',
			'tags'             => $tags,
			'label_text'       => $this->get_header_text(),
			'event_id'         => $event_id,
			'settings'         => $settings,
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
		$args = (array) $this->template_args();
		$id   = $this->get_event_id();

		if ( empty( $args['tags'] ) && ! tribe_is_event( $id ) ) {
			$args['tags'] = [
				'demo-tag-1' => '#',
				'demo-tag-2' => '#',
			];
		}

		return $args;
	}

	/**
	 * Allows filtering of the tag separator prior to output.
	 *
	 * @since 6.4.0
	 *
	 * @param bool $echo Whether to echo the separator or just return it, unescaped.
	 *
	 * @return mixed The separator as a string, no return when $echo set to true.
	 */
	public function print_tags_separator( $echo = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
		/**
		 * Filters the separator for the event tags widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string      $separator The separator.
		 * @param Event_Tags $this The event tags widget instance.
		 *
		 * @return string The filtered separator.
		 */
		$separator = (string) apply_filters( 'tec_events_elementor_event_tags_separator', ',', $this );

		if ( empty( $separator ) ) {
			$separator = ',';
		}

		if ( $echo ) {
			echo esc_html( $separator );
			return;
		}

		return $separator;
	}

	/**
	 * Get the label for the event tags widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string The label for the event tags widget.
	 */
	protected function get_header_text(): string {
		$label_text = sprintf(
			// Translators: %s is the singular lowercase label for an event, e.g., "event".
			__( 'Tags:', 'the-events-calendar' ),
			tribe_get_event_label_singular_lowercase()
		);

		/**
		 * Filters the label text for the event tags widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string      $label_text The label text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered label text.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_widget_header_text', $label_text, $this );
	}

	/**
	 * Get the class for the event tag header.
	 *
	 * @since 6.4.0
	 *
	 * @return string The header class.
	 */
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the base class for the event tags header section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The header base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_header_class', $class, $this );
	}

	/**
	 * Get the class for the event tag links section.
	 *
	 * @since 6.4.0
	 *
	 * @return string The links class.
	 */
	public function get_links_class(): string {
		$class = $this->get_widget_class() . '-links';

		/**
		 * Filters the base class for the event tags links section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The links base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_links_class', $class, $this );
	}

	/**
	 * Get the class for a single event tag link.
	 *
	 * @since 6.4.0
	 *
	 * @return string The link class.
	 */
	public function get_link_class(): string {
		$class = $this->get_widget_class() . '-link';

		/**
		 * Filters the base class for the event tags link section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The link base class.
		 * @param Event_tags $this The event tags widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_tags_link_class', $class, $this );
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
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		$this->header_styling();
		$this->tags_styling();
	}

	/**
	 * Add controls for text content of the event tags.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'content_section',
			[
				'label' => $this->get_title(),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_tags_header',
				'label' => esc_html__( 'Show Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_tags_header' => 'yes',
				],
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
			'header_styling_section',
			[
				'label'     => esc_html__( 'Heading Styles', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_tags_header' => 'yes',
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
	 * Add controls for text styling of the tags.
	 *
	 * @since 6.4.0
	 */
	protected function tags_styling(): void {
		$this->start_controls_section(
			'tags_styling_section',
			[
				'label' => esc_html__( 'Event Tags', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'tags',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'tag_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_links_class() ],
			]
		);

		$this->end_controls_section();
	}
}
