<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_CostTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_cost_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'header_tag' => 'p',
				'cost' => '$10'
			]
		);
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'header_tag' => [
			static function () {
				return [
					'label'  => 'header_tag',
					'value'  => 'span',
					'string' => '<span',
					'additional' => [
						'cost'        => '$10',
						'show_header' => true,
					]
				];
			},
		];
		yield 'cost' => [
			static function () {
				return [
					'label'  => 'cost',
					'value'  => '$20.00',
					'string' => '$20.00',
				];
			},
		];
		yield 'cost_range' => [
			static function () {
				return [
					'label'  => 'cost',
					'value'  => '$10.00 - $100.00',
					'string' => '$10.00 - $100.00',
				];
			},
		];
		yield 'free_cost_range' => [
			static function () {
				return [
					'label'  => 'cost',
					'value'  => 'Free - $100.00',
					'string' => 'Free - $100.00',
				];
			},
		];
		yield 'no_cost' => [
			static function () {
				return [
					'label'  => 'cost',
					'value'  => '',
					'render' => false,
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

		$widget = Event_Cost::class;

		$this->render_filtered( $object, $widget );
	}
}
