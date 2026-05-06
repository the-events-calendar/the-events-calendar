<?php

namespace Tribe\Events\Views\V2\Template;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Events__Main as TEC;
use WP_Query;

class TitleTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Post_Remapping;

	public function setUp() {
		parent::setUp();
		$return_mock_url = static function () {
			return 'http://products.tribe';
		};
		add_filter( 'option_home', $return_mock_url );
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
			'start_date' => '2022-12-01 09:00:00',
			'end_date'   => '2022-12-01 11:00:00',
		] );
		$future_event = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9002,
			'start_date' => $future . ' 09:00:00',
			'end_date'   => $future . ' 11:00:00',
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
			'start_date' => $same_day . ' 09:00:00',
			'end_date'   => $same_day . ' 10:00:00',
		] );
		$event_2 = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9102,
			'start_date' => $same_day . ' 14:00:00',
			'end_date'   => $same_day . ' 16:00:00',
		] );

		$context = tribe_context()->alter( [
			'event_post_type'    => true,
			'event_display'      => 'list',
			'event_display_mode' => 'list',
		] );

		$range = Title::build_post_range_title( $context, '', [ $event_1, $event_2 ] );

		$this->assertSame( tribe_get_start_date( $event_1, false ), $range );
		$this->assertStringNotContainsString( ' - ', $range );
	}

	public function test_post_range_title_preserves_past_dates_in_past_mode() {
		$older = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9201,
			'start_date' => '2022-01-15 09:00:00',
			'end_date'   => '2022-01-15 11:00:00',
		] );
		$newer = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'         => 9202,
			'start_date' => '2022-06-20 09:00:00',
			'end_date'   => '2022-06-20 11:00:00',
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
}
