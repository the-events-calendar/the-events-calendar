<?php

namespace Tribe\Events\Aggregator;

use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Record__Abstract as Record_Abstract;
use Tribe__Events__Aggregator__Record__gCal as Gcal_Record;
use Tribe__Events__Aggregator__Record__Url as Url_Record;
use Tribe__Events__Aggregator__Records as Records;

/**
 * Tests for scheduled import edit authorization (object-level checks on record ID + origin).
 */
class Scheduled_Record_Edit_AccessTest extends \Codeception\TestCase\WPTestCase {

	use RecordMaker;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		wp_set_current_user( 1 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function tearDown() {
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	/**
	 * Request record stub: only `origin` is used by validate_scheduled_record_edit_access().
	 *
	 * @return Gcal_Record
	 */
	private function make_gcal_request_record_stub() {
		return new class() extends Gcal_Record {
			public function __construct() {
				// Skip parent to avoid loading a post; origin is declared on the parent class.
			}
		};
	}

	/**
	 * @return Url_Record
	 */
	private function make_url_request_record_stub() {
		return new class() extends Url_Record {
			public function __construct() {
				// Skip parent to avoid loading a post; origin is declared on the parent class.
			}
		};
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_when_post_id_is_zero() {
		$sut    = Records::instance();
		$result = $sut->validate_scheduled_record_edit_access( 0, $this->make_gcal_request_record_stub() );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-invalid-record-id', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_when_post_is_not_an_aggregator_record() {
		$page_id = $this->factory()->post->create(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$sut    = Records::instance();
		$result = $sut->validate_scheduled_record_edit_access( $page_id, $this->make_gcal_request_record_stub() );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-invalid-record', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_when_record_is_manual_not_schedule() {
		$manual = $this->make_manual_record( 'manual-not-schedule', [], 'pending' );

		$sut    = Records::instance();
		$result = $sut->validate_scheduled_record_edit_access( $manual->id, $this->make_gcal_request_record_stub() );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-not-scheduled-record', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_when_request_origin_does_not_match_record() {
		$scheduled = $this->make_schedule_record( 'origin-mismatch', [] );

		$sut    = Records::instance();
		$result = $sut->validate_scheduled_record_edit_access( $scheduled->id, $this->make_url_request_record_stub() );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-record-origin-mismatch', $result->get_error_code() );
	}

	/**
	 * Happy path: authenticated users who may edit the record get `true` from validation, and `save()` succeeds.
	 *
	 * @test
	 */
	public function it_should_allow_validation_and_save_for_authenticated_users_authorized_to_edit() {
		$scheduled = $this->make_schedule_record( 'happy-path-auth', [] );
		$record_id = (int) $scheduled->id;
		$stub      = $this->make_gcal_request_record_stub();
		$sut       = Records::instance();

		// Primary admin (user 1 from setUp): logged in and explicitly allowed to edit this post.
		$this->assertTrue( is_user_logged_in() );
		$this->assertTrue( current_user_can( 'edit_post', $record_id ), 'Primary admin should pass edit_post for the scheduled record.' );

		$validation = $sut->validate_scheduled_record_edit_access( $record_id, $stub );
		$this->assertFalse( is_wp_error( $validation ), 'Validation should not return WP_Error for an authorized user.' );
		$this->assertSame( true, $validation, 'validate_scheduled_record_edit_access should return true for an authorized user.' );

		// Another administrator should also pass (typical multi-admin / handoff scenario).
		$other_admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $other_admin_id );
		$this->assertTrue( current_user_can( 'edit_post', $record_id ), 'Second administrator should pass edit_post for the same record.' );
		$validation_second = $sut->validate_scheduled_record_edit_access( $record_id, $stub );
		$this->assertFalse( is_wp_error( $validation_second ), 'Second admin validation should not return WP_Error.' );
		$this->assertSame( true, $validation_second );

		// save() must complete when the ID matches the scheduled record and the user is authorized.
		wp_set_current_user( 1 );
		$meta = array_merge(
			$scheduled->meta,
			[
				'type' => 'schedule',
			]
		);

		$save_result = $scheduled->save( $record_id, [], $meta );
		$this->assertFalse( is_wp_error( $save_result ), 'save() should not return WP_Error for a valid authorized edit.' );
		$this->assertInstanceOf( Record_Abstract::class, $save_result );
		$this->assertSame( $record_id, (int) $save_result->id );
		$this->assertTrue( $save_result->is_schedule );
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_when_user_cannot_edit_the_record() {
		$scheduled = $this->make_schedule_record( 'subscriber-denied', [] );

		$subscriber_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber_id );

		$sut    = Records::instance();
		$result = $sut->validate_scheduled_record_edit_access( $scheduled->id, $this->make_gcal_request_record_stub() );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-cannot-edit-record', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function it_should_return_wp_error_from_save_when_post_id_targets_non_record() {
		$scheduled = $this->make_schedule_record( 'save-non-record-target', [] );
		$page_id   = $this->factory()->post->create(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$meta = array_merge(
			$scheduled->meta,
			[
				'type' => 'schedule',
			]
		);

		$result = $scheduled->save( $page_id, [], $meta );

		$this->assertWPError( $result );
		$this->assertEquals( 'tribe-ea-invalid-record', $result->get_error_code() );
	}
}
