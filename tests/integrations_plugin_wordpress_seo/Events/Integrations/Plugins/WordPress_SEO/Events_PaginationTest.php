<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Codeception\TestCase\WPTestCase;

/**
 * Class Events_PaginationTest
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_PaginationTest extends WPTestCase {

	/**
	 * @var Events_Pagination
	 */
	protected $sut;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->sut = new Events_Pagination();
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		// Clean up any filters that might have been added.
		remove_all_filters( 'wpseo_next_rel_link' );
		remove_all_filters( 'wpseo_prev_rel_link' );
		remove_all_actions( 'template_redirect' );
		
		// Reset uopz mocks.
		uopz_unset_return( 'tribe_is_event_query' );
		
		// Reset global query state.
		global $wp_the_query, $wp_query;
		$wp_the_query = null;
		$wp_query = null;
	}

	/**
	 * Helper method to set up and execute the pagination disabler.
	 *
	 * @since TBD
	 *
	 * @param mixed $tribe_is_event_query_return The return value for tribe_is_event_query function.
	 *
	 * @return void
	 */
	private function setup_and_execute_pagination_disabler( $tribe_is_event_query_return ): void {
		// Mock the tribe_is_event_query function.
		uopz_set_return( 'tribe_is_event_query', $tribe_is_event_query_return );

		// Register the pagination disabler.
		$this->sut->register();

		// Directly call the method instead of triggering template_redirect.
		$this->sut->disable_yoast_pagination();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$this->assertInstanceOf( Events_Pagination::class, $this->sut );
	}

	/**
	 * @test
	 */
	public function it_should_register_template_redirect_hook() {
		global $wp_filter;
		
		$this->sut->register();
		
		$this->assertArrayHasKey( 'template_redirect', $wp_filter );
		$this->assertEquals( 5, has_action( 'template_redirect', [ $this->sut, 'disable_yoast_pagination' ] ) );
	}

	/**
	 * Data provider for pagination filter tests.
	 *
	 * @since TBD
	 *
	 * @return \Generator
	 */
	public function pagination_filter_data_provider(): \Generator {
		yield 'event query should disable pagination' => [
			true,  // tribe_is_event_query return value
			true,  // should filters be applied
			'Event queries should disable Yoast pagination filters.',
		];

		yield 'non-event query should not disable pagination' => [
			false, // tribe_is_event_query return value
			false, // should filters be applied
			'Non-event queries should not disable Yoast pagination filters.',
		];

		yield 'missing tribe_is_event_query function should not disable pagination' => [
			null,  // tribe_is_event_query return value (function doesn't exist)
			false, // should filters be applied
			'Missing tribe_is_event_query function should not disable pagination.',
		];
	}

	/**
	 * @dataProvider pagination_filter_data_provider
	 * @test
	 */
	public function it_should_handle_pagination_filters_correctly( $tribe_is_event_query_return, $should_apply_filters, $message ) {
		$this->setup_and_execute_pagination_disabler( $tribe_is_event_query_return );

		// Check that the filters are applied or not applied as expected.
		if ( $should_apply_filters ) {
			$this->assertNotFalse( has_filter( 'wpseo_next_rel_link', '__return_false' ), $message );
			$this->assertNotFalse( has_filter( 'wpseo_prev_rel_link', '__return_false' ), $message );
		} else {
			$this->assertFalse( has_filter( 'wpseo_next_rel_link', '__return_false' ), $message );
			$this->assertFalse( has_filter( 'wpseo_prev_rel_link', '__return_false' ), $message );
		}
	}

	/**
	 * @test
	 */
	public function it_should_register_hook_with_correct_priority() {
		$this->sut->register();
		
		// Check that the hook is registered with the correct callback and priority.
		$this->assertEquals( 5, has_action( 'template_redirect', [ $this->sut, 'disable_yoast_pagination' ] ) );
		
		// Verify the priority by checking the global filter array.
		global $wp_filter;
		$template_redirect_hooks = $wp_filter['template_redirect'] ?? null;
		$this->assertNotNull( $template_redirect_hooks, 'template_redirect hook should be registered.' );
		
		// Check that our callback is registered at priority 5.
		$callbacks = $template_redirect_hooks->callbacks ?? [];
		$this->assertArrayHasKey( 5, $callbacks, 'template_redirect hook should be registered with priority 5.' );
	}

	/**
	 * Data provider for filter behavior tests.
	 *
	 * @since TBD
	 *
	 * @return \Generator
	 */
	public function filter_behavior_data_provider(): \Generator {
		yield 'event query should return false for pagination filters' => [
			true,  // tribe_is_event_query return value
			false, // expected filter return value
			'Event queries should return false for pagination filters.',
		];

		yield 'non-event query should return original values for pagination filters' => [
			false, // tribe_is_event_query return value
			'original', // expected filter return value (original values)
			'Non-event queries should return original values for pagination filters.',
		];
	}

	/**
	 * @dataProvider filter_behavior_data_provider
	 * @test
	 */
	public function it_should_handle_filter_behavior_correctly( $tribe_is_event_query_return, $expected_behavior, $message ) {
		$this->setup_and_execute_pagination_disabler( $tribe_is_event_query_return );

		// Test the actual filter behavior.
		if ( $expected_behavior === false ) {
			$this->assertFalse( apply_filters( 'wpseo_next_rel_link', 'http://example.com/page/2' ), $message );
			$this->assertFalse( apply_filters( 'wpseo_prev_rel_link', 'http://example.com/page/1' ), $message );
		} else {
			$this->assertEquals( 'http://example.com/page/2', apply_filters( 'wpseo_next_rel_link', 'http://example.com/page/2' ), $message );
			$this->assertEquals( 'http://example.com/page/1', apply_filters( 'wpseo_prev_rel_link', 'http://example.com/page/1' ), $message );
		}
	}
}
