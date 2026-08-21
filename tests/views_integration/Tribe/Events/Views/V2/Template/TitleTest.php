<?php

namespace Tribe\Events\Views\V2\Template;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Utils\View as Utils_View;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

class TitleTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Post_Remapping;

	/**
	 * @var array<string, mixed> The global query objects as they stood before the current test.
	 */
	private $global_query_backup = [];

	/**
	 * @var string The request URI as it stood before the current test.
	 */
	private $request_uri_backup = '';

	/**
	 * @var mixed The `posts_per_page` option as it stood before the current test, restored verbatim.
	 */
	private $posts_per_page_backup;

	public function setUp() {
		parent::setUp();
		$return_mock_url = static function () {
			return 'http://products.tribe';
		};
		add_filter( 'option_home', $return_mock_url );

		$this->global_query_backup   = [
			'wp'           => $GLOBALS['wp'] ?? null,
			'wp_query'     => $GLOBALS['wp_query'] ?? null,
			'wp_the_query' => $GLOBALS['wp_the_query'] ?? null,
			'page'         => $GLOBALS['page'] ?? null,
			'paged'        => $GLOBALS['paged'] ?? null,
		];
		$this->request_uri_backup    = $_SERVER['REQUEST_URI'] ?? '';
		$this->posts_per_page_backup = get_option( 'posts_per_page' );
	}

	public function tearDown() {
		/*
		 * A simulated request outlives the test that made it: a leftover `paged` pages every later
		 * title, a leftover request URI pages every later View URL, and a leftover page size
		 * offsets every later View query.
		 */
		foreach ( $this->global_query_backup as $global => $value ) {
			$GLOBALS[ $global ] = $value;
		}

		$_SERVER['REQUEST_URI'] = $this->request_uri_backup;

		update_option( 'posts_per_page', $this->posts_per_page_backup );
		tribe_context()->refresh();

		parent::tearDown();
	}


	public function test_featured_single_event_title() {
		$event   = $this->get_mock_event( 'events/single/1.json' );
		$context = tribe_context()->alter( [
			'post_id'         => $event->ID,
			'single'          => true,
			'event_post_type' => true,
			'featured'        => false,
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_featured_event_archive() {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => true,
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_date_wo_posts() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'featured'           => false,
			'event_date'         => '2018-02-01',
			'event_display'      => 'default',
			'event_display_mode' => 'default',
		] );

		$title = new Title();
		$title->set_context( $context );
		$title->set_posts( [] );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_date_w_posts() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'featured'           => false,
			'event_date'         => '2018-02-02',
			'event_display'      => 'default',
			'event_display_mode' => 'default',
		] );
		$event_1 = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 25,
			'start_date' => '2018-01-01',
			'end_date'   => '2018-01-01',
		] );
		$event_2 = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 89,
			'start_date' => '2018-03-03',
			'end_date'   => '2018-03-03',
		] );

		$title = new Title();
		$title->set_context( $context );
		$title->set_posts( [ $event_1, $event_2 ] );

		$this->assertMatchesSnapshot( $title->build_title() );

		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => true,
			'event_date'      => '2018-02-02',
		] );

		$title->set_context( $context );
		$title->set_posts( [ $event_1, $event_2 ] );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_post_range_title_clamps_past_first_date_for_upcoming() {
		$future       = date( 'Y-m-d', strtotime( '+30 days' ) );
		$past_event   = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9001,
			'start_date' => '2022-12-01',
			'end_date'   => '2022-12-01',
		] );
		$future_event = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9002,
			'start_date' => $future,
			'end_date'   => $future,
		] );

		$context = tribe_context()->alter( [
			'event_post_type'    => true,
			'event_display'      => 'list',
			'event_display_mode' => 'list',
		] );

		$range = Title::build_post_range_title( $context, '', [ $past_event, $future_event ] );

		$expected_first = tribe_format_date( date( 'Y-m-d' ), false );
		$expected_last  = tribe_get_start_date( $future_event, false );

		$this->assertSame( "$expected_first - $expected_last", $range );
		$this->assertStringNotContainsString( '2022', $range );
	}

	public function test_post_range_title_collapses_same_day_to_single_date() {
		$same_day = date( 'Y-m-d', strtotime( '+5 days' ) );

		$event_1 = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9101,
			'start_date' => $same_day,
			'end_date'   => $same_day,
		] );
		$event_2 = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9102,
			'start_date' => $same_day,
			'end_date'   => $same_day,
		] );

		$context = tribe_context()->alter( [
			'event_post_type'    => true,
			'event_display'      => 'list',
			'event_display_mode' => 'list',
		] );

		$range = Title::build_post_range_title( $context, $same_day, [ $event_1, $event_2 ] );

		$this->assertSame( tribe_get_start_date( $event_1, false ), $range );
		$this->assertStringNotContainsString( ' - ', $range );
	}

	public function test_post_range_title_preserves_past_dates_in_past_mode() {
		$older = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9201,
			'start_date' => '2022-01-15',
			'end_date'   => '2022-01-15',
		] );
		$newer = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9202,
			'start_date' => '2022-06-20',
			'end_date'   => '2022-06-20',
		] );

		$context = tribe_context()->alter( [
			'event_post_type'    => true,
			'event_display'      => 'past',
			'event_display_mode' => 'past',
		] );

		$range = Title::build_post_range_title( $context, '', [ $newer, $older ] );

		$expected_first = tribe_get_start_date( $older, false );
		$expected_last  = tribe_get_start_date( $newer, false );

		$this->assertSame( "$expected_first - $expected_last", $range );
	}

	public function title_with_views_data_provider() {
		$events = [
			[
				'ID'         => 1,
				'start_date' => '2018-01-05',
				'end_date'   => '2018-01-05',
			],
			[
				'ID'         => 2,
				'start_date' => '2019-02-03',
				'end_date'   => '2019-02-03',
			],
		];

		$event_displays = [
			'default',
			'list',
			'month',
			null
		];

		$event_dates = [
			'2017-02-02', // before
			'2018-01-05', // first
			'2018-02-01', // in-between
			'2019-02-03', // last
			'2022-06-06', // after
			null
		];

		$event_display_modes = [
			'past',
			null
		];

		$data = [];
		foreach ( $event_dates as $event_date ) {
			foreach ( $event_displays as $view_slug ) {
				foreach ( $event_display_modes as $event_display_mode ) {
					$key          = count( $events ) . " events -> event_date '$event_date' -> display mode '$event_display_mode' -> view '$view_slug'";
					$data[ $key ] = [
						$events,
						[
							'event_post_type'    => true,
							'event_date'         => $event_date,
							'event_display'      => $view_slug,
							'event_display_mode' => $event_display_mode
						]
					];
				}
			}
		}

		return $data;
	}

	/**
	 * @dataProvider title_with_views_data_provider
	 * @test
	 */
	public function test_title_with_views( $events, $context ) {
		$context     = tribe_context()->alter( $context );
		$mock_events = [];
		$is_past     = $context->get( 'event_display_mode' ) === 'past';
		usort( $events, function ( $a, $b ) use ( $is_past ) {

			if ( $is_past ) {
				return strtotime( $a['start_date'] ) > strtotime( $b['start_date'] ) ? - 1 : 1;
			}

			return strtotime( $a['start_date'] ) > strtotime( $b['start_date'] ) ? 1 : - 1;
		} );
		foreach ( $events as $event ) {
			$mock_events[] = $this->get_mock_event( 'events/single/1.template.json', $event );
		}

		$title = new Title();
		$title->set_context( $context );
		$title->set_posts( $mock_events );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_past_events() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'event_display'      => 'default',
			'event_display_mode' => 'past',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_month_view() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'event_display'      => 'month',
			'event_display_mode' => 'month',
			'event_date'         => '2019-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_featured_month_view() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'event_display'      => 'month',
			'event_display_mode' => 'month',
			'featured'           => true,
			'event_date'         => '2019-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_day_view() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'event_display'      => 'day',
			'event_display_mode' => 'day',
			'event_date'         => '2019-02-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_featured_day_view() {
		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'event_display'      => 'day',
			'event_display_mode' => 'day',
			'featured'           => true,
			'event_date'         => '2019-02-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_category() {
		static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'slug' => 'test', 'name' => 'test' ] );

		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'taxonomy'           => TEC::TAXONOMY,
			TEC::TAXONOMY        => 'test',
			'event_display'      => 'default',
			'event_display_mode' => 'default',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_category_and_featured() {
		static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'slug' => 'test', 'name' => 'test' ] );

		$context = tribe_context()->alter( [
			'single'             => false,
			'event_post_type'    => true,
			'featured'           => true,
			'taxonomy'           => TEC::TAXONOMY,
			TEC::TAXONOMY        => 'test',
			'event_display'      => 'default',
			'event_display_mode' => 'default',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	/**
	 * @test
	 */
	public function should_query_only_upcoming_events_for_the_archive_when_posts_are_not_injected() {
		/* Filling the page with past events is what pushes every upcoming one out of the unconstrained query. */
		$per_page = 5;

		[ , $upcoming ] = $this->given_events_on_the_archive( $per_page, $per_page, 2 );

		$this->when_visiting_the_archive();
		$this->assertTrue( is_post_type_archive( TEC::POSTTYPE ) );

		$title = new Title();

		$this->assertEqualSets(
			wp_list_pluck( $upcoming, 'ID' ),
			wp_list_pluck( $title->get_posts(), 'ID' )
		);
	}

	/**
	 * @test
	 */
	public function should_build_the_archive_title_from_upcoming_events_when_posts_are_not_injected() {
		/* With a full page of past events the range ends in the past too, so nothing clamps the start date. */
		$per_page = 5;

		[ $past, $upcoming ] = $this->given_events_on_the_archive( $per_page, $per_page, 2 );

		$this->when_visiting_the_archive();

		$title = ( new Title() )->build_title();

		$first = tribe_get_start_date( reset( $upcoming ), false );
		$last  = tribe_get_start_date( end( $upcoming ), false );

		$this->assertStringContainsString( "$first - $last", $title );

		foreach ( $past as $past_event ) {
			$this->assertStringNotContainsString( tribe_get_start_date( $past_event, false ), $title );
		}
	}

	/**
	 * @test
	 */
	public function should_query_the_most_recent_past_events_for_the_past_archive() {
		/* Past view reads the same page size; a full page proves it orders by most recent, not oldest. */
		$per_page = 5;

		[ $past ] = $this->given_events_on_the_archive( $per_page, $per_page, 2 );

		$this->when_visiting_the_archive( [ Utils_View::get_past_event_display_key() => 'past' ] );

		$posts = ( new Title() )->get_posts();

		$this->assertEqualSets( wp_list_pluck( $past, 'ID' ), wp_list_pluck( $posts, 'ID' ) );
		$this->assertSame( end( $past )->ID, reset( $posts )->ID );
	}

	/**
	 * @test
	 */
	public function should_query_the_requested_page_of_upcoming_events_for_the_archive() {
		/* A page smaller than the upcoming events gives page 2 something to hold; no past events needed. */
		$per_page = 2;

		[ , $upcoming ] = $this->given_events_on_the_archive( $per_page, 0, 5 );

		$this->when_visiting_the_archive( [ 'paged' => 2 ] );

		$expected = array_slice( $upcoming, $per_page, $per_page );

		$this->assertSame(
			wp_list_pluck( $expected, 'ID' ),
			array_values( wp_list_pluck( ( new Title() )->get_posts(), 'ID' ) )
		);
	}

	/**
	 * @test
	 */
	public function should_have_correct_title_on_venue_single() {
		global $wp_query;
		$old_q   = clone $wp_query;
		$post_id = static::factory()->post->create( [
			'post_title' => 'Faux Venue',
			'post_type'  => \Tribe__Events__Venue::POSTTYPE
		] );

		$wp_query = new WP_Query( array( 'p' => $post_id, 'post_type' => \Tribe__Events__Venue::POSTTYPE ) );
		if ( $wp_query->have_posts() ) {
			$wp_query->the_post();
		}

		// Now validate our filter works as expected.
		$title = wp_title( '', false );
		$this->assertEquals( 'Faux Venue', trim( $title ) );

		// put old query back to avoid state bleed.
		$wp_query = $old_q;
	}

	/**
	 * @test
	 */
	public function should_have_correct_title_on_organizer_single() {
		global $wp_query;
		$old_q   = clone $wp_query;
		$post_id = static::factory()->post->create( [
			'post_title' => 'Marilyn Monroe',
			'post_type'  => \Tribe__Events__Organizer::POSTTYPE
		] );

		$wp_query = new WP_Query( array( 'p' => $post_id, 'post_type' => \Tribe__Events__Organizer::POSTTYPE ) );
		if ( $wp_query->have_posts() ) {
			$wp_query->the_post();
		}

		// Now validate our filter works as expected.
		$title = wp_title( '', false );
		$this->assertEquals( 'Marilyn Monroe', trim( $title ) );

		// put old query back to avoid state bleed.
		$wp_query = $old_q;
	}

	/**
	 * Simulates a front-end request for the events archive.
	 *
	 * The Context is a singleton that caches what it reads, so it is refreshed afterwards to make it
	 * report the request just simulated rather than the one before it.
	 *
	 * @param array<string, string|int> $query_args Query arguments to add to the archive URL.
	 */
	private function when_visiting_the_archive( array $query_args = [] ): void {
		$this->go_to( add_query_arg( [ 'post_type' => TEC::POSTTYPE ] + $query_args, '/' ) );

		tribe_context()->refresh();
	}

	/**
	 * Populates the events archive and sets how many events one page of it holds.
	 *
	 * `events_per_page` reads back from the `posts_per_page` option, so the one value sizes both the
	 * page and the unconstrained query the archive title used to run.
	 *
	 * @param int $per_page       How many events a page of the archive holds.
	 * @param int $past_count     How many past events to create.
	 * @param int $upcoming_count How many upcoming events to create.
	 *
	 * @return array{0: WP_Post[], 1: WP_Post[]} The past events and the upcoming ones, both oldest first.
	 */
	private function given_events_on_the_archive( int $per_page, int $past_count, int $upcoming_count ): array {
		update_option( 'posts_per_page', $per_page );

		$past = [];
		for ( $i = $past_count; $i > 0; $i-- ) {
			$past[] = tribe_events()->set_args( [
				'start_date' => "-$i years",
				'duration'   => HOUR_IN_SECONDS,
				'title'      => "Past Event $i",
				'status'     => 'publish',
			] )->create();
		}

		$upcoming = [];
		for ( $i = 1; $i <= $upcoming_count; $i++ ) {
			$upcoming[] = tribe_events()->set_args( [
				'start_date' => "+$i months",
				'duration'   => HOUR_IN_SECONDS,
				'title'      => "Upcoming Event $i",
				'status'     => 'publish',
			] )->create();
		}

		return [ $past, $upcoming ];
	}
}
