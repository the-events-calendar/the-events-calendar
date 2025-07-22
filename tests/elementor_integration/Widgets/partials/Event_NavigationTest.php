<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_NavigationTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;
	use With_Post_Remapping;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_navigation_template_data';

	public static $prev_event;
	public static $next_event;

	public function setUp(): void {
		parent::setUp();

		self::$prev_event = $this->mock_event( 'events/single/1.json' )->get();
		self::$next_event = $this->mock_event( 'events/single/2.json' )->get();
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'prev_link' => [
			static function () {
				return [
					'label'  => 'prev_link',
					'value'  => 'http://theeventscalendar.com/prev',
					'string' => 'http://theeventscalendar.com/prev',
					'additional' => [
						'prev_event' => self::$prev_event,
						'next_event' => self::$next_event,
					],
				];
			},
		];
		yield 'next_link' => [
			static function () {
				return [
					'label'  => 'next_link',
					'value'  => 'http://theeventscalendar.com/next',
					'string' => 'http://theeventscalendar.com/next',
					'additional' => [
						'prev_event' => self::$prev_event,
						'next_event' => self::$next_event,
					],
				];
			},
		];
		yield 'no_next_link' => [
			static function () {
				return [
					'label'  => 'next_link',
					'value'  => '',
					'string' => 'class="tec-elementor-event-widget__navigation--next"',
					'invert' => true,
					'additional' => [
						'prev_link'  => 'http://theeventscalendar.com/prev',
						'prev_event' => self::$prev_event,
						'next_event' => self::$next_event,
					],
				];
			},
		];
		yield 'no_prev_link' => [
			static function () {
				return [
					'label'  => 'prev_link',
					'value'  => '',
					'string' => 'class="tec-elementor-event-widget__navigation--previous"',
					'invert' => true,
					'additional' => [
						'prev_event' => self::$prev_event,
						'next_event' => self::$next_event,
						'next_link'  => 'http://theeventscalendar.com/prev',
					],
				];
			},
		];
		yield 'no_next_event' => [
			static function () {
				return [
					'label'  => 'next_event',
					'value'  => null,
					'string' => 'class="tec-elementor-event-widget__navigation--next"',
					'invert' => true,
					'additional' => [
						'prev_link'  => 'http://theeventscalendar.com/prev',
						'next_link'  => 'http://theeventscalendar.com/next',
						'prev_event' => self::$prev_event,
					],
				];
			},
		];
		yield 'no_prev_event' => [
			static function () {
				return [
					'label'  => 'prev_event',
					'value'  => null,
					'string' => 'class="tec-elementor-event-widget__navigation--prev"',
					'invert' => true,
					'additional' => [
						'prev_link'  => 'http://theeventscalendar.com/prev',
						'next_link'  => 'http://theeventscalendar.com/next',
						'next_event' => self::$next_event,
					],
				];
			},
		];
		yield 'no_links' => [
			static function () {
				return [
					'label'  => 'next_link',
					'value'  => '',
					'render' => false,
					'additional' => [
						'prev_link'  => '',
						'prev_event' => self::$prev_event,
						'next_event' => self::$next_event,
					]
				];
			},
		];
		yield 'no_events' => [
			static function () {
				return [
					'label'  => 'next_event',
					'value'  => null,
					'render' => false,
					'additional' => [
						'prev_event' => null,
						'prev_link'  => 'http://theeventscalendar.com/prev',
						'next_link'  => 'http://theeventscalendar.com/next',
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

		$widget = Event_Navigation::class;

		$this->render_filtered( $object, $widget );
	}
}
