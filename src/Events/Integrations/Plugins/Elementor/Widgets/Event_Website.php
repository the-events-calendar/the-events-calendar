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
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Website
 *
 * @since   TBD
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
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_website';

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
		return esc_html__( 'Event Website', 'the-events-calendar' );
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
		if ( $settings['website_link_target'] ) {
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

		return [
			'align'               => $settings['align'] ?? '',
			'show_website_header' => $settings['show_website_header'] ?? 'yes',
			'header_tag'          => $settings['header_tag'] ?? 'h3',
			'event_id'            => $event_id,
			'header_class'        => $this->get_header_class(),
			'link_class'          => $this->get_link_class(),
		];
	}

	/**
	 * Get the template args for the widget preview.
	 *
	 * @since TBD
	 *
	 * @return array The template args for the preview.
	 */
	protected function preview_args(): array {
		$args = $this->template_args();

		$args['website'] = '<a href="http://theeventscalendar.com" target="_self" rel="external">http://theeventscalendar.com</a>';

		return $args;
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
		$target_override = $settings['website_link_target'];

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
	public function modify_link_label( $label, $post_id ): string {
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
	 * Get the class used for the website link header.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the class used for the website header.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_link_class(): string {
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
		$this->header_options();
		$this->link_options();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel(): void {
		$this->header_styling();
		$this->link_styling();
	}

	/**
	 * Add controls for the header content of the event website.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
