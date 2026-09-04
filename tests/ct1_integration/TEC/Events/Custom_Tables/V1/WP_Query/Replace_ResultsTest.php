<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

class Replace_ResultsTest extends WPTestCase {
	use With_Recurrence_Engine;

	private function given_an_events_query( string $fields = '' ): WP_Query {
		$query = new WP_Query();
		$query->set( 'post_type', TEC::POSTTYPE );
		if ( '' !== $fields ) {
			$query->set( 'fields', $fields );
		}

		return $query;
	}

	private function provisional_ids_of( int $post_id ): array {
		return array_map(
			static fn( Occurrence $occurrence ): int => (int) $occurrence->provisional_id,
			iterator_to_array( Occurrence::where( 'post_id', '=', $post_id )->order_by( 'start_date_utc', 'ASC' )->all(), false )
		);
	}

	/**
	 * It should replace the provisional ids of a multi-date event with its occurrence posts
	 *
	 * @test
	 */
	public function should_replace_the_provisional_ids_of_a_multi_date_event_with_occurrence_posts(): void {
		$post            = $this->given_a_multi_date_event();
		$provisional_ids = $this->provisional_ids_of( $post->ID );
		$this->assertCount( 2, $provisional_ids );

		$replaced = tribe( Replace_Results::class )->replace( $provisional_ids, $this->given_an_events_query() );

		$this->assertCount( 2, $replaced );
		foreach ( $replaced as $index => $occurrence_post ) {
			$this->assertInstanceOf( WP_Post::class, $occurrence_post );
			// A multi-date Event keeps one post per Occurrence, carrying the provisional ID.
			$this->assertEquals( $provisional_ids[ $index ], $occurrence_post->ID );
			$this->assertEquals( $post->post_title, $occurrence_post->post_title );
			$this->assertInstanceOf( Occurrence::class, $occurrence_post->_tec_occurrence );
		}
	}

	/**
	 * It should replace the provisional id of a single event with its real post
	 *
	 * @test
	 */
	public function should_replace_the_provisional_id_of_a_single_event_with_its_real_post(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Single Replace Results Event',
				'status'     => 'publish',
				'start_date' => '2050-01-05 09:00:00',
				'duration'   => HOUR_IN_SECONDS,
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $post );
		$provisional_ids = $this->provisional_ids_of( $post->ID );
		$this->assertCount( 1, $provisional_ids );

		$replaced = tribe( Replace_Results::class )->replace( $provisional_ids, $this->given_an_events_query() );

		$this->assertCount( 1, $replaced );
		$this->assertInstanceOf( WP_Post::class, $replaced[0] );
		$this->assertEquals( $post->ID, $replaced[0]->ID, 'A single Event resolves to its real post.' );
	}

	/**
	 * It should return ids when the query asks for them
	 *
	 * @test
	 */
	public function should_return_ids_when_the_query_asks_for_them(): void {
		$post            = $this->given_a_multi_date_event();
		$provisional_ids = $this->provisional_ids_of( $post->ID );

		$replaced = tribe( Replace_Results::class )->replace( $provisional_ids, $this->given_an_events_query( 'ids' ) );

		$this->assertEquals( $provisional_ids, array_values( $replaced ) );
	}

	/**
	 * It should leave non event queries and non array results alone
	 *
	 * @test
	 */
	public function should_leave_non_event_queries_and_non_array_results_alone(): void {
		$post            = $this->given_a_multi_date_event();
		$provisional_ids = $this->provisional_ids_of( $post->ID );
		$replacer        = tribe( Replace_Results::class );

		$posts_query = new WP_Query();
		$posts_query->set( 'post_type', 'post' );
		$this->assertSame( $provisional_ids, $replacer->replace( $provisional_ids, $posts_query ) );

		$this->assertSame( $provisional_ids, $replacer->replace( $provisional_ids, null ) );

		$this->assertSame( 'not-an-array', $replacer->replace( 'not-an-array', $this->given_an_events_query() ) );

		// Real post IDs are not touched.
		$this->assertSame( [ $post->ID ], $replacer->replace( [ $post->ID ], $this->given_an_events_query() ) );
	}
}
