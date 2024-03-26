<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_WebsiteTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_website_template_data';

	private $settings_array = [
		'align'        => '',
		'header_tag'     => 'h3',
		'show_heading' => 'yes',
		'link_label'   => '',
		'link_target'  => '',
	];

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			$this->settings_array
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
		yield 'header_tag' => [
            static function () {
                return [
                    'label'  => 'header_tag',
                    'value'  => 'p',
                    'string' => '<p',
					'additional' => [
						'website' => '<a href="http://theeventscalendar.com">link</a>',
					]
                ];
            },
        ];
        yield 'show_heading' => [
            static function () {
                return [
                    'label'  => 'show_heading',
                    'value'  => 'yes',
                    'string' => 'Event Website',
					'additional' => [
						'website' => '<a href="http://theeventscalendar.com">link</a>',
					]
                ];
            },
        ];
		yield 'no_website' => [
			static function () {
				return [
					'label'  => 'website',
					'value'  => '',
					'render' => false
				];
			},
		];
		// @todo: come up with a good way to test the link label and target controls.
	}

	/**
	 * Test render with html filtered.
	 *
	 * @dataProvider test_data_provider
	 */
	public function test_render_filtered( Closure $passed ) {
		$object = $passed();

		if ( isset( $object['override_settings'] ) ) {
			$this->settings_array[ $object['label'] ] = $object['value'];

			$this->set_class_fn_return(
				'Elementor\Controls_Stack',
				'get_settings_for_display',
				$this->settings_array
			);
		}

		$widget = Event_Website::class;

		$this->render_filtered( $object, $widget );
	}
}
