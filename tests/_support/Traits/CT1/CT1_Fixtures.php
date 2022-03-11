<?php

namespace Tribe\Events\Test\Traits\CT1;

use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Models\Event as Event_Model;
use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Tables\Provider;
use Tribe__Date_Utils as Dates;
use Tribe__Settings_Manager as Options;
use Tribe__Timezones as Timezones;
use Tribe__Events__Main as TEC;

trait CT1_Fixtures {

	/**
	 * Reset the activation flags, and remove CT1 tables. We want to simulate no activation having been done yet.
	 */
	public function given_a_reset_activation() {
		global $wpdb;
		// Ditch our CT1 schema.
		tribe( Provider::class )->drop_tables();

		// Reset state in the db.
		delete_transient( Activation::ACTIVATION_TRANSIENT );
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_PREVIEW_PROMPT );
		$state->save();

		// Sanity check.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
	}

	/**
	 * @return \WP_Post
	 */
	private function given_a_non_migrated_single_event() {
		// Create an Event.
		$timezone  = new \DateTimeZone( 'Europe/Paris' );
		$utc       = new \DateTimeZone( 'UTC' );
		$now       = new \DateTimeImmutable( 'now', $timezone );
		$two_hours = new \DateInterval( 'PT2H' );
		$post_id   = ( new \WP_UnitTest_Factory_For_Post() )->create( [
			'post_type'   => TEC::POSTTYPE,
			'meta_input'  => [
				'_EventStartDate'    => $now->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'      => $now->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventStartDateUTC' => $now->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDateUTC'   => $now->setTimezone( $utc )->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventDuration'     => 7200,
				'_EventTimezone'     => $timezone->getName(),
				'_EventTimezoneAbbr' => Timezones::abbr( $now, $timezone ),
			],
			'post_status' => 'publish',
		] );
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

	private function given_the_current_migration_phase_is( $phase ) {
		$state          = tribe_get_option( State::STATE_OPTION_KEY, [] );
		$state['phase'] = $phase;
		tribe_update_option( State::STATE_OPTION_KEY, $state );
	}
}