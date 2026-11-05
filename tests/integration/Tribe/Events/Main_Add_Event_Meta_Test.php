<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;
use Tribe__Events__Meta__Save as Meta_Save;

class Main_Add_Event_Meta_Test extends WPTestCase {
	use With_Uopz;

	private int $saves = 0;

	/**
	 * @before
	 */
	public function count_the_saves(): void {
		$this->saves = 0;
		$saves       = &$this->saves;
		// The meta save needs a full edit-screen request (nonce, fields); whether `addEventMeta()` reaches it is what is tested.
		$this->set_class_fn_return(
			Meta_Save::class,
			'maybe_save',
			static function () use ( &$saves ) {
				++$saves;

				return true;
			},
			true
		);
	}

	private function create_event(): int {
		return tribe_events()->set_args(
			[
				'title'      => 'Event',
				'status'     => 'publish',
				'start_date' => '2026-12-01 10:00:00',
				'duration'   => HOUR_IN_SECONDS,
			]
		)->create()->ID;
	}

	/**
	 * Some plugins fire `save_post` with the post ID alone.
	 *
	 * @test
	 */
	public function it_should_save_the_event_meta_when_save_post_passes_only_the_post_id(): void {
		$event_id    = $this->create_event();
		$this->saves = 0;

		TEC::instance()->addEventMeta( $event_id );

		$this->assertSame( 1, $this->saves );
	}

	/**
	 * A call without a post must not leave the recursion guard set for the rest of the request.
	 *
	 * @test
	 */
	public function it_should_still_save_the_next_event_after_a_call_without_a_post(): void {
		$event_id    = $this->create_event();
		$this->saves = 0;

		TEC::instance()->addEventMeta( $event_id + 100000, null );
		TEC::instance()->addEventMeta( $event_id, get_post( $event_id ) );

		$this->assertSame( 1, $this->saves );
	}

	/**
	 * @test
	 */
	public function it_should_do_nothing_when_save_post_passes_something_that_is_not_a_post(): void {
		TEC::instance()->addEventMeta( 'not-a-post-id', 'not-a-post' );

		$this->assertSame( 0, $this->saves );
	}
}
