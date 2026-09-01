<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use ReflectionMethod;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Views\All_View;
use Tribe\Events\Views\V2\Manager;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

class All_Occurrences_ProviderTest extends WPTestCase {
	/**
	 * The URL captured from the last prevented redirect, or `null` when none fired.
	 *
	 * Static, and captured by a static method: closure callbacks leak across tests in
	 * this suite (the hooks snapshot resurrects them keyed by a stale object hash) and
	 * a leaked capture closure would feed `false` to the current one. A static method
	 * has a stable hook ID: re-adding overwrites, removing always matches.
	 *
	 * @var string|false|null
	 */
	public static $redirected_to;

	/**
	 * Captures a prevented redirect location.
	 *
	 * @param string|false $location The redirect location.
	 *
	 * @return false Always `false` to prevent the redirect header.
	 */
	public static function capture_redirect( $location ) {
		self::$redirected_to = $location;

		return false;
	}

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

		// Capture redirects instead of sending headers and dying.
		self::$redirected_to = null;
		add_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		add_filter( 'tribe_exit', static fn() => '__return_true' );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		remove_all_filters( 'tribe_exit' );
		remove_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_a_multi_date_event( array $dates, string $start = '2026-11-05 09:00:00', string $end = '2026-11-05 10:00:00' ): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'All Occurrences Test Event',
				'status'     => 'publish',
				'start_date' => $start,
				'end_date'   => $end,
				'timezone'   => 'UTC',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		$this->assertTrue( tribe( Dates_Service::class )->set_dates( $post->ID, $dates ) );

