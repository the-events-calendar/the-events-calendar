<?php

namespace Tribe\Events\Test\Traits\CT1;

use TEC\Events\Custom_Tables\V1\Models\Event as Event_Model;
use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use Tribe\Events\Test\Factories\Event as Event_Factory;

trait CT1_Fixtures {
	/**
	 * @return \WP_Post
	 */
	private function given_a_non_migrated_single_event() {
		// Create an Event.
		$post_id = ( new Event_Factory )->create();
		// Make sure no models are present in the custom tables for it.
		Occurrence_Model::where( 'post_id', '=', $post_id )
		                ->delete();
		Event_Model::where( 'post_id', '=', $post_id )
		           ->delete();
		$this->assertNull( Event_Model::find( $post_id, 'post_id' ) );
		$this->assertNull( Occurrence_Model::find( $post_id, 'post_id' ) );
		// Just in case, remove any recurrence meta there might be.
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_rules' );
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_exclusions' );
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_description' );

		return get_post( $post_id );
	}
}