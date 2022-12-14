<?php

namespace TEC\Events\Custom_Tables\V1\Updates;

use Codeception\TestCase\WPTestCase;
use DateTime;
use Tribe\Events\Test\Factories\Event as Event_Factory;
use Tribe__Date_Utils;
use Tribe__Events__Main;

class Post_Save_EventsTest extends WPTestCase {

	public function _setUp() {
		parent::_setUp();
		$_POST              = [];
		$_POST['ecp_nonce'] = wp_create_nonce( Tribe__Events__Main::POSTTYPE );

		add_filter( 'user_has_cap', [ $this, 'user_has_cap_filter' ], 10, 2 );
	}

	public function _tearDown() {
		remove_filter( 'user_has_cap', [ $this, 'user_has_cap_filter' ], 10 );
		$_POST = [];
		parent::_tearDown();
	}

	public function integration_data_provider() {
		// @todo meta_input
		return [
			'Testing UTC-5 with DST'             => [
				[
					'EventStartDate' => '2022-11-01',
					'EventEndDate'   => '2022-11-01',
					'EventTimezone'  => 'UTC-5',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
			'Testing UTC-5 w/out DST'            => [
				[
					'EventStartDate' => '2022-12-12',
					'EventEndDate'   => '2022-12-12',
					'EventTimezone'  => 'UTC-5',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
			'Testing UTC with DST'               => [
				[
					'EventStartDate' => '2022-11-01',
					'EventEndDate'   => '2022-11-01',
					'EventTimezone'  => 'UTC',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
			'Testing UTC w/out DST'              => [
				[
					'EventStartDate' => '2022-12-12',
					'EventEndDate'   => '2022-12-12',
					'EventTimezone'  => 'UTC',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
			'Testing America/New_York with DST'  => [
				[
					'EventStartDate' => '2022-11-01',
					'EventEndDate'   => '2022-11-01',
					'EventTimezone'  => 'America/New_York',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
			'Testing America/New_York w/out DST' => [
				[
					'EventStartDate' => '2022-12-12',
					'EventEndDate'   => '2022-12-12',
					'EventTimezone'  => 'America/New_York',
					'EventStartTime' => '18:45:25',
					'EventEndTime'   => '19:45:25',
				]
			],
		];
	}

	public function user_has_cap_filter( $allcaps, $caps ) {
		$caps['edit_tribe_events'] = true;

		return $caps;
	}

	/**
	 * Should more fully test our integration with Tribe__Events__API and CT1 insertion of events with various data.
	 *
	 * @dataProvider integration_data_provider
	 * @test
	 */
	public function should_update_event_with_no_changes_after_creation( $vars ) {
		$_POST = array_merge( $_POST, $vars );

		// Build objects
		$event_start_date = new DateTime( $_POST['EventStartDate'] . ' ' . $_POST['EventStartTime'] );
		$event_end_date   = new DateTime( $_POST['EventEndDate'] . ' ' . $_POST['EventEndTime'] );

		$event_id = ( new Event_Factory() )->create(
			[
				'when'     => $event_start_date->format( Tribe__Date_Utils::DBDATETIMEFORMAT ),
				'duration' => $event_end_date->format( 'U' ) - $event_start_date->format( 'U' ),
				'timezone' => $_POST['EventTimezone']
			]
		);

		$events  = new Events();
		$updated = $events->update( $event_id );
		$this->assertTrue( $updated );
	}
}
