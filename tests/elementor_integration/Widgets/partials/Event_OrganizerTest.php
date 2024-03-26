<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_OrganizerTest extends WPTestCase {
	use SnapshotAssertions, With_Uopz, Filter_Trait,  With_Post_Remapping;

	/**
	 * The event placeholder.
	 */
	public $event;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_organizer_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'show_organizer_header'         => true,
				'link_organizer_name'           => true,
				'show_organizer_name'           => true,
				'show_organizer_phone'          => true,
				'show_organizer_email'          => true,
				'show_organizer_website'        => true,
				'show_organizer_phone_header'   => true,
				'show_organizer_email_header'   => true,
				'show_organizer_website_header' => true,
				'organizer_header_tag'          => 'h2',
				'organizer_phone_header_tag'    => 'h3',
				'organizer_email_header_tag'    => 'h3',
				'organizer_website_header_tag'  => 'h3',
				'organizer_website_link_target' => '_self',
			]
		);

		$this->set_defaults();
	}

	public function _tearDown(){
		$this->unset_uopz_returns();

		parent::_tearDown();
	}

	public function set_defaults() {
		$event = $this->mock_event( 'events/single/1.json' )->with_organizers( 1, 'organizers/1.json' )->get();

		add_filter(
			$this->filter,
			function ( $data ) use ( $event ) {
				$data['event_id'] = $event->ID;
				$data['organizer_ids'] = tribe_get_organizer_ids( $event->ID );
				return $data;
			}
		);
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function test_data_provider(): Generator {
		yield 'show_header' => [
			static function () {
				return [
					'label'  => 'show_header',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-header',
				];
			},
		];
		yield 'no_show_header' => [
			static function () {
				return [
					'label'  => 'show_header',
					'value'  => false,
					'string' => '',
				];
			},
		];
		yield 'link_name' => [
			static function () {
				return [
					'label'  => 'link_name',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-name-link',
				];
			},
		];
		yield 'no_link_name' => [
			static function () {
				return [
					'label'  => 'link_name',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-name-link',
					'invert' => true,
				];
			},
		];
		yield 'show_name' => [
			static function () {
				return [
					'label'  => 'show_name',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-name',
				];
			},
		];
		yield 'no_show_name' => [
			static function () {
				return [
					'label'  => 'show_name',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-name',
					'invert' => true,
				];
			},
		];
		yield 'show_phone' => [
			static function () {
				return [
					'label'  => 'show_phone',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-phone',
				];
			},
		];
		yield 'no_show_phone' => [
			static function () {
				return [
					'label'  => 'show_phone',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-phone',
					'invert' => true,
				];
			},
		];
		yield 'show_email' => [
			static function () {
				return [
					'label'  => 'show_email',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-email',
				];
			},
		];
		yield 'no_show_email' => [
			static function () {
				return [
					'label'  => 'show_email',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-email',
					'invert' => true,
				];
			},
		];
		yield 'show_website' => [
			static function () {
				return [
					'label'  => 'show_website',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-website',
				];
			},
		];
		yield 'no_show_website' => [
			static function () {
				return [
					'label'  => 'show_website',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-website',
					'invert' => true,
				];
			},
		];
		yield 'show_phone_header' => [
			static function () {
				return [
					'label'  => 'show_phone_header',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-phone-label',
				];
			},
		];
		yield 'no_show_phone_header' => [
			static function () {
				return [
					'label'  => 'show_phone_header',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-phone-label',
					'invert' => true,
				];
			},
		];
		yield 'show_email_header' => [
			static function () {
				return [
					'label'  => 'show_email_header',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-email-label',
				];
			},
		];
		yield 'no_show_email_header' => [
			static function () {
				return [
					'label'  => 'show_email_header',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-email-label',
					'invert' => true,
				];
			},
		];
		yield 'show_website_header' => [
			static function () {
				return [
					'label'  => 'show_website_header',
					'value'  => true,
					'string' => 'tec-elementor-event-widget__organizer-website-label',
				];
			},
		];
		yield 'no_show_website_header' => [
			static function () {
				return [
					'label'  => 'show_website_header',
					'value'  => false,
					'string' => 'tec-elementor-event-widget__organizer-website-label',
					'invert' => true,
				];
			},
		];
		yield 'header_tag' => [
			static function () {
				return [
					'label'  => 'header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tribe-events-single-section-title tec-elementor-event-widget__organizer-header" >',
					'additional' => [
						'show_header' => true,
					]
				];
			},
		];
		yield 'phone_header_tag' => [
			static function () {
				return [
					'label'  => 'phone_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-elementor-event-widget__organizer-phone-label" >',
					'additional' => [
						'show_hone'        => true,
						'show_hone_header' => true,
					]
				];
			},
		];
		yield 'email_header_tag' => [
			static function () {
				return [
					'label'  => 'email_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-elementor-event-widget__organizer-email-label" >',
					'additional' => [
						'show_email'        => true,
						'show_email_header' => true,
					]
				];
			},
		];
		yield 'website_header_tag' => [
			static function () {
				return [
					'label'  => 'website_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-elementor-event-widget__organizer-website-label" >',
					'additional' => [
						'show_website'        => true,
						'show_website_header' => true,
					]
				];
			},
		];
		yield 'email_header_text' => [
			static function () {
				return [
					'label'  => 'email_header_text',
					'value'  => 'email_header_text',
					'string' => 'email_header_text',
					'additional' => [
						'show_email'        => true,
						'show_email_header' => true,
					]
				];
			},
		];
		yield 'phone_header_text' => [
			static function () {
				return [
					'label'  => 'phone_header_text',
					'value'  => 'phone_header_text',
					'string' => 'phone_header_text',
					'additional' => [
						'show_phone'        => true,
						'show_phone_header' => true,
					]
				];
			},
		];
		yield 'website_header_text' => [
			static function () {
				return [
					'label'  => 'website_header_text',
					'value'  => 'website_header_text',
					'string' => 'website_header_text',
					'additional' => [
						'show_website'        => true,
						'show_website_header' => true,
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

		$widget = Event_Organizer::class;

		$this->render_filtered( $object, $widget );
	}
}
