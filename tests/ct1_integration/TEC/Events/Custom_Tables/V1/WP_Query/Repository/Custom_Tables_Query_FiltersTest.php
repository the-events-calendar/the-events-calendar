<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Repository;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe__Repository__Decorator as Repository_Decorator;
use Tribe__Events__Main as TEC;

class Test_Repository_ecc8dffa78cbdc94a161c83a1faa3134 extends Repository_Decorator {
	public function __construct() {
		$this->decorated = tribe_events();
		$this->decorated->add_schema_entry( 'has_connections', [ $this, 'filter_by_has_connection' ] );
	}

	public function filter_by_has_connection( $has_connection = true ): void {
		$repository = $this;

		// If the repository is decorated, use that.
		if ( ! empty( $repository ) ) {
			$repository = $this->decorated;
		}

		global $wpdb;

		if ( $has_connection ) {
			$repository->join_clause( "JOIN {$wpdb->postmeta} connection
				ON connection.meta_key = '_connections'
				AND connection.post_id = {$wpdb->posts}.ID" );
			$repository->where_clause( 'connection.meta_value > 0' );

			return;
		}

		$repository->join_clause( "LEFT JOIN {$wpdb->postmeta} connection
			ON connection.meta_key = '_connections'
			AND connection.post_id = {$wpdb->posts}.ID" );
		$repository->where_clause( 'connection.meta_id IS NULL OR connection.meta_value < 0' );
	}
}

class Custom_Tables_Query_FiltersTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should correctly apply schema filters
	 *
	 * @test
	 */
	public function should_correctly_apply_schema_filters() {
		$event_with_connection_1 = tribe_events()->set_args( [
			'title'      => 'Event with connection 1',
			'start_date' => '2022-01-01 10:00:00',
			'duration'   => 7 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$this->assertInstanceOf( \WP_Post::class, $event_with_connection_1 );
		update_post_meta( $event_with_connection_1->ID, '_connections', 23 );

		$event_with_connection_2 = tribe_events()->set_args( [
			'title'      => 'Event with connection 2',
			'start_date' => '2022-01-02 10:00:00',
			'duration'   => 7 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		update_post_meta( $event_with_connection_2->ID, '_connections', 89 );
		$this->assertInstanceOf( \WP_Post::class, $event_with_connection_2 );

		$event_wo_connection_1 = tribe_events()->set_args( [
			'title'      => 'Event without connection 1',
			'start_date' => '2022-01-03 10:00:00',
			'duration'   => 7 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$this->assertInstanceOf( \WP_Post::class, $event_wo_connection_1 );

		$event_wo_connection_2 = tribe_events()->set_args( [
			'title'      => 'Event without connection 2',
			'start_date' => '2022-01-04 10:00:00',
			'duration'   => 7 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$this->assertInstanceOf( \WP_Post::class, $event_wo_connection_2 );
		global $wpdb;

		// No date filter, has connections.
		$repository = new  Test_Repository_ecc8dffa78cbdc94a161c83a1faa3134();
		$matches    = $repository->where( 'has_connections', true )->fields( 'ids' )->all();

		$this->assertEmpty( $wpdb->last_error );
		$this->assertEqualSets( [ $event_with_connection_1->ID, $event_with_connection_2->ID ], $matches );

		// With date filter, has connections.
		$repository = new  Test_Repository_ecc8dffa78cbdc94a161c83a1faa3134();
		$matches    = $repository->where( 'has_connections', true )
		                         ->where( 'starts_after', '2022-01-02 00:00:00' )
		                         ->fields( 'ids' )->all();

		$this->assertEmpty( $wpdb->last_error );
		$this->assertEquals( [ $event_with_connection_2->ID ], $matches );

		// No date filter, has no connections.
		$repository = new  Test_Repository_ecc8dffa78cbdc94a161c83a1faa3134();
		$matches    = $repository->where( 'has_connections', false )->fields( 'ids' )->all();

		$this->assertEmpty( $wpdb->last_error );
		$this->assertEqualSets( [ $event_wo_connection_1->ID, $event_wo_connection_2->ID ], $matches );

		// With date filter, has no connections.
		$repository = new  Test_Repository_ecc8dffa78cbdc94a161c83a1faa3134();
		$matches    = $repository->where( 'has_connections', false )
		                         ->where( 'starts_after', '2022-01-04 00:00:00' )
		                         ->fields( 'ids' )->all();

		$this->assertEmpty( $wpdb->last_error );
		$this->assertEquals( [ $event_wo_connection_2->ID ], $matches );
	}

	/**
	 * It should not add same JOIN clause twice when there are no redirections to custom tables
	 *
	 * The issue would manifest in the context of some queries triggered from ECP, but should be addressed
	 * in TEC: this is the reason why this test is here. The query filters hold internal state that will
	 * store the JOIN clauses to be added, and this state should not contain the same JOIN clause twice.
	 *
	 * @test
	 */
	public function should_not_add_same_join_clause_twice_when_there_are_no_redirections_to_custom_tables(): void {
		$wp_query = new \WP_Query( [
			'post_type' => TEC::POSTTYPE,
		] );

		$query_filters = new Custom_Tables_Query_Filters( new Query_Replace() );
		$query_filters->set_query( $wp_query );

		$filtered_join = trim( $query_filters->filter_posts_join( '', $wp_query ) );
		$occurrences   = Occurrences::table_name();

		$this->assertEquals(
			"JOIN $occurrences ON test_posts.ID = $occurrences.post_id",
			$filtered_join,
			'On first filtering, the JOIN clause on Occurrences should be added.'
		);

		$filtered_join_2 = trim( $query_filters->filter_posts_join( '', $wp_query ) );

		$this->assertEquals(
			"JOIN $occurrences ON test_posts.ID = $occurrences.post_id",
			$filtered_join_2,
			'On second filtering, the JOIN clause on Occurrences should not be added a 2nd time.'
		);

		$filtered_join_3 = trim( $query_filters->filter_posts_join( '', $wp_query ) );

		$this->assertEquals(
			"JOIN $occurrences ON test_posts.ID = $occurrences.post_id",
			$filtered_join_3,
			'On third filtering, the JOIN clause on Occurrences should not be added a 2nd time.'
		);
	}
}
