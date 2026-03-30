<?php

namespace Tribe\Events\Views\V2\SEO\Headers;

use TEC\Events\SEO\Headers\Controller;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

/**
 * Tests for the prevent_list_view_paged_404 logic in the SEO Controller.
 *
 * Background: WordPress's handle_404() has an unconditional path — when
 * is_paged() = true and $wp_query->posts is empty it always sets 404. On
 * MariaDB, TEC's Custom Tables query can return 0 rows for page 2+ even when
 * events exist, triggering spurious 404s. The fix hooks pre_handle_404 and
 * returns true (bypassing handle_404 entirely) when the request is a valid
 * paged list view, letting TEC's V2 repository render the page normally.
 */
class Controller_List_View_Paged_404_Test extends \Codeception\TestCase\WPTestCase {

	use With_Uopz;

	/**
	 * @var \WP_Query|null Original wp_the_query, restored after each test.
	 */
	private ?\WP_Query $original_wp_the_query = null;

	public function setUp(): void {
		parent::setUp();
		$this->original_wp_the_query = $GLOBALS['wp_the_query'] ?? null;
	}

	public function tearDown(): void {
		$GLOBALS['wp_the_query'] = $this->original_wp_the_query;
		parent::tearDown();
	}

	/**
	 * Build a WP_Query instance pre-configured for a paged list view request.
	 *
	 * @param int  $paged     The page number to set on the query.
	 * @param bool $make_main Register this query as the main query so that
	 *                        WP_Query::is_main_query() returns true.
	 *
	 * @return \WP_Query
	 */
	private function make_list_query( int $paged = 2, bool $make_main = true ): \WP_Query {
		$query        = new \WP_Query();
		$query->query = [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'list',
			'paged'        => $paged,
		];

		if ( $make_main ) {
			// WP_Query::is_main_query() returns true only when $this === $GLOBALS['wp_the_query'].
			$GLOBALS['wp_the_query'] = $query;
		}

		return $query;
	}

	// -------------------------------------------------------------------------
	// Tests: conditions that must NOT prevent the 404
	// -------------------------------------------------------------------------

	/**
	 * When another filter has already short-circuited pre_handle_404, we must
	 * honour its decision and pass the truthy value straight through.
	 *
	 * @test
	 */
	public function test_returns_preempt_unchanged_when_already_short_circuited(): void {
		$query = $this->make_list_query();

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( true, $query );

		$this->assertTrue( $result, 'Should return the original preempt value when it is already true.' );
	}

	/**
	 * The filter must only act on the main WP_Query.  Secondary queries (e.g.
	 * widget queries) that happen to be for tribe_events/list must be ignored.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_when_not_main_query(): void {
		// make_main = false means wp_the_query is NOT set to our query,
		// so WP_Query::is_main_query() returns false for it.
		$query = $this->make_list_query( 2, false );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 when the query is not the main query.' );
	}

	/**
	 * Non-tribe_events post types must not be affected.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_for_wrong_post_type(): void {
		$query                    = $this->make_list_query();
		$query->query['post_type'] = 'post';

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 for non-tribe_events post types.' );
	}

	/**
	 * Other TEC event displays (month, day, …) must not be affected.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_when_event_display_is_not_list(): void {
		$query                        = $this->make_list_query();
		$query->query['eventDisplay'] = 'month';

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 when eventDisplay is not "list".' );
	}

	/**
	 * When eventDisplay is absent altogether the filter must be a no-op.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_when_event_display_not_set(): void {
		$query = $this->make_list_query();
		unset( $query->query['eventDisplay'] );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 when eventDisplay is not set.' );
	}

	/**
	 * Page 1 never triggers the MariaDB paged-query bug; no intervention needed.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_for_page_1(): void {
		add_filter(
			'tribe_get_option_tribeEnableViews',
			static fn( $v ) => [ 'list' ],
		);

		$query = $this->make_list_query( 1 );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 for page 1 — WordPress does not 404 non-paged empty queries.' );
	}

	/**
	 * paged = 0 is equivalent to "not paged"; must be left alone.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_when_paged_is_zero(): void {
		add_filter(
			'tribe_get_option_tribeEnableViews',
			static fn( $v ) => [ 'list' ],
		);

		$query = $this->make_list_query( 0 );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 when paged is 0.' );
	}

	/**
	 * When the list view is not in the site's enabled views the URL itself is
	 * illegitimate, so the 404 should be allowed to stand.
	 *
	 * @test
	 */
	public function test_does_not_prevent_404_when_list_view_is_disabled(): void {
		add_filter(
			'tribe_get_option_tribeEnableViews',
			static fn( $v ) => [ 'month', 'day' ],
		);

		$query = $this->make_list_query( 2 );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertFalse( $result, 'Should not prevent 404 when the list view is disabled.' );
	}

	// -------------------------------------------------------------------------
	// Tests: conditions that MUST prevent the 404
	// -------------------------------------------------------------------------

	/**
	 * Core happy path: page 2 of the list view with list view enabled.
	 * This is the exact scenario that caused spurious 404s on MariaDB.
	 *
	 * @test
	 */
	public function test_prevents_404_for_page_2_of_list_view(): void {
		add_filter(
			'tribe_get_option_tribeEnableViews',
			static fn( $v ) => [ 'list', 'month' ],
		);

		$query = $this->make_list_query( 2 );

		tribe_register_provider( Controller::class );
		$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

		$this->assertTrue( $result, 'Should prevent the 404 for page 2 of the list view when list view is enabled.' );
	}

	/**
	 * The fix must apply to any page beyond page 1, not just page 2.
	 *
	 * @test
	 */
	public function test_prevents_404_for_higher_page_numbers(): void {
		add_filter(
			'tribe_get_option_tribeEnableViews',
			static fn( $v ) => [ 'list' ],
		);

		foreach ( [ 3, 5, 10, 99 ] as $page ) {
			$query = $this->make_list_query( $page );

			tribe_register_provider( Controller::class );
			$result = tribe( Controller::class )->prevent_list_view_paged_404( false, $query );

			$this->assertTrue( $result, "Should prevent 404 for page {$page} of the list view." );
		}
	}

	/**
	 * The filter is hooked at pre_handle_404 priority 10 with 2 args.
	 * Verify it is registered (and removed) correctly by do_register / unregister.
	 *
	 * @test
	 */
	public function test_hook_registration_and_removal(): void {
		tribe_register_provider( Controller::class );
		$controller = tribe( Controller::class );

		$this->assertNotFalse(
			has_filter( 'pre_handle_404', [ $controller, 'prevent_list_view_paged_404' ] ),
			'pre_handle_404 filter should be registered after do_register().'
		);

		$controller->unregister();
		$this->assertFalse(
			has_filter( 'pre_handle_404', [ $controller, 'prevent_list_view_paged_404' ] ),
			'pre_handle_404 filter should be removed after unregister().'
		);
	}
}
