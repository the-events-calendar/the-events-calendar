<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Occurrences_ListTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * @after
	 */
	public function reset_list_state(): void {
		unset( $_GET[ Occurrences_List::VIEW_VAR ], $_GET[ Occurrences_List::PAGE_VAR ] );
		remove_all_filters( 'tec_events_recurrence_occurrences_list_per_page' );
	}

	private function given_an_event_with_dates( array $dates, string $start = '' ): WP_Post {
		$start = $start ?: date( 'Y-m-d 09:00:00', strtotime( '+5 days' ) );

		$post = tribe_events()->set_args(
			[
				'title'      => 'Occurrences List Test Event',
				'status'     => 'publish',
				'start_date' => $start,
				'end_date'   => date( 'Y-m-d H:i:s', strtotime( $start ) + HOUR_IN_SECONDS ),
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $post );

		if ( count( $dates ) ) {
			$this->assertTrue( tribe( Dates_Service::class )->set_dates( $post->ID, $dates ) );
		}

		return $post;
	}

	private function day_period( string $modifier ): array {
		return [
			'start' => date( 'Y-m-d 09:00:00', strtotime( $modifier ) ),
			'end'   => date( 'Y-m-d 10:00:00', strtotime( $modifier ) ),
		];
	}

	/**
	 * It should count the scheduled occurrences, accepting a provisional ID
	 *
	 * @test
	 */
	public function should_count_the_scheduled_occurrences(): void {
		$post = $this->given_an_event_with_dates( [ $this->day_period( '+10 days' ), $this->day_period( '+20 days' ) ] );
		$list = tribe( Occurrences_List::class );

		$this->assertEquals( 3, $list->get_count( $post->ID ) );

		$occurrence     = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		$this->assertEquals( 3, $list->get_count( $provisional_id ) );

		$single = $this->given_an_event_with_dates( [] );
		$this->assertEquals( 1, $list->get_count( $single->ID ) );

		tribe()->setVar( 'ct1_fully_activated', false );
		$this->assertEquals( 0, $list->get_count( $post->ID ) );
		tribe()->setVar( 'ct1_fully_activated', true );
	}

	/**
	 * It should list the dates in order with their provisional IDs
	 *
	 * @test
	 */
	public function should_list_the_dates_in_order_with_provisional_ids(): void {
		$post = $this->given_an_event_with_dates( [ $this->day_period( '+20 days' ), $this->day_period( '+10 days' ) ] );

		$_GET[ Occurrences_List::VIEW_VAR ] = 'all';
		$data                               = tribe( Occurrences_List::class )->get_page_data( $post->ID );

		$this->assertEquals( 'all', $data['view'] );
		$this->assertEquals( 3, $data['total'] );
		$this->assertEquals( 1, $data['total_pages'] );
		$this->assertCount( 3, $data['rows'] );

		$starts = array_map( static fn( $row ) => $row['start']->format( 'Y-m-d H:i:s' ), $data['rows'] );
		$this->assertEquals(
			[
				date( 'Y-m-d 09:00:00', strtotime( '+5 days' ) ),
				date( 'Y-m-d 09:00:00', strtotime( '+10 days' ) ),
				date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ),
			],
			$starts
		);

		$base = tribe( ID_Generator::class )->current();
		foreach ( $data['rows'] as $row ) {
			$this->assertGreaterThan( $base, $row['provisional_id'] );
		}
	}

	/**
	 * It should filter the upcoming view and paginate
	 *
	 * @test
	 */
	public function should_filter_upcoming_and_paginate(): void {
		$post = $this->given_an_event_with_dates(
			[ $this->day_period( '+10 days' ), $this->day_period( '+20 days' ), $this->day_period( '-10 days' ) ],
			date( 'Y-m-d 09:00:00', strtotime( '-20 days' ) )
		);

		$list = tribe( Occurrences_List::class );

		// Upcoming: the two future dates only.
		$data = $list->get_page_data( $post->ID );
		$this->assertEquals( 'upcoming', $data['view'] );
		$this->assertEquals( 2, $data['total'] );

		// All, two per page: two pages, page 2 has the two most recent dates.
		add_filter( 'tec_events_recurrence_occurrences_list_per_page', static fn() => 2 );
		$_GET[ Occurrences_List::VIEW_VAR ] = 'all';
		$_GET[ Occurrences_List::PAGE_VAR ] = '2';

		$data = $list->get_page_data( $post->ID );
		$this->assertEquals( 4, $data['total'] );
		$this->assertEquals( 2, $data['total_pages'] );
		$this->assertEquals( 2, $data['page'] );
		$this->assertCount( 2, $data['rows'] );
		$this->assertEquals( date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ), end( $data['rows'] )['start']->format( 'Y-m-d H:i:s' ) );

		// An out-of-range page clamps to the last one.
		$_GET[ Occurrences_List::PAGE_VAR ] = '99';
		$data                               = $list->get_page_data( $post->ID );
		$this->assertEquals( 2, $data['page'] );
	}

	/**
	 * It should register the metabox for multi-date events only
	 *
	 * @test
	 */
	public function should_register_the_metabox_for_multi_date_events_only(): void {
		global $wp_meta_boxes;
		$provider = tribe( Admin_Provider::class );

		$multi         = $this->given_an_event_with_dates( [ $this->day_period( '+10 days' ) ] );
		$wp_meta_boxes = [];
		$provider->register_occurrences_metabox( get_post( $multi->ID ) );
		$this->assertNotEmpty( $wp_meta_boxes['tribe_events']['normal']['low']['tec-events-recurrence-occurrences'] ?? null );

		$single        = $this->given_an_event_with_dates( [] );
		$wp_meta_boxes = [];
		$provider->register_occurrences_metabox( get_post( $single->ID ) );
		$this->assertEmpty( $wp_meta_boxes );
	}

	/**
	 * It should render View links pointing at each occurrence date
	 *
	 * @test
	 */
	public function should_render_view_links_pointing_at_each_occurrence_date(): void {
		set_current_screen( 'post' );
		// The Links provider hooks are restored away between tests while the di52 provider registry is not: re-hook directly.
		tribe( \TEC\Events\Custom_Tables\V1\Links\Provider::class )->register();

		try {
			$post = $this->given_an_event_with_dates( [ $this->day_period( '+10 days' ) ] );

			$_GET[ Occurrences_List::VIEW_VAR ] = 'all';
			ob_start();
			tribe( Admin_Provider::class )->render_occurrences_metabox( get_post( $post->ID ) );
			$html = ob_get_clean();

			// One View link per date, each carrying its own date.
			$this->assertEquals( 2, substr_count( $html, 'rel="noreferrer noopener"' ) );
			$this->assertStringContainsString( date( 'Y-m-d', strtotime( '+5 days' ) ), $html );
			$this->assertStringContainsString( date( 'Y-m-d', strtotime( '+10 days' ) ), $html );
		} finally {
			tribe( \TEC\Events\Custom_Tables\V1\Links\Provider::class )->unregister();
			set_current_screen( 'front' );
		}
	}

	/**
	 * It should list every scheduled date with its status and link
	 *
	 * @test
	 */
	public function should_list_every_scheduled_date_with_status_and_link(): void {
		// The Links provider hooks are restored away between tests while the di52 provider registry is not: re-hook directly.
		tribe( \TEC\Events\Custom_Tables\V1\Links\Provider::class )->register();

		try {
			$post = $this->given_an_event_with_dates(
				[ $this->day_period( '+10 days' ), $this->day_period( '+20 days' ), $this->day_period( '-10 days' ) ],
				date( 'Y-m-d 09:00:00', strtotime( '-20 days' ) )
			);
			$list = tribe( Occurrences_List::class );

			$rows = $list->get_scheduled_dates( $post->ID );

			$this->assertCount( 4, $rows );
			$this->assertEquals( [ 'past', 'past', 'next', 'upcoming' ], array_column( $rows, 'status' ) );
			$this->assertEquals(
				[
					date( 'Y-m-d 09:00:00', strtotime( '-20 days' ) ),
					date( 'Y-m-d 09:00:00', strtotime( '-10 days' ) ),
					date( 'Y-m-d 09:00:00', strtotime( '+10 days' ) ),
					date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ),
				],
				array_map( static fn( array $row ) => $row['start']->format( 'Y-m-d H:i:s' ), $rows )
			);

			$base = tribe( ID_Generator::class )->current();
			foreach ( $rows as $row ) {
				// The dates are built in the Event timezone, the one the wall-clock values are stored in.
				$this->assertEquals( 'America/Sao_Paulo', $row['start']->getTimezone()->getName() );
				$this->assertGreaterThan( $base, $row['provisional_id'] );
				// Each link is the dated Occurrence URL.
				$this->assertStringContainsString( $row['start']->format( 'Y-m-d' ), $row['permalink'] );
			}

			// A provisional ID resolves to the same Event.
			$this->assertCount( 4, $list->get_scheduled_dates( $rows[0]['provisional_id'] ) );

			tribe()->setVar( 'ct1_fully_activated', false );
			$this->assertEquals( [], $list->get_scheduled_dates( $post->ID ) );
			tribe()->setVar( 'ct1_fully_activated', true );
		} finally {
			tribe( \TEC\Events\Custom_Tables\V1\Links\Provider::class )->unregister();
		}
	}

	/**
	 * It should format chips in the event timezone regardless of the site one
	 *
	 * The Occurrences table stores Event-local wall-clock values; formatting them as
	 * UTC epochs shifted by the site offset (the previous `date_i18n()` path) showed
	 * wrong times on any non-UTC site.
	 *
	 * @test
	 */
	public function should_format_chips_in_the_event_timezone(): void {
		$site_timezone = get_option( 'timezone_string' );
		update_option( 'timezone_string', 'Asia/Tokyo' );

		try {
			$post = $this->given_an_event_with_dates( [], '2050-06-15 18:00:00' );
			$list = tribe( Occurrences_List::class );

			$rows = $list->get_scheduled_dates( $post->ID );
			$this->assertCount( 1, $rows );

			$chip = $list->format_chip( $rows[0] );

			$this->assertEquals( 'June 15, 2050', $chip['label'] );
			$this->assertStringContainsString( 'June 15, 2050 @ 6:00 pm – 7:00 pm', $chip['tooltip'][0] );
			$this->assertEquals( 'Next occurrence', $chip['tooltip'][1] );
			$this->assertEquals( 'next', $chip['status'] );
		} finally {
			update_option( 'timezone_string', $site_timezone );
		}
	}
}
