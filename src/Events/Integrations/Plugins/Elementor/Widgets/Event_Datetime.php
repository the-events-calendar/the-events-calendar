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
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Widget_Event_Datetime
 *
 * @since   TBD
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
		return esc_html__( 'Event Date & Time', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	public function template_args(): array {
		$event_id = $this->get_event_id();
		$event    = tribe_get_event( $event_id );

		if ( empty( $event ) ) {
			return [ 'show' => false ];
		}

		$settings = $this->get_settings_for_display();

		// Date and time settings.
		$show_year   = tribe_is_truthy( $settings['show_year'] ?? false );
		$date_format = tribe_get_date_format( $show_year );
		$start_date  = $event->dates->start->format( $date_format ) ?? '';
		$end_date    = $event->dates->end->format( $date_format ) ?? '';

		$time_format = tribe_get_time_format();
		$start_time  = $event->dates->start->format( $time_format ) ?? '';
		$end_time    = $event->dates->end->format( $time_format ) ?? '';

		return [
			'show'              => true,
			'show_header'       => tribe_is_truthy( $settings['show_header'] ?? false ),
			'html_tag'          => $this->get_html_tag(),
			'show_date'         => tribe_is_truthy( $settings['show_date'] ?? false ),
			'show_time'         => tribe_is_truthy( $settings['show_time'] ?? false ),
			'show_year'         => $show_year,
			'start_date'        => $start_date,
			'end_date'          => $end_date,
			'start_time'        => $start_time,
			'end_time'          => $end_time,
			'is_same_day'       => $start_date === $end_date,
			'is_all_day'        => tribe_event_is_all_day( $event_id ),
			'is_same_start_end' => $start_date === $end_date && $start_time === $end_time,
			'event_id'          => $event_id,
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
		$settings    = $this->get_settings_for_display();
		$date_format = tribe_get_date_format();
		$time_format = tribe_get_time_format();

		$start = new \DateTime();
		$start->modify( '+1 day' );
		$start->setTime( 8, 0 );

		$end = new \DateTime();
		$end->modify( '+2 days' );
		$end->setTime( 17, 0 );

		return [
			'html_tag'          => $this->get_html_tag(),
			'show_header'       => tribe_is_truthy( $settings['show_header'] ?? false ),
			'show_date'         => tribe_is_truthy( $settings['show_date'] ?? true ),
			'show_time'         => tribe_is_truthy( $settings['show_time'] ?? true ),
			'show_year'         => tribe_is_truthy( $settings['show_year'] ?? true ),
			'start_date'        => $start->format( $date_format ) ?? '',
			'end_date'          => $end->format( $date_format ) ?? '',
			'start_time'        => $start->format( $time_format ) ?? '',
			'end_time'          => $end->format( $time_format ) ?? '',
			'is_same_day'       => false,
			'is_all_day'        => false,
			'is_same_start_end' => false,
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
		return _x( 'Date & Time:', 'The header text for the event date and time widget', 'the-events-calendar' );
	}

	/**
	 * Get the class used for the category header.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_header_class() {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the class used for the category header.
		 *
		 * @since TBD
		 *
		 * @param string          $class The class used for the category header.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( 'tec_events_pro_elementor_event_category_widget_header_class', $class, $this );
	}

	/**
	 * Get the HTML tag used for the category header.
	 *
	 * @since TBD
	 */
	public function get_header_tag(): string {
		$settings = $this->get_settings_for_display();

		return (string) $settings['header_tag'] ?? 'h3';
	}

	/**
	 * Get the class used for the datetime separators.
	 *
	 * @since TBD
	 *
	 * @return string The class used for the datetime separators.
	 */
	public function get_separator_class() {
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

		return (string) $settings['html_tag'] ?? 'p';
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
		$this->header_content_section();
		$this->datetime_content_section();
		$this->add_event_query_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel(): void {
		// Styling options.
		$this->style_datetime_header();
		$this->style_datetime_content();
	}

	/**
	 * Add controls for header of the event datetime.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	protected function datetime_content_section(): void {
		$this->start_controls_section(
			'content_section',
			[
				'label' => $this->get_title(),
			]
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

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'html_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'default'   => 'p',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the datetime.
	 *
	 * @since TBD
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
	 * @since TBD
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
			'alignment',
			[
				'id'        => 'align_content',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_widget_class() ],
			]
		);

		$this->end_controls_section();
	}
}