		return $post;
	}

	private function given_a_dateless_main_query_for( WP_Post $post ): WP_Query {
		global $wp_query, $wp_the_query;

		$query                          = new WP_Query();
		$query->query_vars              = [
			TEC::POSTTYPE => $post->post_name,
			'post_type'   => TEC::POSTTYPE,
			'name'        => $post->post_name,
		];
		$query->queried_object          = $post;
		$query->queried_object_id       = (int) $post->ID;
		$wp_query                       = $query;
		$wp_the_query                   = $query;

		return $query;
	}

	/**
	 * It should register the all view
	 *
	 * @test
	 */
	public function should_register_the_all_view(): void {
		$views = tribe( Manager::class )->get_registered_views();

		$this->assertArrayHasKey( 'all', $views );
		$this->assertSame( All_View::class, $views['all'] );
		$this->assertArrayNotHasKey( 'all', tribe( Manager::class )->get_publicly_visible_views() );
	}

	/**
	 * It should scope the all view repository to the event occurrences
	 *
	 * @test
	 */
	public function should_scope_the_all_view_repository_to_the_event_occurrences(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			]
		);

		$context = tribe_context()->alter(
			[
				'view'         => 'all',
				'name'         => $post->post_name,
				'event_display' => 'all',
			]
		);

		$view = View::make( All_View::class, $context );

		$setup = new ReflectionMethod( All_View::class, 'setup_repository_args' );
		$setup->setAccessible( true );
		$args = $setup->invoke( $view, $context );

		$this->assertSame( $post->post_name, $args['name'] ?? null );
		$this->assertArrayNotHasKey( 'in_series', $args );
		$this->assertArrayNotHasKey( 'hide_subsequent_recurrences', $args );
		$this->assertSame( $post->ID, $view->get_target_post_id() );

		// One result per Occurrence of the Event, nothing else.
		$matched = tribe_events()->by_args( $args )->count();
		$this->assertEquals( 3, $matched );
	}

	/**
	 * It should build the all view url from its query vars
	 *
	 * @test
	 */
	public function should_build_the_all_view_url_from_its_query_vars(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$context = tribe_context()->alter(
			[
				'view' => 'all',
				'name' => $post->post_name,
			]
		);

		$view = View::make( All_View::class, $context );

		$setup = new ReflectionMethod( All_View::class, 'setup_repository_args' );
		$setup->setAccessible( true );
		$setup->invoke( $view, $context );

		$url = $view->get_url();

		$this->assertStringContainsString( 'eventDisplay=all', $url );
		$this->assertStringContainsString( 'tribe_recurrence_list=1', $url );
		$this->assertStringContainsString( 'tribe_events=' . $post->post_name, $url );
	}

	/**
	 * It should build the all occurrences link for real and provisional ids
	 *
	 * @test
	 */
	public function should_build_the_all_occurrences_link_for_real_and_provisional_ids(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$link = tribe_all_occurrences_link( $post->ID, false );

		$this->assertNotEmpty( $link );
		$this->assertStringContainsString( 'eventDisplay=all', $link );

		$occurrence = Occurrence::where( 'post_id', $post->ID )->order_by( 'start_date', 'DESC' )->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$provisional_id = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;

		$this->assertSame( $link, tribe_all_occurrences_link( $provisional_id, false ) );
	}

	/**
	 * It should decorate events with the all occurrences permalink
	 *
	 * @test
	 */
	public function should_decorate_events_with_the_all_occurrences_permalink(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$decorated = tribe_get_event( $post->ID, OBJECT, 'raw', true );

		$this->assertTrue( (bool) $decorated->recurring );
		$this->assertSame( tribe_all_occurrences_link( $post->ID, false ), $decorated->permalink_all );
	}

	/**
	 * It should redirect the dateless url to the next upcoming occurrence
	 *
	 * @test
	 */
	public function should_redirect_the_dateless_url_to_the_next_upcoming_occurrence(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			]
		);

		$this->given_a_dateless_main_query_for( $post );

		tribe( All_Occurrences_Provider::class )->redirect_dateless_request();

		$this->assertNotNull( self::$redirected_to, 'A redirect should have been triggered.' );
		// The event date is in the future: the first Occurrence is the next upcoming one.
		$this->assertStringContainsString( 'eventDate=2026-11-05', self::$redirected_to );
	}

	/**
	 * It should redirect the dateless url to the archive when all occurrences are past
	 *
	 * @test
	 */
	public function should_redirect_the_dateless_url_to_the_archive_when_all_occurrences_are_past(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2020-02-12 09:00:00', 'end' => '2020-02-12 10:00:00' ],
			],
			'2020-02-05 09:00:00',
			'2020-02-05 10:00:00'
		);

		$this->given_a_dateless_main_query_for( $post );

		tribe( All_Occurrences_Provider::class )->redirect_dateless_request();

		$this->assertNotNull( self::$redirected_to, 'A redirect should have been triggered.' );
		$this->assertStringContainsString( 'eventDisplay=all', self::$redirected_to );
	}

	/**
	 * It should not redirect events with a single occurrence
	 *
	 * @test
	 */
	public function should_not_redirect_events_with_a_single_occurrence(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Single Occurrence Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$this->given_a_dateless_main_query_for( $post );

		tribe( All_Occurrences_Provider::class )->redirect_dateless_request();

		$this->assertNull( self::$redirected_to );
	}

	/**
	 * It should not redirect dated, archive, feed or embed requests
	 *
	 * @test
	 */
	public function should_not_redirect_dated_archive_feed_or_embed_requests(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$provider = tribe( All_Occurrences_Provider::class );

		// A dated request resolves to a single Occurrence already.
		$query                          = $this->given_a_dateless_main_query_for( $post );
		$query->query_vars['eventDate'] = '2026-11-12';
		$provider->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );

		// The Occurrences archive request must never redirect: it would loop.
		$query = $this->given_a_dateless_main_query_for( $post );
		$query->query_vars['eventDisplay'] = 'all';
		$provider->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );

		$query = $this->given_a_dateless_main_query_for( $post );
		$query->query_vars['tribe_recurrence_list'] = 1;
		$provider->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );

		// Feeds and embeds render collapsed instead.
		$query          = $this->given_a_dateless_main_query_for( $post );
		$query->is_feed = true;
		$provider->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );

		$query           = $this->given_a_dateless_main_query_for( $post );
		$query->is_embed = true;
		$provider->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );
	}

	/**
	 * It should allow filtering the redirect url
	 *
	 * @test
	 */
	public function should_allow_filtering_the_redirect_url(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$this->given_a_dateless_main_query_for( $post );

		// An empty string cancels the redirect.
		add_filter( 'tec_events_recurrence_dateless_redirect_url', '__return_empty_string' );
		tribe( All_Occurrences_Provider::class )->redirect_dateless_request();
		$this->assertNull( self::$redirected_to );
		remove_filter( 'tec_events_recurrence_dateless_redirect_url', '__return_empty_string' );

		// A replacement URL wins; a same-host one, `wp_safe_redirect` validates hosts.
		$custom  = home_url( '/custom/' );
		$replace = static fn() => $custom;
		add_filter( 'tec_events_recurrence_dateless_redirect_url', $replace );
		tribe( All_Occurrences_Provider::class )->redirect_dateless_request();
		$this->assertSame( $custom, self::$redirected_to );
		remove_filter( 'tec_events_recurrence_dateless_redirect_url', $replace );
	}

	/**
	 * It should collapse the dateless singular query results to the next upcoming occurrence
	 *
	 * @test
	 */
	public function should_collapse_the_dateless_singular_query_results(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			]
		);

		global $wp_the_query;

		$query        = new WP_Query();
		$wp_the_query = $query;
		$query->query(
			[
				TEC::POSTTYPE => $post->post_name,
				'post_type'   => TEC::POSTTYPE,
				'name'        => $post->post_name,
			]
		);

		$this->assertCount( 1, $query->posts, 'The dateless singular query must resolve to one Occurrence.' );

		$kept       = reset( $query->posts );
		$occurrence = Occurrence::where( 'post_id', $post->ID )->order_by( 'start_date', 'ASC' )->first();

		$this->assertInstanceOf( Occurrence::class, $occurrence );
		$expected_id = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
		$this->assertEquals( $expected_id, $kept->ID );
	}

	/**
	 * It should not collapse the occurrences archive query results
	 *
	 * @test
	 */
	public function should_not_collapse_the_occurrences_archive_query_results(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			]
		);

		global $wp_the_query;

		$query        = new WP_Query();
		$wp_the_query = $query;
		$query->query(
			[
				TEC::POSTTYPE           => $post->post_name,
				'post_type'             => TEC::POSTTYPE,
				'name'                  => $post->post_name,
				'eventDisplay'          => 'all',
				'tribe_recurrence_list' => 1,
			]
		);

		$this->assertCount( 3, $query->posts, 'The archive query must keep one result per Occurrence.' );
	}

	/**
	 * It should prevent the paged archive 404
	 *
	 * @test
	 */
	public function should_prevent_the_paged_archive_404(): void {
		global $wp_the_query;

		$query                                       = new WP_Query();
		$wp_the_query                                = $query;
		$query->query_vars['tribe_recurrence_list'] = 1;
		$query->query_vars['page']                  = 2;

		$this->assertTrue(
			tribe( All_Occurrences_Provider::class )->prevent_all_view_paged_404( false, $query )
		);

		// Not on the first page.
		$query->query_vars['page'] = 1;
		$this->assertFalse(
			tribe( All_Occurrences_Provider::class )->prevent_all_view_paged_404( false, $query )
		);

		// Not on unrelated requests.
		$unrelated                       = new WP_Query();
		$wp_the_query                    = $unrelated;
		$unrelated->query_vars['page'] = 2;
		$this->assertFalse(
			tribe( All_Occurrences_Provider::class )->prevent_all_view_paged_404( false, $unrelated )
		);
	}

	/**
	 * It should produce dated front end permalinks for provisional posts only
	 *
	 * @test
	 */
	public function should_produce_dated_front_end_permalinks_for_provisional_posts_only(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$this->assertFalse( is_admin() );

		// The real Event post keeps the dateless permalink on the front end.
		$this->assertStringNotContainsString( 'eventDate', get_permalink( $post->ID ) );

		$occurrence = Occurrence::where( 'post_id', $post->ID )->order_by( 'start_date', 'ASC' )->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$provisional_id = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );

		$this->assertStringContainsString( 'eventDate=2026-11-05', get_permalink( $provisional_id ) );
	}
}
