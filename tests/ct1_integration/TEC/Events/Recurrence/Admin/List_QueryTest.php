<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Clock_Mock;
use WP_Query;

class List_QueryTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Clock_Mock;

	/** @var array */
	private $globals_before;
	/** @var array */
	private $request_before;

	public function setUp(): void {
		parent::setUp();
		$this->globals_before = [ $GLOBALS['wp_query'], $GLOBALS['wp_the_query'], $GLOBALS['current_screen'] ?? null ];
		$this->request_before = $_REQUEST;
		$_REQUEST = [ 'tec_events_view' => 'occurrences', 'tec_dates' => 'upcoming' ];
		$GLOBALS['current_screen'] = null;
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function tearDown(): void {
		[ $GLOBALS['wp_query'], $GLOBALS['wp_the_query'], $GLOBALS['current_screen'] ] = $this->globals_before;
		$_REQUEST = $this->request_before;
		parent::tearDown();
	}

	private function query( array $args = [] ): WP_Query {
		// Freeze after fixture writes: cache invalidation uses microtime to distinguish consecutive saves.
		$this->freeze_time( new \DateTimeImmutable( '2050-01-07 12:00:00 UTC' ) );
		set_current_screen( 'edit-tribe_events' );
		$query = new WP_Query();
		$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'] = $query;
		$query->query( array_merge( [ 'post_type' => 'tribe_events', 'posts_per_page' => 20 ], $args ) );
		return $query;
	}

	/** @test */
	public function should_show_ongoing_and_future_dates_with_occurrence_pagination(): void {
		$post = $this->given_a_multi_date_event( [
			[ 'start' => '2050-01-07 09:00:00', 'end' => '2050-01-07 13:00:00' ],
			[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
		] );
		$_REQUEST['tec_event'] = $post->ID;
		$query = $this->query( [ 'posts_per_page' => 1 ] );
		$this->assertSame( 2, (int) $query->found_posts, $query->request );
		$this->assertSame( 2, (int) $query->max_num_pages );
		$this->assertCount( 1, $query->posts );
		$this->assertSame( '2050-01-07 09:00:00', get_post_meta( $query->posts[0]->ID, '_EventStartDate', true ) );
		$first_id = $query->posts[0]->ID;
		$query = $this->query( [ 'posts_per_page' => 1, 'paged' => 2 ] );
		$this->assertNotSame( $first_id, $query->posts[0]->ID );
		$this->assertSame( '2050-01-12 09:00:00', get_post_meta( $query->posts[0]->ID, '_EventStartDate', true ) );
		$this->assertSame( 2, tribe( List_Query::class )->counts()['publish'] );
		$_REQUEST['tec_dates'] = 'past';
		$query = $this->query();
		$this->assertSame( 1, (int) $query->found_posts );
		$this->assertSame( '2050-01-05 09:00:00', get_post_meta( $query->posts[0]->ID, '_EventStartDate', true ) );
	}

	/** @test */
	public function should_retain_search_category_and_status_in_both_rows_and_counts(): void {
		$post = $this->given_a_multi_date_event( [], [ 'title' => 'Needle event', 'status' => 'draft' ] );
		$this->given_a_multi_date_event( [], [ 'title' => 'Another event' ] );
		$term = static::factory()->term->create( [ 'taxonomy' => 'tribe_events_cat' ] );
		wp_set_object_terms( $post->ID, [ $term ], 'tribe_events_cat' );
		$_REQUEST['tec_dates'] = 'all';
		$query = $this->query( [ 's' => 'Needle', 'post_status' => 'draft', 'tax_query' => [ [ 'taxonomy' => 'tribe_events_cat', 'terms' => [ $term ] ] ] ] );
		$this->assertSame( 2, (int) $query->found_posts, $query->request );
		$counts = tribe( List_Query::class )->counts();
		$this->assertSame( 2, $counts['draft'] );
		$this->assertSame( 0, $counts['publish'] );
		$this->assertSame( 2, $counts['all'] );
		foreach ( $query->posts as $occurrence ) {
			$this->assertSame( $post->ID, Occurrence::normalize_id( $occurrence->ID ) );
		}
	}

	/** @test */
	public function should_compare_utc_end_dates_for_ongoing_multiday_events(): void {
		$post = $this->given_a_multi_date_event( [], [ 'start_date' => '2050-01-06 20:00:00', 'end_date' => '2050-01-07 08:00:00', 'timezone' => 'America/Los_Angeles' ] );
		$_REQUEST['tec_event'] = $post->ID;
		$query = $this->query();
		$this->assertSame( 2, (int) $query->found_posts, $query->request );
		$this->assertSame( '2050-01-06 20:00:00', get_post_meta( $query->posts[0]->ID, '_EventStartDate', true ) );
	}

	/** @test */
	public function should_keep_parent_management_and_unscheduled_drafts_accessible(): void {
		$post = $this->given_a_multi_date_event();
		$draft = static::factory()->post->create( [ 'post_type' => 'tribe_events', 'post_status' => 'draft', 'post_title' => 'Unscheduled' ] );
		$_REQUEST['tec_events_view'] = 'events';
		$query = $this->query( [ 'post__in' => [ $post->ID, $draft ], 'post_status' => [ 'publish', 'draft' ] ] );
		$this->assertEqualsCanonicalizing( [ $post->ID, $draft ], wp_list_pluck( $query->posts, 'ID' ) );
		$this->assertFalse( (bool) $query->get( List_Query::FLAG ) );
		$this->assertSame( 2, tribe( List_Query::class )->counts()['all'] );
		$query = $this->query( [ 's' => 'Unscheduled' ] );
		$this->assertSame( 1, (int) $query->found_posts );
		$this->assertSame( 1, tribe( List_Query::class )->counts()['draft'] );
		$this->assertSame( 0, tribe( List_Query::class )->counts()['publish'] );
	}

	/** @test */
	public function should_apply_author_and_private_permissions_to_rows_and_counts(): void {
		$author = static::factory()->user->create( [ 'role' => 'author' ] );
		$other = static::factory()->user->create( [ 'role' => 'author' ] );
		$mine = $this->given_a_multi_date_event( [], [ 'author' => $author ] );
		$this->given_a_multi_date_event( [], [ 'author' => $other, 'status' => 'private' ] );
		wp_set_current_user( $author );
		$_REQUEST['tec_dates'] = 'all';
		$query = $this->query();
		$this->assertSame( 2, (int) $query->found_posts, $query->request );
		$this->assertSame( 0, tribe( List_Query::class )->counts()['private'] );
		$query = $this->query( [ 'author' => $author ] );
		$this->assertSame( 2, (int) $query->found_posts );
		$this->assertSame( $mine->ID, Occurrence::normalize_id( $query->posts[0]->ID ) );
		$this->assertSame( 2, tribe( List_Query::class )->counts()['all'] );
	}

	/** @test */
	public function should_include_an_ongoing_all_day_date_and_label_its_timezone(): void {
		$post = $this->given_a_multi_date_event( [], [ 'start_date' => '2050-01-07 00:00:00', 'end_date' => '2050-01-07 23:59:59', 'all_day' => true, 'timezone' => 'America/New_York' ] );
		$_REQUEST['tec_event'] = $post->ID;
		$query = $this->query();
		$this->assertSame( 2, (int) $query->found_posts, $query->request );
		$data = tribe( Presentation::class )->get( $query->posts[0]->ID );
		$this->assertStringContainsString( 'All Day', $data['start'] );
		$this->assertStringContainsString( 'America/New_York', $data['start'] );
	}

	/** @test */
	public function should_preserve_explicit_date_sorting(): void {
		$post = $this->given_a_multi_date_event();
		$_REQUEST['tec_event'] = $post->ID;
		$_REQUEST['tec_dates'] = 'all';
		$query = $this->query( [ 'orderby' => 'end-date', 'order' => 'DESC' ] );
		$this->assertSame( '2050-01-12 10:00:00', get_post_meta( $query->posts[0]->ID, '_EventEndDate', true ) );
	}

	/** @test */
	public function should_preserve_the_parent_filter_when_occurrence_display_is_turned_off(): void {
		$post = $this->given_a_multi_date_event();
		$other = $this->given_a_multi_date_event();
		$_REQUEST['tec_event'] = $post->ID;
		$_REQUEST['tec_dates'] = 'all';
		$this->assertSame( 2, (int) $this->query()->found_posts );
		$_REQUEST['tec_events_view'] = 'events';
		$query = $this->query();
		$this->assertSame( [ $post->ID ], wp_list_pluck( $query->posts, 'ID' ) );
		$this->assertSame( 1, (int) $query->found_posts );
		$this->assertSame( 1, tribe( List_Query::class )->counts()['all'] );
		$query = $this->query( [ 'post__in' => [ $other->ID ] ] );
		$this->assertSame( [], $query->posts );
		$this->assertSame( 0, tribe( List_Query::class )->counts()['all'] );
	}
}
