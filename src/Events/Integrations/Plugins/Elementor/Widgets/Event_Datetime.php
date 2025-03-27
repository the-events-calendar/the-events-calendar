<?php
/**
 * Event Date & Time Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use Tribe__Events__Timezones;

/**
 * Class Widget_Event_Datetime
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Datetime extends Abstract_Widget {
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
	protected static string $slug = 'event_datetime';

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
		return esc_html__( 'Event Date & Time', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 * @since 6.9.0 Changed `format` method to `format_i18n` to allow for translations of dates.
	 *
	 * @return array The template args.
	 */
	public function template_args(): array {
		$event = $this->get_event();

		if ( empty( $event ) ) {
			return [];
		}

		$settings = $this->get_settings_for_display();

		// Date and time settings.
		$show_year   = tribe_is_truthy( $settings['show_year'] ?? false );
		$date_format = tribe_get_date_format( $show_year );
		$start_date  = $event->dates->start->format_i18n( $date_format ) ?? '';
		$end_date    = $event->dates->end->format_i18n( $date_format ) ?? '';

		$time_format = tribe_get_time_format();
		$start_time  = $event->dates->start->format_i18n( $time_format ) ?? '';
		$end_time    = $event->dates->end->format_i18n( $time_format ) ?? '';

		$show_tz = tribe_is_truthy( $settings['show_timezone'] ?? tribe_get_option( 'tribe_events_timezones_show_zone', false ) );
		if ( $show_tz ) {
			$time_zone_label = Tribe__Events__Timezones::is_mode( 'site' ) ? Tribe__Events__Timezones::wp_timezone_abbr( $start_date ) : Tribe__Events__Timezones::get_event_timezone_abbr( $event->ID );
		}

		return [
			'all_day_text'      => $this->get_all_day_text(),
			'end_date'          => $end_date,
			'end_time'          => $end_time,
			'header_text'       => $this->get_header_text(),
			'header_tag'        => $this->get_header_tag(),
			'html_tag'          => $this->get_html_tag(),
			'is_all_day'        => tribe_event_is_all_day( $event ),
			'is_same_day'       => $start_date === $end_date,
			'is_same_start_end' => ( $start_date === $end_date ) && ( $start_time === $end_time ),
			'show_date'         => tribe_is_truthy( $settings['show_date'] ?? true ),
			'show_header'       => tribe_is_truthy( $settings['show_header'] ?? false ),
			'show_time'         => tribe_is_truthy( $settings['show_time'] ?? true ),
			'show_year'         => $show_year,
			'show_timezone'     => $show_tz,
			'time_zone_label'   => $time_zone_label ?? '',
			'start_date'        => $start_date,
			'start_time'        => $start_time,
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
		return $this->template_args();
	}

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_header_text(): string {
		return _x( 'Date & Time:', 'The header text for the event date and time widget', 'the-events-calendar' );
	}

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_all_day_text(): string {
		return _x( 'All day', 'The all-day text for the event date and time widget', 'the-events-calendar' );
	}

	/**
	 * Get the class used for the category header.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_header_class() {
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
	 * Get the HTML tag used for the category header.
	 *
	 * @since 6.4.0
	 */
	public function get_header_tag(): string {
		$settings = $this->get_settings_for_display();

		return $settings['header_tag'] ?? 'h3';
	}

	/**
	 * Get the class used for the datetime separators.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime separators.
	 */
	public function get_separator_class() {
		return $this->get_widget_class() . '-separator';
	}

	/**
	 * Get the base class used for the datetime date.
	 *
	 * @since 6.4.0
	 *
	 * @return string The base class used for the datetime date.
	 */
	public function get_date_class() {
		return $this->get_widget_class() . '-date';
	}

	/**
	 * Get the class used for the datetime start date.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime start date.
	 */
	public function get_start_date_class() {
		return $this->get_date_class() . '--start';
	}

	/**
	 * Get the class used for the datetime end date.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime end date.
	 */
	public function get_end_date_class() {
		return $this->get_date_class() . '--end';
	}

	/**
	 * Get the class used for the datetime all day indication.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime all day indication.
	 */
	public function get_all_day_class() {
		return $this->get_widget_class() . '--all-day';
	}

	/**
	 * Get the base class used for the datetime time.
	 *
	 * @since 6.4.0
	 *
	 * @return string The base class used for the datetime time.
	 */
	public function get_time_class() {
		return $this->get_widget_class() . '-time';
	}

	/**
	 * Get the class used for the datetime start time.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime start time.
	 */
	public function get_start_time_class() {
		return $this->get_time_class() . '--start';
	}

	/**
	 * Get the class used for the datetime end time.
	 *
	 * @since 6.4.0
	 *
	 * @return string The class used for the datetime end time.
	 */
	public function get_end_time_class() {
		return $this->get_time_class() . '--end';
	}

	/**
	 * Determine the HTML tag to use for the event datetime based on settings.
	 *
	 * @since 6.4.0
	 *
	 * @return string The HTML tag to use for the event datetime.
	 */
	protected function get_html_tag() {
		$settings = $this->get_settings_for_display();

		return $settings['html_tag'] ?? 'div';
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
		$this->header_content_section();
		$this->datetime_content_section();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel(): void {
		// Styling options.
		$this->style_datetime_header();
		$this->style_datetime_content();
	}

	/**
	 * Add controls for header of the event datetime.
	 *
	 * @since 6.4.0
	 */
	protected function header_content_section(): void {
		$this->start_controls_section(
			'header_section',
			[
				'label' => 'Date & Time Header',
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
				'default'   => 'h3',
				'condition' => [
					'show_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event datetime.
	 *
	 * @since 6.4.0
	 */
	protected function datetime_content_section(): void {
		$this->start_controls_section(
			'content_section',
			[ 'label' => $this->get_title() ]
		);

		// Toggle for yearless date format.
		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_year',
				'label'     => esc_html__( 'Show Year', 'the-events-calendar' ),
				'default'   => 'no',
				'separator' => 'before',
			]
		);

		// Toggle to show or hide the date.
		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_date',
				'label'     => esc_html__( 'Show Date (Day, Month)', 'the-events-calendar' ),
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		// Toggle to show or hide the time.
		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_time',
				'label'     => esc_html__( 'Show Time', 'the-events-calendar' ),
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		// Toggle to show or hide the time.
		$this->add_shared_control(
			'show',
			[
				'id'          => 'show_timezone',
				'label'       => esc_html__( 'Show Timezone', 'the-events-calendar' ),
				'default'     => 'no',
				'separator'   => 'before',
				'description' => esc_html__( 'Show the timezone of the event. This overrides the option set in Events -> Settings -> Display.', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'html_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'default'   => 'div',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the datetime.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_datetime_header(): void {
		$this->start_controls_section(
			'datetime_header_styling',
			[
				'label'     => esc_html__( 'Date & Time Header', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'datetime_header',
				'selector' => '{{WRAPPER}} .' . $this->get_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'align_header',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_header_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the datetime.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_datetime_content(): void {
		$this->start_controls_section(
			'datetime_content_styling',
			[
				'label' => esc_html__( 'Date & Time Content', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'datetime_content',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_shared_control(
			'flex_alignment',
			[
				'id'        => 'align_content',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() . '-wrapper',
				],
			]
		);

		$this->end_controls_section();
	}
}
