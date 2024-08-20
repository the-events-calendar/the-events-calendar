<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_WebsiteTest extends WPTestCase {
	use SnapshotAssertions, With_Uopz, Filter_Trait, With_Post_Remapping;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_website_template_data';

	private $settings_array = [
		'show_website_header' => 'yes',
		'header_tag'          => 'h3',
		'header_class'        => 'tec-events-elementor-event-widget__website-header',
		'link_class'          => 'tec-events-elementor-event-widget__website-link',
	];

	public function setUp(): void {
		parent::setUp();

		$event = $this->mock_event( 'events/single/website.json' );

		add_filter(
			'tec_events_elementor_widget_event_id',
			static function () use ( $event ) {
				return 13;
			}
		);

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			$this->settings_array
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
                    'value'  => 'p',
                    'string' => '<p',
					'additional' => [
						'show_website_header' => 'yes',
					]
                ];
            },
        ];
        yield 'show_website_header' => [
            static function () {
                return [
                    'label'  => 'show_website_header',
                    'value'  => 'yes',
                    'string' => 'tec-events-elementor-event-widget__website-header',
                ];
            },
        ];
		// @todo: come up with a good way to test the link label and target controls.
	}

	/**
	 * Test render with html filtered.
	 *
	 * @dataProvider data_provider
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
