<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Strategies;

use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy as Strategy;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\With_Uopz;

class Single_Event_Migration_StrategyTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use With_Uopz;

	/**
	 * @before
	 */
	public function set_migration_phase() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
	}

	/**
	 * It should correctly migrate a single event
	 *
	 * @test
	 */
	public function should_correctly_migrate_a_single_event() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;

		$strategy = new Strategy( $post_id, false );
		$strategy->apply( $report );

		$event = Event::find( $post_id, 'post_id' );

		$this->assertInstanceOf( Event::class, $event );

		$occurrences = Occurrence::where( 'post_id', '=', $post_id )
		                         ->get();

		$this->assertCount( 1, $occurrences );
		$this->assertContainsOnlyInstancesOf( Occurrence::class, $occurrences );
	}

	/**
	 * It should throw if post is not event
	 *
	 * @test
	 */
	public function should_throw_if_post_is_not_event() {
		$post = static::factory()->post->create_and_get();

		$post_id = $post->ID;

		$this->expectException( Migration_Exception::class );

		new Strategy( $post_id, false );
	}

	/**
	 * It should throw if event cannot be upserted
	 *
	 * @test
	 */
	public function should_throw_if_event_cannot_be_upserted() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;
		// The Builder is, actually, the class doing the upsertion.
		$this->uopz_set_return( Builder::class, 'upsert', false );

		$this->expectException( Migration_Exception::class );

		$strategy = new Strategy( $post_id, false );
		$strategy->apply( $report );
	}

	/**
	 * It should throw if event cannot be found after upsertion
	 *
	 * @test
	 */
	public function should_throw_if_event_cannot_be_found_after_upsertion() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;
		// Say we're done, but we've done nothing.
		$this->uopz_set_return( Builder::class, 'upsert', true );

		$this->expectException( Migration_Exception::class );

		$strategy = new Strategy( $post_id, false );
		$strategy->apply( $report );
	}

	/**
	 * It should throw if occurrence cannot be created
	 *
	 * @test
	 */
	public function should_throw_if_occurrence_cannot_be_created() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;
		// Right after the Occurrences have been crated, delete them.
		add_filter( 'tec_events_custom_tables_v1_after_insert_occurrences', static function ( $post_is_param ) use ( $post_id ) {
			if ( $post_is_param === $post_id ) {
				Occurrence::where( 'post_id', '=', $post_id )->delete();
			}
		} );
		$this->expectException( Migration_Exception::class );

		$strategy = new Strategy( $post_id, false );
		$strategy->apply( $report );
	}

	/**
	 * It should throw if more than one occurrence is created
	 *
	 * @test
	 */
	public function should_throw_if_more_than_one_occurrence_is_created() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;
		// Right after the Occurrences have been crated, add 2 more.
		add_filter( 'tec_events_custom_tables_v1_after_insert_occurrences', static function ( $post_is_param, $insertions ) use ( $post_id ) {
			if ( $post_is_param === $post_id ) {
				$proto = $insertions[0];
				Occurrence::insert( array_merge( $proto, [ 'hash' => sha1( microtime() ) ] ) );
				Occurrence::insert( array_merge( $proto, [ 'hash' => sha1( microtime() ) ] ) );
			}
		}, 10, 2 );
		$this->expectException( Migration_Exception::class );

		$strategy = new Strategy( $post_id, false );
		$strategy->apply( $report );
	}
}
