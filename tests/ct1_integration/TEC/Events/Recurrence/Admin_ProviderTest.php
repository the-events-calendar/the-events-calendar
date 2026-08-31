<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use WP_Post;

class Admin_ProviderTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model static extensions cache: it may have been locked before the engine registered.
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		unset( $_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ], $_POST[ Admin_Provider::FIELD ] );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Admin Provider Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	private function post_dates( array $rows ): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ] = wp_create_nonce( Admin_Provider::NONCE_ACTION );
		$_POST[ Admin_Provider::FIELD ]                   = $rows;
	}

	/**
	 * It should save valid rows and skip malformed ones
	 *
	 * @test
	 */
	public function should_save_valid_rows_and_skip_malformed_ones(): void {
		$post = $this->given_an_event();

		$this->post_dates(
			[
				[ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ],
				[ 'date' => 'not-a-date', 'start' => '09:00', 'end' => '10:00' ],
				[ 'date' => '2026-11-19', 'start' => '11:00', 'end' => '10:00' ], // End before start.
			]
		);

		tribe( Admin_Provider::class )->save_dates( $post->ID, $post );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 2, $dates );
		$this->assertEquals( '2026-11-12 09:00:00', $dates[1]['start'] );
		$this->assertTrue( tribe_is_recurring_event( $post->ID ) );
		// The Links layer calls this as a Model method: the extension must provide it.
		$this->assertTrue( \TEC\Events\Custom_Tables\V1\Models\Event::find( $post->ID, 'post_id' )->has_recurrence() );
	}

	/**
	 * It should collapse the event when all rows are removed
	 *
	 * @test
	 */
	public function should_collapse_the_event_when_all_rows_are_removed(): void {
		$post = $this->given_an_event();

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID, $post );
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );

		$this->post_dates( [] );
		tribe( Admin_Provider::class )->save_dates( $post->ID, $post );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 1, $dates );
		$this->assertEquals( '2026-11-05 09:00:00', $dates[0]['start'] );
		$this->assertFalse( tribe_is_recurring_event( $post->ID ) );
	}

	/**
	 * It should not touch rule based recurrence data
	 *
	 * @test
	 */
	public function should_not_touch_rule_based_recurrence_data(): void {
		$post = $this->given_an_event();
		$meta = [ 'rules' => [ [ 'type' => 'Weekly' ] ] ];
		update_post_meta( $post->ID, '_EventRecurrence', $meta );

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID, $post );

		$this->assertEquals( $meta, get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should ignore a save without the metabox nonce
	 *
	 * @test
	 */
	public function should_ignore_a_save_without_the_metabox_nonce(): void {
		$post = $this->given_an_event();

		$_POST[ Admin_Provider::FIELD ] = [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ];
		tribe( Admin_Provider::class )->save_dates( $post->ID, $post );

		$this->assertCount( 1, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}
}
