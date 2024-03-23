<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Tests\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_DescriptionTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_description_template_data';

	public function setUp(): void {
		parent::setUp();

		// Ensure the function gets a string to return.
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'header_tag' => 'Event Description',
				'event_description_content' => '<p>Default Content</p>',
			]
		);
	}

	public function _tearDown(){
		$this->unset_uopz_returns();

		parent::_tearDown();
	}

	/**
	 * Data provider for tests.
	 */
	public function test_data_provider(): Generator {
		yield 'content' => [
			static function () {
				return [
					'label'  => 'content',
					'value'  => '<p>Filtered Content</p>',
					'string' => '<p>Filtered Content</p>',
				];
			},
		];
		yield 'no_content' => [
			static function () {
				return [
					'label'  => 'content',
					'value'  => '',
					'render' => false,
				];
			},
		];
	}

	/**
	 * Test render with html filtered.
	 *
	 * @dataProvider test_data_provider
	 */
	public function test_render_filtered( Closure $passed ) {
		$object = $passed();

		$widget = Event_Description::class;

		$this->render_filtered( $object, $widget );
	}
}
