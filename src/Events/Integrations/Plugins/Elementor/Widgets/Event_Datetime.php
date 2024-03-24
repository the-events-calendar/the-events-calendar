<?php
/**
 * Event Date & Time Elementor Widget.
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

/**
 * Class Widget_Event_Datetime
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Datetime extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_datetime';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Date & Time', 'tribe-events-calendar-pro' );
	}

	/**
	 * Get the class used for the datetime separators.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime separators.
	 */
	public function get_separator_class() {
		// tec-elementor-event-widget__datetime-separator.
		return $this->get_widget_class() . '-separator';
	}

	/**
	 * Get the base class used for the datetime date.
	 *
	 * @since TBD
	 *
	 * @return string The base class used for the datetime date.
	 */
	public function get_date_class() {
		// tec-elementor-event-widget__datetime-date.
		return $this->get_widget_class() . '-date';
	}

	/**
	 * Get the class used for the datetime start date.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime start date.
	 */
	public function get_start_date_class() {
		// tec-elementor-event-widget__datetime-date--start.
		return $this->get_date_class() . '--start';
	}

	/**
	 * Get the class used for the datetime end date.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime end date.
	 */
	public function get_end_date_class() {
		// tec-elementor-event-widget__datetime-date--end.
		return $this->get_date_class() . '--end';
	}

	/**
	 * Get the class used for the datetime all day indication.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime all day indication.
	 */
	public function get_all_day_class() {
		// tec-elementor-event-widget__datetime--all-day.
		return $this->get_widget_class() . '--all-day';
	}

	/**
	 * Get the base class used for the datetime time.
	 *
	 * @since TBD
	 *
	 * @return string The base class used for the datetime time.
	 */
	public function get_time_class() {
		// tec-elementor-event-widget__datetime-time.
		return $this->get_widget_class() . '-time';
	}

	/**
	 * Get the class used for the datetime start time.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime start time.
	 */
	public function get_start_time_class() {
		// tec-elementor-event-widget__datetime-time--start.
		return $this->get_time_class() . '--start';
	}

	/**
	 * Get the class used for the datetime end time.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime end time.
	 */
	public function get_end_time_class() {
		// tec-elementor-event-widget__datetime-time--end.
		return $this->get_time_class() . '--end';
	}

	/**
	 * Determine the HTML tag to use for the event datetime based on settings.
	 *
	 * @since TBD
	 *
	 * @return string The HTML tag to use for the event datetime.
	 */
	protected function get_html_tag() {

		$settings = $this->get_settings_for_display();

		return $settings['html_tag'] ?? 'p';
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
		$event    = tribe_get_event( $event_id );

		if ( empty( $event ) ) {
			return [];
		}
		$settings = $this->get_settings_for_display();

		// Date and time settings.
		$show_year   = tribe_is_truthy( $settings['show_datetime_year'] );
		$show_date   = tribe_is_truthy( $settings['show_datetime_date'] );
		$show_time   = tribe_is_truthy( $settings['show_datetime_time'] );
		$date_format = tribe_get_date_format( $show_year );
		$time_format = tribe_get_time_format();

		$start_date = $event->dates->start->format( $date_format ) ?? '';
		$end_date   = $event->dates->end->format( $date_format ) ?? '';
		$start_time = $event->dates->start->format( $time_format ) ?? '';
		$end_time   = $event->dates->end->format( $time_format ) ?? '';

		return [
			'html_tag'          => $this->get_html_tag(),
			'event_id'          => $event_id,
			'show_date'         => $show_date,
			'show_time'         => $show_time,
			'show_year'         => $show_year,
			'start_date'        => $start_date,
			'end_date'          => $end_date,
			'start_time'        => $start_time,
			'end_time'          => $end_time,
			'is_same_day'       => $start_date === $end_date,
			'is_all_day'        => tribe_event_is_all_day( $event_id ),
			'is_same_start_end' => $start_date === $end_date && $start_time === $end_time,
		];
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
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel() {
		// Styling options.
		$this->styling_options();
	}

	/**
	 * Add controls for text content of the event datetime.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => $this->get_title(),
			]
		);

		// Toggle for yearless date format.
		$this->add_control(
			'show_datetime_year',
			[
				'label'     => esc_html__( 'Show Year', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'Hide', 'tribe-events-calendar-pro' ),
				'default'   => 'no',
				'separator' => 'before',
			]
		);

		// Toggle to show or hide the date.
		$this->add_control(
			'show_datetime_date',
			[
				'label'     => esc_html__( 'Show Date (Day, Month)', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'Hide', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		// Toggle to show or hide the time.
		$this->add_control(
			'show_datetime_time',
			[
				'label'     => esc_html__( 'Show Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'Hide', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'p',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event datetime.
	 *
	 * @since TBD
	 */
	protected function styling_options() {
		$this->style_datetime();
	}

	/**
	 * Assembles the styling controls for the datetime.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_datetime() {
		$this->start_controls_section(
			'datetime_styling',
			[
				'label' => esc_html__( 'Date & Time Content', 'tribe-events-calendar-pro' ),
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
					'{{WRAPPER}} .' . $this->get_widget_class() => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
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
					'{{WRAPPER}} .' . $this->get_widget_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
