<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_VenueTest extends WPTestCase {
	use SnapshotAssertions, With_Uopz, Filter_Trait,  With_Post_Remapping;

	/**
	 * The event placeholder.
	 */
	public $event;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_venue_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'header_tag'            => 'p',
				'show_name'             => true,
				'show_header'           => true,
				'show_address'          => true,
				'show_address_map_link' => true,
				'show_map'              => true,
				'show_phone'            => true,
				'show_website'          => true,
				'show_address_header'   => false,
				'show_phone_header'     => false,
				'show_website_header'   => false,
				'header_tag'            => 'h2',
				'name_tag'              => 'h3',
				'address_header_tag'    => 'h3',
				'phone_header_tag'      => 'h3',
				'website_header_tag'    => 'h3',
				'website_link_target'   => '_self',
			]
		);

		$this->set_defaults();
	}

	public function set_defaults() {
		$event = $this->mock_event( 'events/single/1.json' )->with_venue( 'venues/1.json' )->get();

		add_filter(
			'tec_events_elementor_widget_event_id',
			static function () use ( $event ) {
				return 8;
			}
		);

		add_filter(
			$this->filter,
			function ( $data ) use ( $event ) {
				$data['event_id'] = $event->ID;
				$data['venue_ids'] = tec_get_venue_ids( $event->ID );
				$data['venues'] = $this->setup_venues( $event->ID );

				return $data;
			}
		);
	}

	public function setup_venues( $event_id ) {
		$venues = [];

		$venue_ids = tec_get_venue_ids( $event_id );

		foreach ( $venue_ids as $venue_id ) {
			$venue = tribe_get_venue( $venue_id );

			$venues[] = [
				'id'         => $venue_id,
				'name'       => tribe_get_venue( $venue_id ),
				'name_link'  => tribe_get_venue_link( $venue_id, false ),
				'address'    => tribe_get_full_address( $venue_id ),
				'phone'      => tribe_get_phone( $venue_id ),
				'phone_link' => false,
				'map_link'   => tribe_get_map_link_html( $venue_id ),
				'website'    => tribe_get_venue_website_link( $venue_id ),
				'map'        => tribe_get_embedded_map( $venue_id, '100%', '200px' ),
			];
		}

		return $venues;
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'show_name' => [
			static function () {
				return [
					'label'      => 'show_name',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-name',
				];
			},
		];
		yield 'no_show_name' => [
			static function () {
				return [
					'label'      => 'show_name',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-name',
					'invert'	 => true,
				];
			},
		];
		yield 'show_widget_header' => [
			static function () {
				return [
					'label'      => 'show_widget_header',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-header',
				];
			},
		];
		yield 'no_show_widget_header' => [
			static function () {
				return [
					'label'      => 'show_widget_header',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-header',
					'invert'     => true,
				];
			},
		];
		yield 'show_address' => [
			static function () {
				return [
					'label'      => 'show_address',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-address',
				];
			},
		];
		yield 'no_show_address' => [
			static function () {
				return [
					'label'      => 'show_address',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-address',
					'invert'     => true,
				];
			},
		];
		yield 'show_address_map_link' => [
			static function () {
				return [
					'label'      => 'show_address_map_link',
					'value'      => true,
					'string'     => 'tribe-events-gmap',
				];
			},
		];
		yield 'no_show_address_map_link' => [
			static function () {
				return [
					'label'      => 'show_address_map_link',
					'value'      => false,
					'string'     => 'tribe-events-gmap',
					'invert'     => true,
				];
			},
		];
		yield 'show_map' => [
			static function () {
				return [
					'label'      => 'show_map',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-map',
				];
			},
		];
		yield 'no_show_map' => [
			static function () {
				return [
					'label'      => 'show_map',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-map',
					'invert'     => true,
				];
			},
		];
		yield 'show_phone' => [
			static function () {
				return [
					'label'      => 'show_phone',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-phone',
				];
			},
		];
		yield 'no_show_phone' => [
			static function () {
				return [
					'label'      => 'show_phone',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-phone',
					'invert'     => true,
				];
			},
		];
		yield 'show_website' => [
			static function () {
				return [
					'label'      => 'show_website',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-website',
				];
			},
		];
		yield 'no_show_website' => [
			static function () {
				return [
					'label'      => 'show_website',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-website',
					'invert'     => true,
				];
			},
		];
		yield 'show_address_header' => [
			static function () {
				return [
					'label'      => 'show_address_header',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-address-header',
				];
			},
		];
		yield 'no_show_address_header' => [
			static function () {
				return [
					'label'      => 'show_address_header',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-address-header',
					'invert'     => true,
				];
			},
		];
		yield 'show_phone_header' => [
			static function () {
				return [
					'label'      => 'show_phone_header',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-phone-header',
				];
			},
		];
		yield 'no_show_phone_header' => [
			static function () {
				return [
					'label'      => 'show_phone_header',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-phone-header',
					'invert'     => true,
				];
			},
		];
		yield 'show_website_header' => [
			static function () {
				return [
					'label'      => 'show_website_header',
					'value'      => true,
					'string'     => 'tec-events-elementor-event-widget__venue-website-header',
				];
			},
		];
		yield 'no_show_website_header' => [
			static function () {
				return [
					'label'      => 'show_website_header',
					'value'      => false,
					'string'     => 'tec-events-elementor-event-widget__venue-website-header',
					'invert'     => true,
				];
			},
		];
		yield 'header_tag' => [
			static function () {
				return [
					'label'      => 'header_tag',
					'value'      => 'h1',
					'string'     => '<h1',
					'additional' => [
						'show_widget_header' => true,
					]
				];
			},
		];
		yield 'name_tag' => [
			static function () {
				return [
					'label'      => 'name_tag',
					'value'      => 'h1',
					'string'     => '<h1',
					'additional' => [
						'show_name' => true,
					]
				];
			},
		];
		yield 'address_header_tag' => [
			static function () {
				return [
					'label'      => 'address_header_tag',
					'value'      => 'h1',
					'string'     => '<h1',
					'additional' => [
						'show_address'        => true,
						'show_address_header' => true,
					]
				];
			},
		];
		yield 'phone_header_tag' => [
			static function () {
				return [
					'label'      => 'phone_header_tag',
					'value'      => 'h1',
					'string'     => '<h1',
					'additional' => [
						'show_phone'        => true,
						'show_phone_header' => true,
					]
				];
			},
		];
		yield 'website_header_tag' => [
			static function () {
				return [
					'label'      => 'website_header_tag',
					'value'      => 'h1',
					'string'     => '<h1',
					'additional' => [
						'show_website'        => true,
						'show_website_header' => true,
					]
				];
			},
		];
		yield 'header_text' => [
			static function () {
				return [
					'label'      => 'header_text',
					'value'      => 'Header Text',
					'string'     => 'Header Text',
					'additional' => [
						'show_widget_header' => true,
					]
				];
			},
		];
		yield 'address_header_text' => [
			static function () {
				return [
					'label'      => 'address_header_text',
					'value'      => 'address_header_text',
					'string'     => 'address_header_text',
					'additional' => [
						'show_address'        => true,
						'show_address_header' => true,
					]
				];
			},
		];
		yield 'phone_header_text' => [
			static function () {
				return [
					'label'      => 'phone_header_text',
					'value'      => 'phone_header_text',
					'string'     => 'phone_header_text',
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
					'label'      => 'website_header_text',
					'value'      => 'website_header_text',
					'string'     => 'website_header_text',
					'additional' => [
						'show_website'        => true,
						'show_website_header' => true,
					]
				];
			},
		];
		yield 'website_link_target' => [
			static function () {
				return [
					'label'      => 'website_link_target',
					'value'      => '_blank',
					'string'     => 'target="_blank"',
					'additional' => [
						'show_website' => true,
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

		$widget = Event_Venue::class;

		$this->render_filtered( $object, $widget );
	}
}
