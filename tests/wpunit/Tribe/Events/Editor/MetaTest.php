<?php

namespace Tribe\Events\Editor;

use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Editor__Meta as Meta;
use Tribe__Events__Main as TEC;
use WP_REST_Request;

class MetaTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();

		$this->factory()->event = new Event();

		/*
		 * The editor provider only registers the meta when blocks load, which the suite
		 * does not guarantee.
		 */
		( new Meta() )->register();

		update_option( 'timezone_string', 'America/New_York' );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function timezone_provider(): array {
		return [
			'valid identifier'       => [ 'Europe/Paris', 'Europe/Paris' ],
			'valid utc offset'       => [ 'UTC+5', 'UTC+5' ],
			'markup'                 => [ '<img src="x" onerror="alert(1)">', 'America/New_York' ],
			'utc prefixed attribute' => [ 'UTC+5" onerror="alert(1)', 'America/New_York' ],
			'not a timezone'         => [ 'Not/A_Timezone', 'America/New_York' ],
		];
	}

	/**
	 * It should only store a real timezone identifier or UTC offset saved through the REST API.
	 *
	 * @test
	 * @dataProvider timezone_provider
	 */
	public function should_only_store_a_valid_timezone_saved_through_rest( string $timezone, string $expected ) {
		$event_id = $this->factory()->event->create();

		$request = new WP_REST_Request( 'POST', '/wp/v2/' . TEC::POSTTYPE . '/' . $event_id );
		$request->set_body_params( [ 'meta' => [ '_EventTimezone' => $timezone ] ] );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $expected, get_post_meta( $event_id, '_EventTimezone', true ) );
	}
}
