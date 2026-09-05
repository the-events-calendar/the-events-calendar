<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Occurrences_ListTest extends WPTestCase {
	use With_Recurrence_Engine;

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
			// The edit link targets the Occurrence's provisional post directly.
			$this->assertStringContainsString( 'post.php?post=' . $rows[0]['provisional_id'] . '&action=edit', $chip['edit_link'] );
		} finally {
			update_option( 'timezone_string', $site_timezone );
		}
	}
}
