<?php

namespace TEC\Events\Recurrence\Views;

use Tribe\Events\Test\Testcases\TecViewTestCase;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Events\Views\V2\View;

class All_ViewTest extends TecViewTestCase {
	use With_Recurrence_Engine;

	/**
	 * It should render the occurrences archive of an event
	 *
	 * The rendered HTML is asserted structurally, not snapshotted: the Occurrence rows
	 * resolve to provisional post IDs the remapping fixtures cannot normalize, so a
	 * full-HTML snapshot would not be reproducible across environments. The marker
	 * partials carry the markup snapshots.
	 *
	 * @test
	 */
	public function should_render_the_occurrences_archive_of_an_event(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			],
			[ 'title' => 'All View Render Event' ]
		);
		wp_update_post(
			[
				'ID'        => $post->ID,
				'post_name' => 'all-view-render-event',
			]
		);

		$context = tribe_context()->alter(
			[
				'view'          => 'all',
				'name'          => 'all-view-render-event',
				'event_display' => 'all',
				'today'         => '2026-11-01 09:00:00',
				'now'           => '2026-11-01 09:00:00',
				'event_date'    => '2026-11-01',
			]
		);

		$view = View::make( All_View::class, $context );
		$html = $view->get_html();

		// One result per Occurrence of the Event.
		$this->assertCount( 3, $view->found_post_ids() );

		// The List templates render the archive.
		$this->assertStringContainsString( 'tribe-events-calendar-list', $html );
		$this->assertEquals( 3, substr_count( $html, 'tribe-events-calendar-list__event-row' ) );

		// Every Occurrence links its own date URL (plain permalinks in this suite).
		foreach ( [ '2026-11-05', '2026-11-12', '2026-11-19' ] as $date ) {
			$this->assertStringContainsString( "eventDate={$date}", $html );
		}

		// The title and breadcrumbs are the view's own.
		$this->assertStringContainsString( 'All events for All View Render Event', $html );
	}

	/**
	 * It should build the past url title and breadcrumbs
	 *
	 * @test
	 */
	public function should_build_the_past_url_title_and_breadcrumbs(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			],
			[ 'title' => 'All View Support Event' ]
		);

		$context = tribe_context()->alter(
			[
				'view' => 'all',
				'name' => $post->post_name,
			]
		);

		$view = View::make( All_View::class, $context );

		$setup = new \ReflectionMethod( All_View::class, 'setup_repository_args' );
		$setup->setAccessible( true );
		$setup->invoke( $view, $context );

		$this->assertStringContainsString( 'All events for All View Support Event', $view->setup_title( '' ) );

		$breadcrumbs = $view->setup_breadcrumbs( [], $view );
		$this->assertCount( 2, $breadcrumbs );
		$this->assertEquals( 'All View Support Event', end( $breadcrumbs )['label'] );

		$past = new \ReflectionMethod( All_View::class, 'get_past_url' );
		$past->setAccessible( true );
		$past_url = $past->invoke( $view );
		$this->assertStringContainsString( '=past', $past_url );
	}
}
