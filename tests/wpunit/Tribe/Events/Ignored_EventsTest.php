<?php

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe\Tests\Traits\With_Uopz;

class Ignored_EventsTest extends Events_TestCase {

	use With_Uopz;

	protected     $ignored_events;
	public static $wp_redirect_params;

	public function setUp(): void {
		parent::setUp();
		$this->ignored_events = new Tribe__Events__Ignored_Events();

		uopz_allow_exit( false );

		$instance_holder = $this;

		$this->set_fn_return(
			'wp_redirect', function ( $location, $status = 302, $x_redirect_by = 'WordPress' ) use ( $instance_holder ) {
			$instance_holder::$wp_redirect_params = [ $location, $status, $x_redirect_by ];
			return true;
		},  true
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		uopz_allow_exit( true );
		self::$wp_redirect_params = null; // Reset the static variable after each test
	}


	/**
	 * @test
	 */
	public function it_should_fail_because_i_do_not_have_permission() {
		$event_id = tribe_events()->set_args(
			[
				'title'        => 'Test',
				'status'       => 'tribe-ignored',
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => get_current_user_id(),
			]
		)->create()->ID;

		$_GET['action']     = 'tribe-restore';
		$_GET['post']       = $event_id;
		$_REQUEST['action'] = 'tribe-restore';
		$_REQUEST['post']   = $event_id;

		// Expect WPDieException to be thrown with the specific message
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'You do not have permission to restore this post.' );

		$this->ignored_events->action_restore_events();
	}

	/**
	 * @test
	 */
	public function it_should_succeed_with_permission() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		// Create an event as the current user
		$event_id = tribe_events()->set_args(
			[
				'title'        => 'Test',
				'status'       => 'tribe-ignored',
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => get_current_user_id(),
			]
		)->create()->ID;

		$_GET['action']     = 'tribe-restore';
		$_GET['post']       = $event_id;
		$_REQUEST['action'] = 'tribe-restore';
		$_REQUEST['post']   = $event_id;

		// Ensure no exception is thrown
		$this->ignored_events->action_restore_events();

		// Add assertions to verify the event is restored
		$restored_event = get_post( $event_id );
		$this->assertNotEquals( 'tribe-ignored', $restored_event->post_status );

		// Assert that wp_redirect was called with the correct parameters
		$this->assertNotEmpty( self::$wp_redirect_params, 'wp_redirect was not called.' );
		$expected_params = [
			'?restored=1',
			302,
			'WordPress',
		];
		$this->assertEquals( $expected_params, self::$wp_redirect_params );
	}
}
