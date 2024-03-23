<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Tests\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;

use function Crontrol\Event\add;

class Event_TagsTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_tags_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'show_heading' => true,
				'heading_tag' => 'h3',
			]
		);
	}

	public function _tearDown(){
		$this->unset_uopz_returns();

		parent::_tearDown();
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function test_data_provider(): Generator {
		yield 'no_tags' => [
			static function () {
				return [
					'label'  => 'tags',
					'value'  => '',
					'render' => false
				];
			},
		];
		yield 'tags' => [
			static function () {
				return [
					'label'  => 'tags',
					'value'  => [ 'filtered_tag' => 'http://filtered.com' ],
					'string' => 'filtered_tag',
					'additional' => [
						'event_id'    => 5096,
						'settings'     => [],
						'show_heading' => true,
						'heading_tag'  => 'h3',
						'label_text'   => 'Event Tags:',
					]
				];
			},
		];
		yield 'heading_text' => [
			static function () {
				return [
					'label'  => 'label_text',
					'value'  => 'Filtered Title',
					'string' => 'Filtered Title',
					'additional' => [
						'event_id'    => 5096,
						'settings'     => [],
						'show_heading' => true,
						'heading_tag'  => 'h3',
						'tags'         => [
							'example_tag' => 'http://example.com',
						]
					]
				];
			},
		];
		yield 'heading_tag' => [
			static function () {
				return [
					'label' => 'heading_tag',
					'value'  => 'h4',
					'string' => '<h4',
					'additional' => [
						'event_id'    => 5096,
						'settings'     => [],
						'show_heading' => true,
						'label_text'   => 'Event Tags:',
						'tags' => [
							'example_tag' => 'http://example.com',
						]
					]
				];
			},
		];
		yield 'no_header' => [
			static function () {
				return [
					'label'  => 'show_heading',
					'value'  => false,
					'string' => 'Event Tags:',
					'invert' => true,
					'additional' => [
						'event_id'    => 5096,
						'settings'     => [],
						'heading_tag' => 'h3',
						'label_text'  => 'Event Tags:',
						'tags'        => [
							'example_tag' => 'http://example.com',
						]
					]
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

		$widget = Event_Tags::class;

		$this->render_filtered( $object, $widget );
	}
}
