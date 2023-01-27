<?php

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\Traits\With_Context;
use Tribe__Context as Context;

class HTML_CacheTest extends \Codeception\TestCase\WPTestCase {
	use With_Context;

	/**
	 * @var HTML_Cache
	 */
	protected $implementation;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::factory()->event = new Event();
	}

	public function setUp() {
		parent::setUp();
		$this->implementation = new class extends View {
			protected static $view_slug = 'month';

			use HTML_Cache;
		};

		// Kill straggler events that might be created due to SQL execution delay.
		while ( tribe_events()->found() ) {
			tribe_events()->delete();
		}
		tribe_singleton( 'context', new \Tribe__Context() );
		$this->backup_context();
	}

	public function tearDown(  ) {
		$this->restore_context();
	}

	/**
	 * It should cache HTML if user is not logged in and there are no pwd-protected events
	 *
	 * @test
	 */
	public function should_cache_html_if_user_is_not_logged_in_and_there_are_no_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		wp_set_current_user( 0 );

		$should_cache_html = $this->implementation->should_cache_html();
		$cache_key_salts   = $this->implementation->get_cache_html_key_salts();

		$this->assertTrue( $should_cache_html );
		$this->assertEqualSets( $cache_key_salts, [ 'current_user_can_read_private_posts' => false, 'locale' => 'en_US' ] );
	}

	/**
	 * It should not cache HTML if user is not logged in and there are pwd-protected events
	 *
	 * @test
	 */
	public function should_not_cache_html_if_user_is_not_logged_in_and_there_are_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		static::factory()->event->create( [ 'post_status' => 'publish', 'post_password' => 'secret' ] );
		wp_set_current_user( 0 );

		$should_cache_html = $this->implementation->should_cache_html();

		$this->assertFalse( $should_cache_html );
	}

	/**
	 * It should cache if user is logged in, cannot read private posts, and there are no pwd-protected events
	 *
	 * @test
	 */
	public function should_cache_if_user_is_logged_in_cannot_read_private_posts_and_there_are_no_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		$subscriber = static::factory()->user->create(['role' => 'subscriber']);
		wp_set_current_user( $subscriber );

		$should_cache_html = $this->implementation->should_cache_html();
		$cache_key_salts   = $this->implementation->get_cache_html_key_salts();

		$this->assertTrue( $should_cache_html );
		$this->assertEqualSets( $cache_key_salts, [ 'current_user_can_read_private_posts' => false, 'locale' => 'en_US' ] );
	}

	/**
	 * It should not cache if user is logged in, cannot read private posts, and there are pwd-protected events
	 *
	 * @test
	 */
	public function should_not_cache_if_user_is_logged_in_cannot_read_private_posts_and_there_are_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		static::factory()->event->create( [ 'post_status' => 'publish', 'post_password' => 'secret' ] );
		$subscriber = static::factory()->user->create(['role' => 'subscriber']);
		wp_set_current_user( $subscriber );

		$should_cache_html = $this->implementation->should_cache_html();

		$this->assertFalse( $should_cache_html );
	}

	/**
	 * It should cache if user is logged in, can read private posts, and there are no pwd-protected-events
	 *
	 * @test
	 */
	public function should_cache_if_user_is_logged_in_can_read_private_posts_and_there_are_no_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		$administrator = static::factory()->user->create(['role' => 'administrator']);
		wp_set_current_user( $administrator );

		$should_cache_html = $this->implementation->should_cache_html();
		$cache_key_salts   = $this->implementation->get_cache_html_key_salts();

		$this->assertTrue( $should_cache_html );
		$this->assertEqualSets( $cache_key_salts, [ 'current_user_can_read_private_posts' => true, 'locale' => 'en_US' ] );
	}

	/**
	 * It should not cache if user is logged in, can read private posts, and there are pwd-protected events
	 *
	 * @test
	 */
	public function should_not_cache_if_user_is_logged_in_can_read_private_posts_and_there_are_pwd_protected_events() {
		static::factory()->event->create( [ 'post_status' => 'publish' ] );
		static::factory()->event->create( [ 'post_status' => 'private' ] );
		static::factory()->event->create( [ 'post_status' => 'publish', 'post_password' => 'secret' ] );
		$administrator = static::factory()->user->create(['role' => 'administrator']);
		wp_set_current_user( $administrator );

		$should_cache_html = $this->implementation->should_cache_html();

		$this->assertFalse( $should_cache_html );
	}

	/**
	 * It should not regenerate HTML cache key more than once per Context instance
	 *
	 * @test
	 */
	public function should_not_regenerate_html_cache_key_more_than_once_per_context_instance() {
		$context_1      = tribe( 'context' )->alter( [ 'test' => 'one' ] );
		$context_2      = tribe( 'context' )->alter( [ 'test' => 'two' ] );
		$context_1_args = $context_1->to_array();
		$context_2_args = $context_1->to_array();

		// Sanity  check.
		$this->assertNotSame( $context_1, $context_2 );

		$this->implementation->set_context( $context_1 );
		$context_1_key = $this->implementation->get_cache_html_key();
		$this->implementation->set_context( $context_2 );
		$context_2_key = $this->implementation->get_cache_html_key();

		$this->assertNotSame( $context_1_key, $context_2_key );

		// Filter the Context locations after a first fetch: the `to_array` dump will contain the new location.
		add_filter( 'tribe_context_locations', static function ( array $locations ) {
			$locations['test_2'] = [
				'read' => [
					Context::FUNC => static function () {
						return 23;
					}
				]
			];

			return $locations;
		} );
		$context_1->get( 'test_2', 'bar' );
		$context_2->get( 'test_2', 'baz' );

		$this->assertNotSame( $context_1_args, $context_1->to_array() );
		$this->assertNotSame( $context_2_args, $context_2->to_array() );

		$this->implementation->set_context( $context_1 );
		$context_1_key_after = $this->implementation->get_cache_html_key();
		$this->implementation->set_context( $context_2 );
		$context_2_key_after = $this->implementation->get_cache_html_key();

		$this->assertSame( $context_1_key, $context_1_key_after );
		$this->assertSame( $context_2_key, $context_2_key_after );
		$this->assertNotSame( $context_1_key_after, $context_2_key_after );
	}
}
