<?php
/**
 * Elementor Event Export Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe\Events\Views\V2\iCalendar\Links\iCal;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_365;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_Live;

/**
 * Class Widget_Event_Export
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Export extends Abstract_Widget {
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
	protected static string $slug = 'event_export';

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
		return esc_html__( 'Add to Calendar', 'the-events-calendar' );
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
		$args     = [
			'settings' => $settings,
			'event_id' => $this->get_event_id(),
			'show'     => false,
		];

		if ( ! $this->has_event_id() ) {
			return $args;
		}

		if ( tribe_is_truthy( $settings['show_gcal_link'] ?? true ) ) {
			$args = $this->add_gcal_data( $args );
		}

		if ( tribe_is_truthy( $settings['show_ical_link'] ?? true ) ) {
			$args = $this->add_ical_data( $args );
		}

		if ( tribe_is_truthy( $settings['show_outlook_365_link'] ?? true ) ) {
			$args = $this->add_outlook_365_data( $args );
		}

		if ( tribe_is_truthy( $settings['show_outlook_live_link'] ?? true ) ) {
			$args = $this->add_outlook_live_data( $args );
		}

		return $args;
	}

	/**
	 * Get the template args for the widget preview.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function preview_args(): array {
		$settings = $this->get_settings_for_display();
		$args     = [
			'event_id' => $this->get_event_id(),
			'show'     => true,
		];

		if ( tribe_is_truthy( $settings['show_gcal_link'] ?? true ) ) {
			$args                 = $this->add_gcal_data( $args );
			$args['gcal']['link'] = '#';
		}

		if ( tribe_is_truthy( $settings['show_ical_link'] ?? true ) ) {
			$args                 = $this->add_ical_data( $args );
			$args['ical']['link'] = '#';
		}

		if ( tribe_is_truthy( $settings['show_outlook_365_link'] ?? true ) ) {
			$args                        = $this->add_outlook_365_data( $args );
			$args['outlook_365']['link'] = '#';
		}

		if ( tribe_is_truthy( $settings['show_outlook_live_link'] ?? true ) ) {
			$args                         = $this->add_outlook_live_data( $args );
			$args['outlook_live']['link'] = '#';
		}

		return $args;
	}

	/**
	 * Get the template data for the ical link.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args The template data.
	 */
	protected function add_ical_data( $args ): array {
		$args['show']           = true;
		$ical_helper            = new iCal();
		$args['show_ical_link'] = true;
		$args['ical']['label']  = $ical_helper->get_label();
		$args['ical']['link']   = tribe_get_single_ical_link();
		$args['ical']['class']  = [
			$this->get_list_item_class(),
			$this->get_ical_class(),
		];

		return $args;
	}

	/**
	 * Get the template data for the gcal link.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args The template data.
	 */
	protected function add_gcal_data( $args ): array {
		$args['show']           = true;
		$gcal_helper            = new Google_Calendar();
		$args['show_gcal_link'] = true;
		$args['gcal']['label']  = $gcal_helper->get_label();
		$args['gcal']['link']   = \Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() );
		$args['gcal']['class']  = [
			$this->get_list_item_class(),
			$this->get_gcal_class(),
		];

		return $args;
	}

	/**
	 * Get the template data for the Outlook 365 link.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args The template data.
	 */
	protected function add_outlook_365_data( $args ): array {
		$args['show']                  = true;
		$outlook_365_helper            = new Outlook_365();
		$args['show_outlook_365_link'] = true;
		$args['outlook_365']['label']  = $outlook_365_helper->get_label();
		$args['outlook_365']['link']   = $outlook_365_helper->generate_outlook_full_url();
		$args['outlook_365']['class']  = [
			$this->get_list_item_class(),
			$this->get_outlook_365_class(),
		];

		return $args;
	}

	/**
	 * Get the template data for the Outlook Live link.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args The template data.
	 */
	protected function add_outlook_live_data( $args ): array {
		$args['show']                   = true;
		$outlook_live_helper            = new Outlook_Live();
		$args['show_outlook_live_link'] = true;
		$args['outlook_live']['label']  = $outlook_live_helper->get_label();
		$args['outlook_live']['link']   = $outlook_live_helper->generate_outlook_full_url();
		$args['outlook_live']['class']  = [
			$this->get_list_item_class(),
			$this->get_outlook_live_class(),
		];

		return $args;
	}

	/**
	 * Get the class used for the dropdown.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_dropdown_class(): string {
		$class = $this->get_widget_class() . '-dropdown';

		/**
		 * Filters the class used for the website link label.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the website link label.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown button.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_button_class(): string {
		$class = $this->get_dropdown_class() . '-button';

		/**
		 * Filters the class used for the dropdown button.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown button.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_button_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown list.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_list_class(): string {
		$class = $this->get_dropdown_class() . '-list';

		/**
		 * Filters the class used for the dropdown list.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown list.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_list_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown list items.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_list_item_class(): string {
		$class = $this->get_dropdown_class() . '-list-item';

		/**
		 * Filters the class used for the dropdown list items.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown list items.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_list_item_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown links.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_link_class(): string {
		$class = $this->get_dropdown_class() . '-link';

		/**
		 * Filters the class used for the dropdown links.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown links.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_link_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown content.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_content_class(): string {
		$class = $this->get_dropdown_class() . '-content';

		/**
		 * Filters the class used for the dropdown content.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown content.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_content_class', $class, $this );
	}

	/**
	 * Get the class used for the dropdown icon (arrow/caret).
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_dropdown_icon_class(): string {
		$class = $this->get_dropdown_class() . '-icon';

		/**
		 * Filters the class used for the dropdown icon.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the dropdown icon.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_icon_class', $class, $this );
	}

	/**
	 * Get the class used for the export icon (arrow/caret).
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_export_icon_class(): string {
		$class = $this->get_dropdown_class() . '-export-icon';

		/**
		 * Filters the class used for the export icon.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the export icon.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_export_icon_class', $class, $this );
	}

	/**
	 * Get the class used for the gcal link.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_gcal_class(): string {
		$class = $this->get_dropdown_class() . '--gcal';

		/**
		 * Filters the class used for the gcal link.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the gcal link.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_gcal_class', $class, $this );
	}

	/**
	 * Get the class used for the ical link.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_ical_class(): string {
		$class = $this->get_dropdown_class() . '--ical';

		/**
		 * Filters the class used for the ical link.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the ical link.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_ical_class', $class, $this );
	}

	/**
	 * Get the class used for the Outlook 365 link.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_outlook_365_class(): string {
		$class = $this->get_dropdown_class() . '--outlook-365';

		/**
		 * Filters the class used for the 365 link.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the Outlook 365 link.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_365_class', $class, $this );
	}

	/**
	 * Get the class used for the Outlook live link.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_outlook_live_class(): string {
		$class = $this->get_dropdown_class() . '--outlook-live';

		/**
		 * Filters the class used for the live link.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The class used for the Outlook live link.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_elementor_event_export_widget_dropdown_live_class', $class, $this );
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
	 * Add controls for text content of the Google & iCal export.
	 *
	 * @since 6.4.0
	 */
	protected function content_options(): void {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Add to Calendar Button', 'the-events-calendar' ),
			]
		);

		// Toggle for including Google Calendar.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_gcal_link',
				'label' => esc_html__( 'Include Google Calendar', 'the-events-calendar' ),
			]
		);

		// Toggle for including iCalendar.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_ical_link',
				'label' => esc_html__( 'Include iCalendar', 'the-events-calendar' ),
			]
		);

		// Toggle for including Outlook 365.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_outlook_365_link',
				'label' => esc_html__( 'Include Outlook 365', 'the-events-calendar' ),
			]
		);

		// Toggle for including Outlook Live.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_outlook_live_link',
				'label' => esc_html__( 'Include Outlook Live', 'the-events-calendar' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		$this->style_export_button();
		$this->style_export_button_hover();
		$this->style_export_dropdown();
	}

	/**
	 * Add controls for text styling of the Google & iCal export button.
	 *
	 * @since 6.4.0
	 */
	protected function style_export_button(): void {
		$this->start_controls_section(
			'button_styling_section',
			[
				'label' => esc_html__( 'Button Styles', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'button',
				'selector' => '{{WRAPPER}} .' . $this->get_button_class(),
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class() => 'border-color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_dropdown_class() . '  .' . $this->get_button_class() => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the Google & iCal export button on hover.
	 *
	 * @since 6.4.0
	 */
	protected function style_export_button_hover(): void {
		$this->start_controls_section(
			'button_hover_styling_section',
			[
				'label' => esc_html__( 'Button Styles on Hover', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'button_hover',
				'selector' => '{{WRAPPER}} .' . $this->get_button_class() . ':hover',
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_button_class() . ':hover' => 'border-color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_hover_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_button_class() => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the Google & iCal export dropdown.
	 *
	 * @since 6.4.0
	 */
	protected function style_export_dropdown(): void {
		$this->start_controls_section(
			'dropdown_styling_section',
			[
				'label' => esc_html__( 'Dropdown Options', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'dropdown',
				'selector' => '{{WRAPPER}} .' . $this->get_link_class(),
				'label'    => esc_html__( 'Dropdown Typography', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'dropdown_align',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_list_class() ],
			]
		);

		$this->add_control(
			'dropdown_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .' . $this->get_list_class() => 'border-color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dropdown_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_list_class() => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}
}
