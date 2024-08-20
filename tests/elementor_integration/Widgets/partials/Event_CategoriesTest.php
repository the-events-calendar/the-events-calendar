<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_CategoriesTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_categories_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'show_heading' => true ]
		);
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'heading_tag' => [
			static function () {
				return [
					'label'  => 'heading_tag',
					'value'  => 'h4',
					'string' => '<h4',
					'additional' => [
						'show_heading' => true,
						'header_tag'   => 'h4',
						'categories'   => 'not_empty',
					],
				];
			},
		];
		yield 'no_categories' => [
			static function () {
				return [
					'label'  => 'categories',
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

		$widget = Event_Categories::class;

		$this->render_filtered( $object, $widget );
	}
}
