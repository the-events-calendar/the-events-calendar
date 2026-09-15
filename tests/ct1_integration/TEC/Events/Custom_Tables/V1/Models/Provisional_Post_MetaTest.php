<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Provisional_Post_MetaTest extends WPTestCase {
	use With_Recurrence_Engine;

	private function provisional_id_for( Occurrence $occurrence ): int {
		return tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
	}

	/**
	 * It should leave the meta value alone for a provisional ID not backed by an Occurrence
	 *
	 * A deleted Occurrence, a stale bookmark or a hand-typed `?p=<base+n>` all pass the
	 * provisional ID range check; `get_post()` returns `null` for them.
	 *
	 * @test
	 */
	public function should_leave_the_meta_value_alone_for_a_dangling_provisional_id(): void {
		$dangling = tribe( ID_Generator::class )->current() + 987654;

		$this->assertTrue( tribe( Provisional_Post::class )->is_provisional_post_id( $dangling ) );
		$this->assertNull( get_post( $dangling ) );

		$meta = tribe( Provisional_Post_Meta::class );

		$this->assertSame( 'untouched', $meta->hydrate_tec_occurrence_meta( 'untouched', $dangling, '_tec_occurrence' ) );
		$this->assertNull( $meta->hydrate_tec_occurrence_meta( null, $dangling, '_tec_occurrence' ) );
	}

	/**
	 * It should hydrate the Occurrence for a live provisional ID
	 *
	 * @test
	 */
	public function should_hydrate_the_occurrence_for_a_live_provisional_id(): void {
		$post        = $this->given_a_multi_date_event();
		$occurrences = iterator_to_array(
			Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date_utc', 'ASC' )->all(),
			false
		);
		$this->assertCount( 2, $occurrences );

		$second      = $occurrences[1];
		$provisional = $this->provisional_id_for( $second );
		// Production reaches this filter from `WP_Post::__get()` on an already hydrated provisional post.
		tribe( Provisional_Post::class )->hydrate_caches( [ $provisional ] );

		$result = tribe( Provisional_Post_Meta::class )->hydrate_tec_occurrence_meta( null, $provisional, '_tec_occurrence' );

		$this->assertInstanceOf( Occurrence::class, $result );
		$this->assertEquals( $second->occurrence_id, $result->occurrence_id );
		$this->assertEquals( $post->ID, $result->post_id );

		// A second call is served from the memoized cache and returns the same Occurrence.
		$again = tribe( Provisional_Post_Meta::class )->hydrate_tec_occurrence_meta( null, $provisional, '_tec_occurrence' );
		$this->assertInstanceOf( Occurrence::class, $again );
		$this->assertEquals( $second->occurrence_id, $again->occurrence_id );
	}

	/**
	 * It should pass through other meta keys and non-provisional IDs
	 *
	 * @test
	 */
	public function should_pass_through_other_meta_keys_and_real_post_ids(): void {
		$post        = $this->given_a_multi_date_event();
		$occurrence  = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date_utc', 'ASC' )->first();
		$provisional = $this->provisional_id_for( $occurrence );
		$meta        = tribe( Provisional_Post_Meta::class );

		$this->assertSame( 'start', $meta->hydrate_tec_occurrence_meta( 'start', $provisional, '_EventStartDate' ) );
		$this->assertSame( 'real', $meta->hydrate_tec_occurrence_meta( 'real', $post->ID, '_tec_occurrence' ) );
	}
}
