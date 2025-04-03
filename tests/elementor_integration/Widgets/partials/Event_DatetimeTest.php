<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Datetime;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Tests\Traits\With_Uopz;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_DatetimeTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;
	use With_Post_Remapping;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_datetime_template_data';


	protected Abstract_Widget $widget;

	/**
	 * @before
	 */
	public function enable_widget_hooks(): void {
		// We're not using an existing WP_Post, so let's provide a title.
		add_filter( 'tec_events_elementor_widget_event_datetime_template_data', [ $this, 'mock_data' ], 0 );
		$this->widget = new Event_Datetime();

		$event = $this->mock_event( 'events/single/1.json' )->get();

		$this->widget->get_template()->set_event( $event );

		// Widget specific filter.
		add_filter(
			'tec_events_elementor_widget_event_id',
			static fn() => $event->ID
		);

		$this->set_fn_return(
			\Elementor\Controls_Stack::class,
			'get_settings_for_display',
			[
				'html_tag'      => 'p',
				'show_year'     => false,
				'show_date'     => true,
				'show_time'     => true,
				'show_timezone' => false,
			],
			false
		);
	}

	/**
	 * @after
	 */
	public function disable_widget_hooks(){
		remove_filter( 'tec_events_elementor_widget_event_datetime_template_data', [ $this, 'mock_data' ], 0 );

		//unset( $this->widget );

		$this->unset_uopz_functions();
	}

	public function mock_data( $data ) {
		return [
			'html_tag'          => 'p', // default <p>
			'widget'            => $this->widget, // needs to be an object
			'show_header'       => false, // default: false
			'show_date'         => true,
			'show_time'         => true,
			'show_year'         => false, // not used in template
			'start_date'        => 'July 4', // default: F j
			'end_date'          => 'July 5',
			'start_time'        => '8:00 PM', // default: g:i A
			'end_time'          => '10:00 AM',
			'is_same_day'       => false,
			'is_all_day'        => false,
			'all_day_text'      => 'All day',
			'is_same_start_end' => false,
			'show_timezone'     => false,
			'time_zone_label'   => 'EDT',
		];
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'html_tag' => [
			static function () {
				return [
					'label'  => 'html_tag',
					'value'  => 'div',
					'string' => '<div',
				];
			},
		];
		yield 'show_date'         => [
			static function () {
				return [
					'label'  => 'show_date',
					'value'  => false,
					'string' => '',
				];
			},
		];
		yield 'show_time'         => [
			static function () {
				return [
					'label'  => 'show_time',
					'value'  => false,
					'string' => '',
				];
			},
		];
		yield 'show_timezone'     => [
			static function () {
				return [
					'label'  => 'show_timezone',
					'value'  => true,
					'string' => 'EDT',
				];
			},
		];
		yield 'start_date'        => [
			static function () {
				return [
					'label'  => 'start_date',
					'value'  => 'July 3',
					'string' => 'July 3',
				];
			},
		];
		yield 'end_date'          => [
			static function () {
				return [
					'label'  => 'end_date',
					'value'  => 'July 6',
					'string' => 'July 6',
					'additional' => [
						'is_same_day' => false,
					]
				];
			},
		];
		yield 'start_time'        => [
			static function () {
				return [
					'label'  => 'start_time',
					'value'  => '9:00 AM',
					'string' => '9:00 AM',
				];
			},
		];
		yield 'end_time'          => [
			static function () {
				return [
					'label'  => 'end_time',
					'value'  => '9:00 AM',
					'string' => '9:00 AM',
				];
			},
		];
		yield 'is_same_day'       => [
			static function () {
				return [
					'label'  => 'is_same_day',
					'value'  => false,
					'string' => 'All day',
					'invert' => true,
					'additional' => [
						'end_date'   => 'July 4',
						'end_time'   => '10:00 AM',
						'is_all_day' => false,
						'start_date' => 'July 5',
						'start_time' => '8:00 AM',
					]
				];
			},
		];
		yield 'is_all_day'        => [
			static function () {
				return [
					'label'  => 'is_all_day',
					'value'  => true,
					'string' => 'AM', // We don't show specific text, but we do hide the time.
					'invert' => true,
					'additional' => [
						'end_date'    => 'July 4',
						'end_time'    => '10:00 AM',
						'is_same_day' => true,
						'start_date'  => 'July 4',
						'start_time'  => '8:00 AM',
					]
				];
			},
		];
		yield 'is_same_start_end' => [
			static function () {
				return [
					'label'  => 'is_same_start_end',
					'value'  => true,
					'string' => 'tec-events-pro-schedule__separator--time',
					'invert' => true,
					'additional' => [
						'end_date'    => 'July 4',
						'end_time'    => '10:00 AM',
						'is_all_day'  => true,
						'is_same_day' => true,
						'start_date'  => 'July 4',
						'start_time'  => '10:00 AM',
					]
				];
			},
		];
	}

	/**
	 * Test render with html filtered.
	 *
	 * @dataProvider data_provider
	 */
	public function test_render_filtered( Closure $passed ) {
		$object = $passed();

		$this->render_filtered( $object, $this->widget );
	}
}
