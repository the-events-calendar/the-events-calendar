<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Admin_Provider;
use TEC\Events\Recurrence\Authoring_Guard;
use TEC\Events\Recurrence\Settings;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Rules_Conversion_RequestTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * The URL captured from the last prevented redirect, or `null` when none fired.
	 *
	 * Static, captured by a static method: closure callbacks leak across tests in this suite.
	 *
	 * @var string|false|null
	 */
	public static $redirected_to;

	public static function capture_redirect( $location ) {
		self::$redirected_to = $location;

		return false;
	}

	/**
	 * @before
	 */
	public function prepare_request(): void {
		self::$redirected_to = null;
		add_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		add_filter( 'tribe_exit', static fn() => '__return_true' );
		tribe_update_option( Settings::LOCK_OPTION, false );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_SERVER['REQUEST_METHOD'] = 'POST';
	}

	/**
	 * @after
	 */
	public function cleanup_request(): void {
		remove_all_filters( 'tribe_exit' );
		remove_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		tribe_remove_option( Settings::LOCK_OPTION );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		delete_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		foreach ( [ 'action', Rules_Conversion_Request::POST_FIELD, '_wpnonce', Rules_Conversion_Request::ACK_FIELD ] as $key ) {
			unset( $_POST[ $key ], $_REQUEST[ $key ] );
		}
		unset( $_SERVER['REQUEST_METHOD'] );
		wp_set_current_user( 0 );
	}

	private function given_a_rule_locked_event(): WP_Post {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
				[ 'start' => '2050-01-17 09:00:00', 'end' => '2050-01-17 10:00:00' ],
			],
			[
				'start_date' => '2050-01-03 09:00:00',
				'end_date'   => '2050-01-03 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		);
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500103T090000\nRRULE:FREQ=WEEKLY;COUNT=3;BYDAY=MO" ]
		);

		return $post;
	}

	private function post( int $post_id, bool $ack = true, ?string $nonce = null, ?int $nonce_post_id = null ): void {
		// The nonce is minted for the real Event the edit screen shows.
		$fields                                     = Rules_Conversion_Request::get_form_fields( $nonce_post_id ?? $post_id );
		$fields[ Rules_Conversion_Request::POST_FIELD ] = (string) $post_id;

		if ( null !== $nonce ) {
			$fields['_wpnonce'] = $nonce;
		}

		if ( $ack ) {
			$fields[ Rules_Conversion_Request::ACK_FIELD ] = '1';
		}

		foreach ( $fields as $key => $value ) {
			$_POST[ $key ]    = $value;
			$_REQUEST[ $key ] = $value;
		}
	}

	private function notice(): array {
		$notice = get_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		$this->assertIsArray( $notice, 'A notice must be left for the user.' );

		return $notice;
	}

	/**
	 * It should register only without pro
	 *
	 * @test
	 */
	public function should_register_the_handler_with_the_admin_provider(): void {
		$request = tribe( Rules_Conversion_Request::class );

		$this->assertEquals( 10, has_action( 'admin_post_' . Rules_Conversion_Request::ACTION, [ $request, 'handle' ] ) );

		tribe( Admin_Provider::class )->unregister();
		$this->assertFalse( has_action( 'admin_post_' . Rules_Conversion_Request::ACTION, [ $request, 'handle' ] ) );

		tribe( Admin_Provider::class )->register();
		$this->assertEquals( 10, has_action( 'admin_post_' . Rules_Conversion_Request::ACTION, [ $request, 'handle' ] ) );
	}

	/**
	 * It should expose the form fields
	 *
	 * @test
	 */
	public function should_expose_the_form_fields(): void {
		$fields = Rules_Conversion_Request::get_form_fields( 23 );

		$this->assertEquals( Rules_Conversion_Request::ACTION, $fields['action'] );
		$this->assertEquals( '23', $fields[ Rules_Conversion_Request::POST_FIELD ] );
		$this->assertNotFalse( wp_verify_nonce( $fields['_wpnonce'], Rules_Conversion_Request::NONCE_ACTION . '23' ) );
		$this->assertArrayNotHasKey( Rules_Conversion_Request::ACK_FIELD, $fields );
		$this->assertStringEndsWith( 'admin-post.php', Rules_Conversion_Request::get_action_url() );
	}

	/**
	 * It should convert and redirect to the event edit screen with a success notice
	 *
	 * @test
	 */
	public function should_convert_and_redirect_to_the_event_edit_screen_with_a_success_notice(): void {
		$post = $this->given_a_rule_locked_event();
		$this->post( $post->ID );

		tribe( Rules_Conversion_Request::class )->handle();

		$this->assertFalse( tribe( Authoring_Guard::class )->is_rule_locked( $post->ID ) );
		$this->assertEquals( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), self::$redirected_to );
		$notice = $this->notice();
		$this->assertEquals( 'success', $notice['type'] );
		$this->assertStringContainsString( '3 scheduled dates were kept', $notice['message'] );
		$this->assertStringNotContainsString( 'Series', $notice['message'] );
	}

	/**
	 * It should refuse without the acknowledgment
	 *
	 * @test
	 */
	public function should_refuse_without_the_acknowledgment(): void {
		$post = $this->given_a_rule_locked_event();
		$this->post( $post->ID, false );

		tribe( Rules_Conversion_Request::class )->handle();

		$this->assertTrue( tribe( Authoring_Guard::class )->is_rule_locked( $post->ID ) );
		$this->assertEquals( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), self::$redirected_to );
		$notice = $this->notice();
		$this->assertEquals( 'error', $notice['type'] );
		$this->assertStringContainsString( 'Confirm', $notice['message'] );
	}

	/**
	 * It should redirect with an error notice on a bad nonce
	 *
	 * @test
	 */
	public function should_redirect_with_an_error_notice_on_a_bad_nonce(): void {
		$post = $this->given_a_rule_locked_event();
		$this->post( $post->ID, true, 'stale' );

		tribe( Rules_Conversion_Request::class )->handle();

		$this->assertTrue( tribe( Authoring_Guard::class )->is_rule_locked( $post->ID ) );
		$this->assertEquals( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), self::$redirected_to );
		$this->assertStringContainsString( 'security check failed', $this->notice()['message'] );
	}

	/**
	 * It should convert through a provisional id and redirect to the real event
	 *
	 * @test
	 */
	public function should_convert_through_a_provisional_id_and_redirect_to_the_real_event(): void {
		$post       = $this->given_a_rule_locked_event();
		$occurrence = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date', 'DESC' )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		$this->post( $provisional_id, true, null, $post->ID );

		tribe( Rules_Conversion_Request::class )->handle();

		$this->assertFalse( tribe( Authoring_Guard::class )->is_rule_locked( $post->ID ) );
		$this->assertEquals( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), self::$redirected_to );
	}

	/**
	 * It should report an already converted event as info
	 *
	 * @test
	 */
	public function should_report_an_already_converted_event_as_info(): void {
		$post = $this->given_a_multi_date_event();
		$this->post( $post->ID );

		tribe( Rules_Conversion_Request::class )->handle();

		$notice = $this->notice();
		$this->assertEquals( 'info', $notice['type'] );
		$this->assertStringContainsString( 'already uses individual dates', $notice['message'] );
	}

	/**
	 * It should die without the edit post capability
	 *
	 * @test
	 */
	public function should_die_without_the_edit_post_capability(): void {
		$post = $this->given_a_rule_locked_event();
		$this->post( $post->ID );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'subscriber' ] ) );

		$this->expectException( \WPDieException::class );

		tribe( Rules_Conversion_Request::class )->handle();
	}

	/**
	 * It should die on a get request
	 *
	 * @test
	 */
	public function should_die_on_a_get_request(): void {
		$post = $this->given_a_rule_locked_event();
		$this->post( $post->ID );
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->expectException( \WPDieException::class );

		tribe( Rules_Conversion_Request::class )->handle();
	}
}
