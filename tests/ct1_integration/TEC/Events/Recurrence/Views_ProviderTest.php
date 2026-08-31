<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main as TEC;
use Tribe__Template;
use WP_Post;

class Views_ProviderTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model static extensions cache: it may have been locked before the engine registered.
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_a_multi_date_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Views Provider Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		tribe( Dates_Service::class )->set_dates(
			$post->ID,
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		return $post;
	}

	private function events_template(): Tribe__Template {
		$template = new Tribe__Template();
		$template->set_template_origin( TEC::instance() )
				->set_template_folder( 'src/views/v2' )
				->set_template_folder_lookup( true )
				->set_template_context_extract( true );

		return $template;
	}

	/**
	 * It should decorate the event object with the recurring flag
	 *
	 * @test
	 */
	public function should_decorate_the_event_object_with_the_recurring_flag(): void {
		$post = $this->given_a_multi_date_event();

		$decorated = tribe_get_event( $post->ID, OBJECT, 'raw', true );

		$this->assertTrue( (bool) $decorated->recurring );
		$this->assertNotEmpty( $decorated->permalink_all );

		$single = tribe_events()->set_args(
			[
				'title'      => 'Plain Single',
				'status'     => 'publish',
				'start_date' => '2026-11-06 09:00:00',
				'end_date'   => '2026-11-06 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$decorated_single = tribe_get_event( $single->ID, OBJECT, 'raw', true );

		$this->assertFalse( (bool) $decorated_single->recurring );
		$this->assertEmpty( $decorated_single->permalink_all );
	}

	/**
	 * It should render the recurring marker for recurring events only
	 *
	 * @test
	 */
	public function should_render_the_recurring_marker_for_recurring_events_only(): void {
		$post      = $this->given_a_multi_date_event();
		$decorated = tribe_get_event( $post->ID, OBJECT, 'raw', true );

		$html = $this->events_template()->template( 'list/event/recurring', [ 'event' => $decorated ], false );

		$this->assertStringContainsString( 'tribe-events-calendar-list__event-datetime-recurring-link', $html );
		$this->assertStringContainsString( 'tribe-common-c-svgicon--recurring', $html );

		$not_recurring            = clone $decorated;
		$not_recurring->recurring = false;

		$empty = $this->events_template()->template( 'list/event/recurring', [ 'event' => $not_recurring ], false );

		$this->assertStringNotContainsString( 'recurring-link', (string) $empty );
	}

	/**
	 * It should inject the marker after the date meta template
	 *
	 * @test
	 */
	public function should_inject_the_marker_after_the_date_meta_template(): void {
		$post      = $this->given_a_multi_date_event();
		$decorated = tribe_get_event( $post->ID, OBJECT, 'raw', true );

		$template = $this->events_template();
		// Prime the template context the injected marker will read.
		$template->add_template_globals( [ 'event' => $decorated ] );

		ob_start();
		do_action(
			'tribe_template_after_include:events/v2/list/event/date/meta',
			'list/event/date/meta',
			[ 'list', 'event', 'date', 'meta' ],
			$template
		);
		$html = ob_get_clean();

		$this->assertStringContainsString( 'tribe-events-calendar-list__event-datetime-recurring-link', $html );
	}
}
