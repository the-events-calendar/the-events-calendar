<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as TEC_Rewrite;

class Frontend_ProviderTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
	}

	/**
	 * It should add the all base slug
	 *
	 * @test
	 */
	public function should_add_the_all_base_slug(): void {
		$bases = tribe( Frontend_Provider::class )->add_all_base_slug( [ 'single' => 'event' ] );

		$this->assertArrayHasKey( 'all', $bases );
		$this->assertSame( [ 'all', 'all' ], $bases['all'] );
		$this->assertSame( 'event', $bases['single'] );
	}

	/**
	 * It should whitelist the recurrence list query var
	 *
	 * @test
	 */
	public function should_whitelist_the_recurrence_list_query_var(): void {
		$this->assertContains(
			'tribe_recurrence_list',
			tribe( Frontend_Provider::class )->add_query_vars( [ 'paged' ] )
		);
		// The registration is live too.
		$this->assertContains( 'tribe_recurrence_list', apply_filters( 'query_vars', [] ) );
	}

	/**
	 * It should register the all rewrite rules
	 *
	 * @test
	 */
	public function should_register_the_all_rewrite_rules(): void {
		global $wp_rewrite;
		// The `matches` prefix is set during `WP_Rewrite::rewrite_rules()`.
		$wp_rewrite->matches = 'matches';
		$rewrite             = new TEC_Rewrite( $wp_rewrite );
		$rewrite->setup( $wp_rewrite );

		do_action( 'tribe_events_pre_rewrite', $rewrite );

		$all_rules = array_filter(
			$rewrite->rules,
			static function ( $query ) {
				return false !== strpos( $query, 'tribe_recurrence_list' );
			}
		);

		$this->assertCount( 2, $all_rules );

		$queries = array_values( $all_rules );
		sort( $queries );

		$this->assertSame(
			[
				'index.php?tribe_events=$matches[1]&post_type=tribe_events&eventDisplay=all&tribe_recurrence_list=1',
				'index.php?tribe_events=$matches[1]&post_type=tribe_events&eventDisplay=all&tribe_recurrence_list=1&page=$matches[2]',
			],
			$queries
		);

		foreach ( array_keys( $all_rules ) as $regex ) {
			$this->assertStringContainsString( '(?:all)', $regex );
		}
	}

	/**
	 * It should resolve the all query vars to the pretty URL form
	 *
	 * @test
	 */
	public function should_resolve_the_all_query_vars_to_the_pretty_url_form(): void {
		$matchers = tribe( Frontend_Provider::class )->add_dynamic_matchers(
			[],
			[
				TEC::POSTTYPE           => 'some-event',
				'tribe_recurrence_list' => true,
			],
			TEC_Rewrite::instance()
		);

		$this->assertNotEmpty( $matchers );

		$values = array_values( $matchers );

		$this->assertSame( 'some-event/all', reset( $values ) );
	}

	/**
	 * It should not add dynamic matchers to unrelated query vars
	 *
	 * @test
	 */
	public function should_not_add_dynamic_matchers_to_unrelated_query_vars(): void {
		$matchers = tribe( Frontend_Provider::class )->add_dynamic_matchers(
			[ 'foo' => 'bar' ],
			[ TEC::POSTTYPE => 'some-event' ],
			TEC_Rewrite::instance()
		);

		$this->assertSame( [ 'foo' => 'bar' ], $matchers );
	}

	/**
	 * It should mark the single event rules as handled
	 *
	 * @test
	 */
	public function should_mark_the_single_event_rules_as_handled(): void {
		$all_rules = [
			'(?:event)/([^/]+)/(?:all)/?$'                => 'index.php?tribe_events=$matches[1]&post_type=tribe_events&eventDisplay=all&tribe_recurrence_list=1',
			'(?:event)/([^/]+)/(\d{4}-\d{2}-\d{2})/?$'    => 'index.php?tribe_events=$matches[1]&eventDate=$matches[2]',
			'(?:events)/(?:list)/?$'                      => 'index.php?post_type=tribe_events&eventDisplay=list',
			'unrelated/?$'                                => 'index.php?page_id=2',
		];

		// Bust the memoized value other tests may have primed.
		tribe_cache()['tec_recurrence_handled_rewrite_rules'] = null;

		$handled = tribe( Frontend_Provider::class )->add_handled_rewrite_rules( [], $all_rules );

		$this->assertArrayHasKey( '(?:event)/([^/]+)/(?:all)/?$', $handled );
		$this->assertArrayHasKey( '(?:event)/([^/]+)/(\d{4}-\d{2}-\d{2})/?$', $handled );
		$this->assertArrayNotHasKey( '(?:events)/(?:list)/?$', $handled );
		$this->assertArrayNotHasKey( 'unrelated/?$', $handled );
	}
}
