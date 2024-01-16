<?php

use Tribe__Events__Main as TEC;

class RevisionTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @before
	 */
	public function become_administrator(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * It should not save revision meta on post update
	 *
	 * @test
	 */
	public function should_not_save_revision_meta_on_post_update(): void {
		// Let's make sure revisions are set to be saved after the post insertion per WordPress 6.4.
		if ( function_exists( 'wp_save_post_revision_on_insert' )
		     && ! has_action( 'wp_after_insert_post', 'wp_save_post_revision_on_insert' )
		) {
			add_action( 'wp_after_insert_post', 'wp_save_post_revision_on_insert' );
		}
		// Create an Event.
		$event_id = tribe_events()->set_args( [
			'title'      => 'Test',
			'status'     => 'publish',
			'start_date' => '2020-01-01 10:00:00',
			'duration'   => 2 * HOUR_IN_SECONDS,
		] )->create()->ID;

		// Hook on the action that will save the Event meta to log the sequence of saves as they happen.
		$saved_ids_sequence = [];
		add_action( 'tribe_events_event_save', function ( $event_id ) use ( &$saved_ids_sequence ) {
			$saved_ids_sequence[] = $event_id;
		} );

		// Set the nonce that will allow the update of the Event meta.
		$_POST['ecp_nonce'] = wp_create_nonce( TEC::POSTTYPE );
		// Update the Event: this will trigger the save of a revision **after** the post updates.
		wp_update_post( [ 'ID' => $event_id, 'post_title' => 'Test__Update' ] );

		$data    = array_combine(
			$saved_ids_sequence,
			array_map( fn( $id ) => get_post_field( 'post_type', $id ), $saved_ids_sequence )
		);
		$message = 'Either revisions are not saved after the Event, or they are saved before it;' .
		           ' sequence was: ' . json_encode( $data, JSON_PRETTY_PRINT );
		$this->assertEquals(
			$event_id,
			end( $saved_ids_sequence ),
			$message
		);
	}
}
