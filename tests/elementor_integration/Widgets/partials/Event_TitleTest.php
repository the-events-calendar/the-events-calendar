<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_TitleTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_title_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'html_tag' => 'p', 'title' => 'Event Title' ]
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
					'value'  => 'h2',
					'string' => '<h2',
					'additional' => [
						'title' => 'Backup Title',
					]
				];
			},
		];
		yield 'title' => [
			static function () {
				return [
					'label'  => 'title',
					'value'  => 'Filtered Title',
					'string' => 'Filtered Title',
				];
			},
		];
		yield 'no_title' => [
			static function () {
				return [
					'label'  => 'title',
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

		$widget = Event_Title::class;

		$this->render_filtered( $object, $widget );
	}
}
