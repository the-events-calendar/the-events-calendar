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

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

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

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

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

	/**
	 * @test
	 */
	public function it_should_fail_for_user_y_without_permission() {
		// Create an event as user X
		$user_x_id = $this->factory->user->create( [ 'role' => 'author' ] );
		$event_id  = tribe_events()->set_args(
			[
				'title'        => 'Test by User X',
				'status'       => 'tribe-ignored',
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => $user_x_id,
			]
		)->create()->ID;

		// Log in as user Y
		$user_y_id = $this->factory->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_y_id );

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		// Expect WPDieException to be thrown with the specific message
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'You do not have permission to restore this post.' );

		$this->ignored_events->action_restore_events();
	}

	/**
	 * @test
	 */
	public function it_should_succeed_for_user_y_with_permission() {
		// Create an event as user X
		$user_x_id = $this->factory->user->create( [ 'role' => 'author' ] );
		$event_id  = tribe_events()->set_args(
			[
				'title'        => 'Test by User X',
				'status'       => 'tribe-ignored',
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => $user_x_id,
			]
		)->create()->ID;

		// Log in as user Y with appropriate permissions
		$user_y_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_y_id );

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

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

	/**
	 * @test
	 */
	public function it_should_fail_for_anonymous_user() {
		// Create an event by an anonymous user
		$event_id = tribe_events()->set_args(
			[
				'title'        => 'Test by Anonymous',
				'status'       => 'tribe-ignored',
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => 0, // 0 for anonymous user
			]
		)->create()->ID;

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		// Expect WPDieException to be thrown with the specific message
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'You do not have permission to restore this post.' );

		$this->ignored_events->action_restore_events();
	}

	/**
	 * @test
	 */
	public function it_should_fail_without_post_id_in_request() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_GET['action']       = 'tribe-restore';
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_0' );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		$restored_events = $this->ignored_events->action_restore_events();

		$this->assertEquals( null, $restored_events );
	}

	/**
	 * @test
	 */
	public function it_should_fail_with_invalid_post_id() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = 999999; // Assuming this post ID does not exist
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . 999999 );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = 999999;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		// Expect WPDieException to be thrown with the specific message
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'You do not have permission to restore this post.' );

		$this->ignored_events->action_restore_events();
	}

	/**
	 * @test
	 */
	public function it_should_fail_with_correct_action_but_incorrect_post_type() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		// Create a post that is not an event
		$post_id = wp_insert_post(
			[
				'post_title'   => 'Non-event post',
				'post_status'  => 'tribe-ignored',
				'post_content' => 'This is a test.',
				'post_author'  => $user_id,
				'post_type'    => 'post', // Not an event type
			]
		);

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $post_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $post_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $post_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		$restored_events = $this->ignored_events->action_restore_events();

		$this->assertEquals( null, $restored_events );
	}

	/**
	 * @test
	 */
	public function it_should_fail_if_event_is_not_ignored() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		// Create an event that is not ignored
		$event_id = tribe_events()->set_args(
			[
				'title'        => 'Active Event',
				'status'       => 'publish', // Not ignored
				'start_date'   => '2050-01-01 09:00:00',
				'end_date'     => '2050-01-01 11:30:00',
				'post_content' => 'testing',
				'post_author'  => $user_id,
			]
		)->create()->ID;

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'restore-post_' . $event_id );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		$restored_events = $this->ignored_events->action_restore_events();

		$this->assertEquals( null, $restored_events );
	}

	/**
	 * @test
	 */
	public function it_should_fail_with_invalid_nonce() {
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

		$_GET['action']       = 'tribe-restore';
		$_GET['post']         = $event_id;
		$_GET['_wpnonce']     = wp_create_nonce( 'invalid-nonce' );
		$_REQUEST['action']   = 'tribe-restore';
		$_REQUEST['post']     = $event_id;
		$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];

		// Expect WPDieException to be thrown with the specific message
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'The link you followed has expired.' );

		$this->ignored_events->action_restore_events();
	}
}
