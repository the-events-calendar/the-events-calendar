<?php
/**
 * Provides the activation ritual for the Recurrence (Occurrences) engine in test cases,
 * plus the multi-date Event fixture the feature tests share.
 *
 * GOTCHA the trait bakes in: hook-capture callbacks in these suites MUST be static
 * methods (or `[ $this, 'method' ]` on the test case) — bare closures leak across tests
 * because the test case hooks snapshot resurrects them keyed by a stale object hash,
 * and a leaked capture poisons later tests.
 *
 * @package Tribe\Events\Test\Traits
 */

namespace Tribe\Events\Test\Traits;

use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Recurrence\Controller;
use TEC\Events\Recurrence\Dates_Service;
use WP_Post;

trait With_Recurrence_Engine {
	/**
	 * Activates the Recurrence engine, forcing a re-registration of the gated feature.
	 *
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model extensions cache: it may have been locked before the engine registered.
		Model::reset_extensions();
	}

	/**
	 * Deactivates the Recurrence engine, symmetrically to the activation.
	 *
	 * @after
	 */
	public function deactivate_recurrence_engine(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		Model::reset_extensions();
	}

	/**
	 * Creates a published Event with additional explicit dates authored through the
	 * Dates_Service, i.e. a multi-Occurrence (recurring) Event.
	 *
	 * @param array<int,array{start: string, end: string}> $dates      The additional dates; a default
	 *                                                                 one-week-later date when empty.
	 * @param array<string,mixed>                          $event_args Overrides for the Event creation arguments.
	 *
	 * @return WP_Post The created Event post.
	 */
	protected function given_a_multi_date_event( array $dates = [], array $event_args = [] ): WP_Post {
		$post = tribe_events()->set_args(
			array_merge(
				[
					'title'      => 'Multi Date Test Event',
					'status'     => 'publish',
					'start_date' => '2026-11-05 09:00:00',
					'end_date'   => '2026-11-05 10:00:00',
					'timezone'   => 'UTC',
				],
				$event_args
			)
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		if ( ! $dates ) {
			$dates = [
				[
					'start' => '2026-11-12 09:00:00',
					'end'   => '2026-11-12 10:00:00',
				],
			];
		}

		$this->assertTrue( tribe( Dates_Service::class )->set_dates( $post->ID, $dates ) );

		return $post;
	}
}
