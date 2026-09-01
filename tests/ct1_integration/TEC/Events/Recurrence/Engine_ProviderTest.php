<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Engine_ProviderTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * It should register its hooks with the documented priorities
	 *
	 * @test
	 */
	public function should_register_its_hooks_with_the_documented_priorities(): void {
		$engine = tribe( Engine_Provider::class );

		/*
		 * The dates generator must run before any rule engine (Events Calendar Pro hooks
		 * at 10) and the freeze generator after every one of them.
		 */
		$this->assertEquals( 9, has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
		$this->assertEquals( 100, has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_freeze_generator' ] ) );
		$this->assertEquals( 9, has_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $engine, 'get_occurrence_match' ] ) );
		$this->assertEquals( 10, has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $engine, 'derive_dates_rset_from_meta' ] ) );
		$this->assertEquals( 10, has_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $engine, 'prune_occurrences_by_sequence' ] ) );
		$this->assertEquals( 10, has_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $engine, 'normalize_occurrence_id' ] ) );

		$occurrences = Occurrences::table_name( false );
		$events      = Events::table_name( false );
		$this->assertEquals( 10, has_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $engine, 'extend_occurrence_model' ] ) );
		$this->assertEquals( 10, has_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $engine, 'extend_event_model' ] ) );
	}

	/**
	 * It should unregister symmetrically
	 *
	 * @test
	 */
	public function should_unregister_symmetrically(): void {
		$engine = tribe( Engine_Provider::class );

		$engine->unregister();

		$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
		$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_freeze_generator' ] ) );
		$this->assertFalse( has_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $engine, 'get_occurrence_match' ] ) );
		$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $engine, 'derive_dates_rset_from_meta' ] ) );
		$this->assertFalse( has_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $engine, 'prune_occurrences_by_sequence' ] ) );

		// Registering again re-attaches, idempotently for this instance.
		$engine->register();
		$engine->register();

		$this->assertEquals(
			9,
			has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] )
		);
	}

	/**
	 * It should extend the models with the recurrence fields
	 *
	 * @test
	 */
	public function should_extend_the_models_with_the_recurrence_fields(): void {
		$engine = tribe( Engine_Provider::class );

		$event_extensions = $engine->extend_event_model( [ 'validators' => [], 'formatters' => [], 'hashed_keys' => [] ] );
		$this->assertArrayHasKey( 'rset', $event_extensions['validators'] );
		$this->assertArrayHasKey( 'rset', $event_extensions['formatters'] );

		$occurrence_extensions = $engine->extend_occurrence_model( [ 'validators' => [], 'formatters' => [], 'hashed_keys' => [] ] );
		foreach ( [ 'has_recurrence', 'sequence', 'is_rdate' ] as $field ) {
			$this->assertArrayHasKey( $field, $occurrence_extensions['validators'] );
			$this->assertArrayHasKey( $field, $occurrence_extensions['formatters'] );
		}
	}

	/**
	 * It should not derive the rset when one was already derived
	 *
	 * @test
	 */
	public function should_not_derive_the_rset_when_one_was_already_derived(): void {
		$post = $this->given_a_multi_date_event();

		$data = [
			'rset'       => 'RULE-ENGINE-DERIVED',
			'timezone'   => 'UTC',
			'start_date' => '2026-11-05 09:00:00',
			'end_date'   => '2026-11-05 10:00:00',
		];

		$filtered = tribe( Engine_Provider::class )->derive_dates_rset_from_meta( $data, $post->ID );

		// A rule engine providing its own derivation wins.
		$this->assertEquals( 'RULE-ENGINE-DERIVED', $filtered['rset'] );
	}

	/**
	 * It should not derive the rset from non dates meta
	 *
	 * @test
	 */
	public function should_not_derive_the_rset_from_non_dates_meta(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Rule Meta Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		update_post_meta(
			$post->ID,
			'_EventRecurrence',
			[
				'rules' => [
					[
						'type'   => 'Custom',
						'custom' => [ 'type' => 'Week', 'interval' => 1 ],
					],
				],
			]
		);

		$data = [
			'timezone'   => 'UTC',
			'start_date' => '2026-11-05 09:00:00',
			'end_date'   => '2026-11-05 10:00:00',
		];

		$filtered = tribe( Engine_Provider::class )->derive_dates_rset_from_meta( $data, $post->ID );

		$this->assertArrayNotHasKey( 'rset', $filtered );
	}

	/**
	 * It should derive a dates only rset from the meta
	 *
	 * @test
	 */
	public function should_derive_a_dates_only_rset_from_the_meta(): void {
		$post = $this->given_a_multi_date_event();

		$data = [
			'timezone'   => 'UTC',
			'start_date' => '2026-11-05 09:00:00',
			'end_date'   => '2026-11-05 10:00:00',
		];

		$filtered = tribe( Engine_Provider::class )->derive_dates_rset_from_meta( $data, $post->ID );

		$this->assertArrayHasKey( 'rset', $filtered );
		$this->assertTrue( Dates::is_dates_only( $filtered['rset'] ) );
	}
}
