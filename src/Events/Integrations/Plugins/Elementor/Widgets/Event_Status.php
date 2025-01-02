<?php
/**
 * Event Status Elementor Widget.
 *
 * @since 6.4.0
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
use Tribe\Events\Event_Status\Status_Labels;

/**
 * Class Widget_Event_Status
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Status extends Abstract_Widget {
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
	protected static string $slug = 'event_status';

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Status', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	public function template_args(): array {
		$event = $this->get_event();

		if ( empty( $event ) ) {
			return [];
		}

		$is_passed = tribe_is_event( $event ) && tribe_is_past_event( get_post( $event ) );
		$settings  = $this->get_settings_for_display();

		return [
			'description_class'  => $this->get_status_description_class(),
			'label_class'        => $this->get_status_label_class(),
			'status'             => $event->event_status ?? '',
			'status_label'       => $this->get_status_label( $event ),
			'status_reason'      => $event->event_status_reason ?? '',
			'show_status'        => tribe_is_truthy( $settings['show_status'] ?? true ),
			'show_passed'        => tribe_is_truthy( $settings['show_passed'] ?? true ),
			'is_passed'          => tribe_is_truthy( $is_passed ),
			'passed_label'       => $this->get_passed_label_text(),
			'passed_label_class' => $this->get_passed_label_class(),
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
		$event = $this->get_event();

		if ( tribe_is_event( $event ) ) {
			return $this->template_args();
		}

		return [
			'description_class'  => $this->get_status_description_class(),
			'label_class'        => $this->get_status_label_class(),
			'status'             => __( 'postponed', 'the-events-calendar' ),
			'status_label'       => __( 'postponed', 'the-events-calendar' ),
			'status_reason'      => __( '(DEMO) This event has been postponed.', 'the-events-calendar' ),
			'show_status'        => tribe_is_truthy( $settings['show_status'] ?? true ),
			'show_passed'        => tribe_is_truthy( $settings['show_passed'] ?? true ),
			'is_passed'          => true,
			'passed_label'       => $this->get_passed_label_text(),
			'passed_label_class' => $this->get_passed_label_class(),
		];
	}

	/**
	 * Get the CSS class for the label.
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the label.
	 */
	public function get_status_label_class(): string {
		return $this->get_widget_class() . '-label';
	}

	/**
	 * Get the displayed label for the status widget.
	 *
	 * @since 6.4.0
	 *
	 * @param \WP_Post $event The event post object.
	 *
	 * @return string The CSS class for the status label.
	 */
	protected function get_status_label( $event ): ?string {
		if ( empty( $event->event_status ) ) {
			return null;
		}

		$status_labels = new Status_Labels();
		$method        = 'get_' . $event->event_status . '_label';

		if ( ! method_exists( $status_labels, $method ) ) {
			return null;
		}

		return $status_labels->$method();
	}

	/**
	 * Get the CSS class for the passed label.
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the passed label.
	 */
	public function get_passed_label_class(): string {
		return $this->get_widget_class() . '-passed';
	}

	/**
	 * Get the CSS class for the Status description.
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the description.
	 */
	public function get_status_description_class(): string {
		return $this->get_widget_class() . '--description';
	}

	/**
	 * Get the CSS class for the status.
	 *
	 * @since 6.4.0
	 *
	 * @param string $status The status.
	 *
	 * @return string The CSS class for the status.
	 */
	public function get_status_class( $status ) {
		$method = 'get_' . $status . '_class';

		// Don't call a method we don't have - in case of custom stati.
		if ( ! method_exists( $this, $method ) ) {
			return '';
		}

		return $this->$method();
	}

	/**
	 * Get the CSS class for the postponed label.
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the postponed label.
	 */
	protected function get_postponed_class(): string {
		return $this->get_status_label_class() . '--postponed';
	}

	/**
	 * Get the CSS class for the canceled .
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the canceled .
	 */
	protected function get_canceled_class(): string {
		return $this->get_status_label_class() . '--canceled';
	}

	/**
	 * Get the CSS class for the event passed label.
	 *
	 * @since 6.4.0
	 *
	 * @return string The CSS class for the event passed label.
	 */
	protected function get_passed_label_text(): string {
		$label_text = sprintf(
			// Translators: %s is the singular lowercase label for an event, e.g., "event".
			__( 'This %s has passed.', 'the-events-calendar' ),
			tribe_get_event_label_singular_lowercase()
		);

		/**
		 * Filters the label text for the event passed widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string       $label_text The label text.
		 * @param Event_Passed $this The event passed widget instance.
		 *
		 * @return string The filtered label text.
		 */
		return apply_filters( 'tec_events_elementor_event_passed_label_text', $label_text, $this );
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
		$this->passed_label_styling();

		$this->status_label_styling();

		$this->status_description_styling();

		$this->status_peripherals_styling();
	}

	/**
	 * Add controls for text content of the event status widget.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'content_section_title',
			[
				'label' => esc_html__( 'Content', 'the-events-calendar' ),
			]
		);

		$this->add_control(
			'content_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__(
					'The toggles below let you control the visibility of message banners related to:',
					'the-events-calendar'
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_passed',
				'label' => esc_html__( 'Show Event Passed', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_status',
				'label' => esc_html__( 'Show Event Status', 'the-events-calendar' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event passed label.
	 *
	 * @since 6.4.0
	 */
	protected function passed_label_styling() {
		$this->start_controls_section(
			'passed_label_styling_section_title',
			[
				'label' => esc_html__( 'Passed Label', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'passed',
				'selector' => '{{WRAPPER}} .' . $this->get_passed_label_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'align_passed',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_passed_label_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event status label.
	 *
	 * @since 6.4.0
	 */
	protected function status_label_styling() {
		$this->start_controls_section(
			'status_label_styling_section_title',
			[
				'label' => esc_html__( 'Status Label', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'status',
				'selector' => '{{WRAPPER}} .' . $this->get_status_label_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'align_status',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_status_label_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event status status.
	 *
	 * @since 6.4.0
	 */
	protected function status_description_styling() {
		$this->start_controls_section(
			'status_description_styling_section_title',
			[
				'label' => esc_html__( 'Status Description', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'status_description_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_status_description_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'status_description_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} ' . $this->get_status_description_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'status_description_text_stroke',
				'selector' => '{{WRAPPER}} ' . $this->get_status_description_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'status_description_text_shadow',
				'selector' => '{{WRAPPER}} ' . $this->get_status_description_class(),
			]
		);

		$this->add_control(
			'status_description_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $this->get_status_description_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event status peripherals.
	 *
	 * @since 6.4.0
	 */
	protected function status_peripherals_styling() {
		$this->start_controls_section(
			'status_peripherals_styling_section_title',
			[
				'label' => esc_html__( 'Status Peripherals', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'status_peripherals_main_border_color',
			[
				'label'     => esc_html__( 'Main Border Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#da394d',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'border: 1px solid {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'status_peripherals_border_left_color',
			[
				'label'     => esc_html__( 'Left Border Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'border-left: 4px solid {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get the message to show when the widget is empty.
	 *
	 * @since 6.4.0
	 *
	 * @return string The message shown when an event widget is empty.
	 */
	public function get_empty_message(): string {
		return esc_html_x(
			'The Event Status widget only shows content if the chosen event has passed, been canceled, or postponed.',
			'The message shown when the event status widget is empty.',
			'the-events-calendar'
		);
	}

	/**
	 * Conditions for showing the empty widget template in the editor.
	 *
	 * @since 6.4.0
	 */
	protected function empty_conditions(): bool {
		$event = $this->get_event();

		if ( ! tribe_is_event( $event ) ) {
			return true;
		}

		$settings = $this->get_settings_for_display();

		if ( isset( $settings['show_passed'] ) && tribe_is_past_event( $event ) ) {
			return false;
		}

		if ( isset( $settings['show_status'] ) && ! empty( $event->event_status ) ) {
			return false;
		}

		return true;
	}
}
