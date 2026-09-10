<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main;
use WP_Post;
use WP_Query;

/**
 * Ported from the Events Calendar Pro suite together with the class; the rule-based
 * fixtures are replaced by dates-only Events with several dates on the same day.
 */
class Custom_Query_FiltersTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;

	/**
	 * Creates an Event with two extra dates on `$day`, i.e. two same-day Occurrences.
	 */
	private function given_an_event_with_two_dates_on( string $day, string $title ): WP_Post {
		return $this->given_a_multi_date_event(
			[
				[ 'start' => "$day 13:00:00", 'end' => "$day 14:00:00" ],
				[ 'start' => "$day 16:00:00", 'end' => "$day 17:00:00" ],
			],
			[ 'title' => $title ]
		);
	}

	private function given_a_sequence_query_for( WP_Post $post, string $date, $sequence ): WP_Query {
		$query = new WP_Query();
		$query->set( 'eventDate', $date );
		$this->set_class_fn_return( WP_Query::class, 'is_main_query', true );
		$query->query['name'] = $post->post_name;
		$query->set( 'post_type', Tribe__Events__Main::POSTTYPE );
		$query->set( 'eventSequence', $sequence );
		$query->set( 'p', 0 );

		return $query;
	}

	/**
	 * Validate the query filter will interpret when to modify the WP_Query object.
	 *
	 * @test
	 */
	public function should_modify_wp_query_when_proper_query() {
		$overlap_date = '2050-01-06';
		// A near identical Event that should not be found.
		$this->given_an_event_with_two_dates_on( $overlap_date, 'Ignored Event' );
		$post = $this->given_an_event_with_two_dates_on( $overlap_date, 'Searched Event' );

		$overlap_occurrences = Occurrence::where( 'post_id', $post->ID )
										->where( 'start_date', '>=', "$overlap_date 00:00:00" )
										->where( 'start_date', '<=', "$overlap_date 23:59:59" )
										->order_by( 'start_date', 'ASC' )
										->get();
		$this->assertCount( 2, $overlap_occurrences );

		$qf    = tribe( Custom_Query_Filters::class );
		$query = $this->given_a_sequence_query_for( $post, $overlap_date, 'faux' );

		// A non-numeric sequence must not modify the query.
		$qf->parse_for_sequence_id_lookup( $query );
		$this->assertEmpty( $query->get( 'p' ) );

		$sequence = 0;
		foreach ( $overlap_occurrences as $occurrence ) {
			++$sequence;
			$query->set( 'eventSequence', $sequence );
			$qf->parse_for_sequence_id_lookup( $query );
			$this->assertNotEmpty( $query->get( 'p' ) );
			$this->assertEquals( $occurrence->provisional_id, (int) $query->get( 'p' ) );
			// Clear for the next run.
			unset( $query->query_vars['p'] );
		}
	}

	/**
	 * Validate the query will 404 for invalid sequences.
	 *
	 * @test
	 */
	public function should_404_wp_query_when_invalid_sequence() {
		$overlap_date = '2050-01-06';
		$post         = $this->given_an_event_with_two_dates_on( $overlap_date, 'Searched Event' );

		$qf    = tribe( Custom_Query_Filters::class );
		$query = $this->given_a_sequence_query_for( $post, $overlap_date, '123' );

		$this->assertFalse( $query->is_404() );
		$qf->parse_for_sequence_id_lookup( $query );
		$this->assertEmpty( $query->get( 'p' ) );
		$this->assertTrue( $query->is_404() );
	}

	/**
	 * It should leave queries that are not sequence lookups alone
	 *
	 * @test
	 */
	public function should_leave_non_sequence_queries_alone() {
		$post = $this->given_an_event_with_two_dates_on( '2050-01-06', 'Searched Event' );
		$qf   = tribe( Custom_Query_Filters::class );

		// No date.
		$query = $this->given_a_sequence_query_for( $post, '', 1 );
		$qf->parse_for_sequence_id_lookup( $query );
		$this->assertEmpty( $query->get( 'p' ) );
		$this->assertFalse( $query->is_404() );

		// A post already set.
		$query = $this->given_a_sequence_query_for( $post, '2050-01-06', 1 );
		$query->set( 'p', $post->ID );
		$qf->parse_for_sequence_id_lookup( $query );
		$this->assertEquals( $post->ID, $query->get( 'p' ) );

		// Not a query at all.
		$qf->parse_for_sequence_id_lookup( null );
	}
}
