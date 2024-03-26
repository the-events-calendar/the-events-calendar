<?php
/**
 * Elementor Event Export Widget.
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
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe\Events\Views\V2\iCalendar\Links\iCal;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_365;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_Live;

/**
 * Class Widget_Event_Export
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Export extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_export';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Add to Calendar', 'tribe-events-calendar-pro' );
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
		$args     = [
			'settings' => $settings,
			'event_id' => $this->get_event_id(),
			'show'     => false,
		];

		if ( ! $this->has_event_id() ) {
			return $args;
		}

		if ( tribe_is_truthy( $settings['show_gcal_link'] ?? false ) ) {
			$args['show']           = true;
			$gcal_helper            = new Google_Calendar();
			$args['show_gcal_link'] = true;
			$args['gcal']['label']  = $gcal_helper->get_label();
			$args['gcal']['link']   = \Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() );
			$args['gcal']['class']  = [
				$this->get_list_item_class(),
				$this->get_gcal_class(),
			];
		}

		if ( tribe_is_truthy( $settings['show_ical_link'] ?? false ) ) {
			$args['show']           = true;
			$ical_helper            = new iCal();
			$args['show_ical_link'] = true;
			$args['ical']['label']  = $ical_helper->get_label();
			$args['ical']['link']   = tribe_get_single_ical_link();
			$args['ical']['class']  = [
				$this->get_list_item_class(),
				$this->get_ical_class(),
			];
		}

		if ( tribe_is_truthy( $settings['show_outlook_365_link'] ?? false ) ) {
			$args['show']                  = true;
			$outlook_365_helper            = new Outlook_365();
			$args['show_outlook_365_link'] = true;
			$args['outlook_365']['label']  = $outlook_365_helper->get_label();
			$args['outlook_365']['link']   = $outlook_365_helper->generate_outlook_full_url();
			$args['outlook_365']['class']  = [
				$this->get_list_item_class(),
				$this->get_outlook_365_class(),
			];
		}

		if ( tribe_is_truthy( $settings['show_outlook_live_link'] ?? false ) ) {
			$args['show']                   = true;
			$outlook_live_helper            = new Outlook_Live();
			$args['show_outlook_live_link'] = true;
			$args['outlook_live']['label']  = $outlook_live_helper->get_label();
			$args['outlook_live']['link']   = $outlook_live_helper->generate_outlook_full_url();
			$args['outlook_live']['class']  = [
				$this->get_list_item_class(),
				$this->get_outlook_live_class(),
			];
		}

		return $args;
	}

	/**
	 * Get the class used for the dropdown.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_dropdown_class(): string {
		$class = $this->get_widget_class() . '-dropdown';

		/**
		 * Filters the class used for the website link label.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_button_class(): string {
		$class = $this->get_dropdown_class() . '-button';

		/**
		 * Filters the class used for the dropdown button.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_list_class(): string {
		$class = $this->get_dropdown_class() . '-list';

		/**
		 * Filters the class used for the dropdown list.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_list_item_class(): string {
		$class = $this->get_dropdown_class() . '-list-item';

		/**
		 * Filters the class used for the dropdown list items.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_link_class(): string {
		$class = $this->get_dropdown_class() . '-link';

		/**
		 * Filters the class used for the dropdown links.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_content_class(): string {
		$class = $this->get_dropdown_class() . '-content';

		/**
		 * Filters the class used for the dropdown content.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_dropdown_icon_class(): string {
		$class = $this->get_dropdown_class() . '-icon';

		/**
		 * Filters the class used for the dropdown icon.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_export_icon_class(): string {
		$class = $this->get_dropdown_class() . '-export-icon';

		/**
		 * Filters the class used for the export icon.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_gcal_class(): string {
		$class = $this->get_dropdown_class() . '--gcal';

		/**
		 * Filters the class used for the gcal link.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_ical_class(): string {
		$class = $this->get_dropdown_class() . '--ical';

		/**
		 * Filters the class used for the ical link.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_outlook_365_class(): string {
		$class = $this->get_dropdown_class() . '--outlook-365';

		/**
		 * Filters the class used for the 365 link.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_outlook_live_class(): string {
		$class = $this->get_dropdown_class() . '--outlook-live';

		/**
		 * Filters the class used for the live link.
		 *
		 * @since TBD
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
	 * Add controls for text content of the Google & iCal export.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Add to Calendar Button', 'tribe-events-calendar-pro' ),
			]
		);

		// Toggle for including Google Calendar.
		$this->add_control(
			'show_gcal_link',
			[
				'label'     => esc_html__( 'Include Google Calendar', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Toggle for including iCalendar.
		$this->add_control(
			'show_ical_link',
			[
				'label'     => esc_html__( 'Include iCalendar', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Toggle for including Outlook 365.
		$this->add_control(
			'show_outlook_365_link',
			[
				'label'     => esc_html__( 'Include Outlook 365', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Toggle for including Outlook Live.
		$this->add_control(
			'show_outlook_live_link',
			[
				'label'     => esc_html__( 'Include Outlook Live', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel() {
		$this->style_export_button();

		$this->style_export_dropdown();
	}

	/**
	 * Add controls for text styling of the Google & iCal export button.
	 *
	 * @since TBD
	 */
	protected function style_export_button() {
		$this->start_controls_section(
			'styling_section_title',
			[
				'label' => esc_html__( 'Add to Calendar Button', 'tribe-events-calendar-pro' ),
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
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_on_hover',
			[
				'label'     => esc_html__( 'Text Color on Hover', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class() . ':hover' => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class(),
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
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_button_class() => 'border-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . '  .' . $this->get_button_class() => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label'     => esc_html__( 'Hover Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . '  .' . $this->get_button_class() . ':hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the Google & iCal export dropdown.
	 *
	 * @since TBD
	 */
	protected function style_export_dropdown() {
		$this->start_controls_section(
			'dropdown_styling_section_title',
			[
				'label' => esc_html__( 'Export Options', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dropdown_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_link_class() => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'dropdown_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_link_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'dropdown_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_link_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'dropdown_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_link_class(),
			]
		);

		$this->add_control(
			'dropdown_blend_mode',
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
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_link_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->add_control(
			'dropdown_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_list_class() => 'border-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dropdown_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_dropdown_class() . ' .' . $this->get_list_class() => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}
}
