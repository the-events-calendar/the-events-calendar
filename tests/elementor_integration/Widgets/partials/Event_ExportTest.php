<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_ExportTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;
	use With_Post_Remapping;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_export_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_defaults();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'show_gcal_link'         => 'yes',
				'show_ical_link'         => 'yes',
				'show_outlook_365_link'  => 'yes',
				'show_outlook_live_link' => 'yes',
			]
		);
	}

	public function set_defaults() {
		$event = $this->mock_event( 'events/single/1.json' )->get();

		// Widget specific filter.
		add_filter(
			'tec_events_elementor_widget_event_id',
			fn() => $event->ID
		);

		// TEC filter as well.
		add_filter(
			'tribe_get_event_before',
			fn() => $event
		);
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'no_show' => [
			static function () {
				return [
					'label'  => 'show',
					'value'  => false,
					'render' => false
				];
			},
		];

		yield 'no_show_gcal_link' => [
			static function () {
				return [
					'label'  => 'show_gcal_link',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__export-dropdown--gcal',
					'invert' => true
				];
			},
		];

		yield 'no_show_ical_link' => [
			static function () {
				return [
					'label'  => 'show_ical_link',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__export-dropdown--ical',
					'invert' => true
				];
			},
		];

		yield 'no_show_outlook_365_link' => [
			static function () {
				return [
					'label'  => 'show_outlook_365_link',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__export-dropdown--outlook_365',
					'invert' => true
				];
			},
		];

		yield 'no_show_outlook_live_link' => [
			static function () {
				return [
					'label'  => 'show_outlook_live_link',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__export-dropdown--outlook_live',
					'invert' => true
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

		$widget = Event_Export::class;

		$this->render_filtered( $object, $widget );
	}
}
