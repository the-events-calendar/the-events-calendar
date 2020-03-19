<?php

namespace Tribe\Events\Views\V2;

use Codeception\TestCase\WPTestCase;

class HooksTest extends WPTestCase {
	protected $context_backup;

	public function setUp() {
		parent::setUp();
		$this->context_backup = tribe_context();
	}

	public function tearDown() {
		tribe_singleton( 'context', $this->context_backup );
		parent::tearDown();
	}


	/**
	 * It should not filter an admin request that uses the "view" query arg.
	 *
	 * @test
	 */
	public function should_not_filter_an_admin_request_that_uses_the_view_query_arg_() {
		// Make sure original URL and redirect URL are different.
		$original_url = admin_url( '/options-general.php?page=some-page&view=some-view' );
		$url          = admin_url( '/options-general.php?page=some-page&view=some-view&foo=bar&_wpnonce=e4db523f03' );
		tribe_singleton( 'context', tribe_context()->alter( [
			'view_request' => 'some-view'
		] ) );

		/** @var Hooks $hooks */
		$hooks          = tribe( 'events.views.v2.hooks' );
		$redirected_url = $hooks->filter_redirect_canonical( $url, $original_url );

		$this->assertEquals( $url, $redirected_url );
	}

	/**
	 * It should not filter the URL if the view request is not for an enabled view
	 *
	 * @test
	 */
	public function should_not_filter_the_url_if_the_view_request_is_not_for_an_enabled_view() {
		// Make sure original URL and redirect URL are different.
		$original_url = home_url( '/lore-dolor' );
		$url          = home_url( '/?view=photo' );
		tribe_update_option( 'tribeEnableViews', [ 'list', 'month' ] );
		tribe_singleton( 'context', tribe_context()->alter( [
			'view_request' => 'photo'
		] ) );

		/** @var Hooks $hooks */
		$hooks          = tribe( 'events.views.v2.hooks' );
		$redirected_url = $hooks->filter_redirect_canonical( $url, $original_url );

		$this->assertEquals( $url, $redirected_url );
	}

	/**
	 * It should block redirection for embedded views
	 *
	 * @test
	 */
	public function should_block_redirection_for_embedded_views() {
		// Make sure original URL and redirect URL are different.
		$original_url = home_url( '/?view=lorem-dolor' );
		$url          = home_url( '/?view=list' );
		tribe_update_option( 'tribeEnableViews', [ 'list', 'month' ] );
		tribe_singleton( 'context', tribe_context()->alter( [
			'view_request' => 'embed'
		] ) );

		/** @var Hooks $hooks */
		$hooks          = tribe( 'events.views.v2.hooks' );
		$redirected_url = $hooks->filter_redirect_canonical( $url, $original_url );

		$this->assertFalse(  $redirected_url );
	}

	/**
	 * It should not filter for single views
	 *
	 * @test
	 */
	public function should_not_filter_for_single_views() {
		// Make sure original URL and redirect URL are different.
		$original_url = home_url( '/events/test-1' );
		$url          = home_url( '/events/test-2' );
		tribe_singleton( 'context', tribe_context()->alter( [
			'view_request' => 'single-event'
		] ) );

		/** @var Hooks $hooks */
		$hooks          = tribe( 'events.views.v2.hooks' );
		$redirected_url = $hooks->filter_redirect_canonical( $url, $original_url );

		$this->assertEquals( $url, $redirected_url );
	}
}
