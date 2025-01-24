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
				'link_organizer_email'          => false,
				'link_organizer_phone'          => false,
				'organizer_email_header_tag'    => 'h3',
				'organizer_email_header_text'   => 'Email',
				'organizer_header_tag'          => 'h2',
				'organizer_name_tag'            => 'h2',
				'organizer_phone_header_tag'    => 'h3',
				'organizer_phone_header_text'   => 'Phone',
				'organizer_website_header_tag'  => 'h3',
				'organizer_website_header_text' => 'Website',
				'organizer_website_link_target' => '_self',
				'show_organizer_email_header'   => true,
				'show_organizer_email'          => true,
				'show_organizer_header'         => true,
				'show_organizer_name'           => true,
				'show_organizer_phone_header'   => true,
				'show_organizer_phone'          => true,
				'show_organizer_website_header' => true,
				'show_organizer_website'        => true,
			]
		);

		$this->set_defaults();
	}

	public function set_defaults() {
		$event = $this->mock_event( 'events/single/1.json' )->with_organizers( 1, 'organizers/1.json' )->get();

		add_filter(
			$this->filter,
			function ( $data ) use ( $event ) {
				$data['event_id'] = $event->ID;
				$data['organizers'] = $this->setup_organizers( $event->ID );

				return $data;
			}
		);
	}

	public function setup_organizers( $event_id ) {
		$ids = tribe_get_organizer_ids( $event_id );
		$organizers = [];
		foreach ( $ids as $id ) {
			$organizers[ $id ] = [
				'id'         => $id,
				'name'       => tribe_get_organizer( $id ),
				'phone'      => tribe_get_organizer_phone( $id ),
				'phone_link' => false,
				'website'    => tribe_get_organizer_website_link( $id ),
				'email'      => tribe_get_organizer_email( $id, false ),
			];
		}

		return $organizers;
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'show_organizer_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_header',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-header',
				];
			},
		];
		yield 'no_show_organizer_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_header',
					'value'  => false,
					'string' => '',
				];
			},
		];
		yield 'show_organizer_name' => [
			static function () {
				return [
					'label'  => 'show_organizer_name',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-name',
				];
			},
		];
		yield 'no_show_organizer_name' => [
			static function () {
				return [
					'label'  => 'show_organizer_name',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-name',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_phone' => [
			static function () {
				return [
					'label'  => 'show_organizer_phone',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-phone',
				];
			},
		];
		yield 'no_show_organizer_phone' => [
			static function () {
				return [
					'label'  => 'show_organizer_phone',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-phone',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_email' => [
			static function () {
				return [
					'label'  => 'show_organizer_email',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-email',
				];
			},
		];
		yield 'no_show_organizer_email' => [
			static function () {
				return [
					'label'  => 'show_organizer_email',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-email',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_website' => [
			static function () {
				return [
					'label'  => 'show_organizer_website',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-website',
				];
			},
		];
		yield 'no_show_organizer_website' => [
			static function () {
				return [
					'label'  => 'show_organizer_website',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-website',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_phone_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_phone_header',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-phone-header',
				];
			},
		];
		yield 'no_show_organizer_phone_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_phone_header',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-phone-header',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_email_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_email_header',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-email-header',
				];
			},
		];
		yield 'no_show_organizer_email_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_email_header',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-email-header',
					'invert' => true,
				];
			},
		];
		yield 'show_organizer_website_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_website_header',
					'value'  => true,
					'string' => 'tec-events-elementor-event-widget__organizer-website-header',
				];
			},
		];
		yield 'no_show_website_header' => [
			static function () {
				return [
					'label'  => 'show_organizer_website_header',
					'value'  => false,
					'string' => 'tec-events-elementor-event-widget__organizer-website-header',
					'invert' => true,
				];
			},
		];
		yield 'organizer_header_tag' => [
			static function () {
				return [
					'label'  => 'organizer_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-events-elementor-event-widget__organizer-header" >',
					'additional' => [
						'show_header' => true,
					]
				];
			},
		];
		yield 'organizer_phone_header_tag' => [
			static function () {
				return [
					'label'  => 'organizer_phone_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-events-elementor-event-widget__organizer-phone-header" >',
					'additional' => [
						'show_hone'        => true,
						'show_hone_header' => true,
					]
				];
			},
		];
		yield 'organizer_email_header_tag' => [
			static function () {
				return [
					'label'  => 'organizer_email_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-events-elementor-event-widget__organizer-email-header" >',
					'additional' => [
						'show_email'        => true,
						'show_email_header' => true,
					]
				];
			},
		];
		yield 'organizer_website_header_tag' => [
			static function () {
				return [
					'label'  => 'organizer_website_header_tag',
					'value'  => 'h1',
					'string' => '<h1 class="tec-events-elementor-event-widget__organizer-website-header" >',
					'additional' => [
						'show_website'        => true,
						'show_website_header' => true,
					]
				];
			},
		];
		yield 'organizer_email_header_text' => [
			static function () {
				return [
					'label'  => 'organizer_email_header_text',
					'value'  => 'organizer_email_header_text',
					'string' => 'organizer_email_header_text',
					'additional' => [
						'show_email'        => true,
						'show_email_header' => true,
					]
				];
			},
		];
		yield 'organizer_phone_header_text' => [
			static function () {
				return [
					'label'  => 'organizer_phone_header_text',
					'value'  => 'organizer_phone_header_text',
					'string' => 'organizer_phone_header_text',
					'additional' => [
						'show_phone'        => true,
						'show_phone_header' => true,
					]
				];
			},
		];
		yield 'organizer_website_header_text' => [
			static function () {
				return [
					'label'  => 'organizer_website_header_text',
					'value'  => 'organizer_website_header_text',
					'string' => 'organizer_website_header_text',
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
	 * @dataProvider data_provider
	 */
	public function test_render_filtered( Closure $passed ) {
		$object = $passed();

		$widget = Event_Organizer::class;

		$this->render_filtered( $object, $widget );
	}
}
