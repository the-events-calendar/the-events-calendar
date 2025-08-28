<?php

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Class Link_HideOnAndroidTest
 *
 * @since TBD
 */
class Link_HideOnAndroidTest extends WPTestCase {
	use With_Uopz;

	/**
	 * Create a concrete implementation of Link_Abstract for testing.
	 *
	 * @since TBD
	 */
	protected function make_instance() {
		return new class extends Link_Abstract {
			public static $slug = 'test';

			protected function label(): string {
				return 'Test Label';
			}

			protected function single_label(): string {
				return 'Test Single Label';
			}

			public function register() {
				// Empty implementation for testing
			}
		};
	}

	/**
	 * Test that maybe_hide_subscribe_link returns true on single event pages regardless of user agent.
	 *
	 * @since TBD
	 * @dataProvider single_event_page_provider
	 */
	public function test_maybe_hide_subscribe_link_returns_true_on_single_event_page(
		string $user_agent,
		bool $visible,
		bool $expected,
		string $expected_message
	) {
		$link = $this->make_instance();

		// Mock is_single() to return true
		$this->set_fn_return( 'is_single', true );

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $link->maybe_hide_subscribe_link( $visible );

		$this->assertEquals( $visible, $result, $expected_message );
	}

	/**
	 * Data provider for single event page scenarios.
	 *
	 * @since TBD
	 * @return \Generator
	 */
	public function single_event_page_provider(): \Generator {
		yield 'android_user_agent_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true on single event pages with Android user agent when visible=true'
		];

		yield 'android_user_agent_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return true on single event pages with Android user agent when visible=false'
		];

		yield 'chrome_user_agent_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true on single event pages with Chrome user agent when visible=true'
		];

		yield 'chrome_user_agent_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return true on single event pages with Chrome user agent when visible=false'
		];

		yield 'empty_user_agent_visible_true' => [
			'user_agent' => '',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true on single event pages with empty user agent when visible=true'
		];
	}

	/**
	 * Test that maybe_hide_subscribe_link returns false when user agent contains Android and not on single page.
	 *
	 * @since TBD
	 * @dataProvider android_non_single_page_provider
	 */
	public function test_maybe_hide_subscribe_link_returns_false_for_android_user_agent(
		string $user_agent,
		bool $visible,
		bool $expected,
		string $expected_message
	) {
		$link = $this->make_instance();

		// Mock is_single() to return false
		$this->set_fn_return( 'is_single', false );

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $link->maybe_hide_subscribe_link( $visible );

		$this->assertEquals( $expected, $result, $expected_message );
	}

	/**
	 * Data provider for Android user agent scenarios on non-single pages.
	 *
	 * @since TBD
	 * @return \Generator
	 */
	public function android_non_single_page_provider(): \Generator {
		yield 'android_lowercase_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Linux; android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false for lowercase Android user agent when visible=true'
		];

		yield 'android_lowercase_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Linux; android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for lowercase Android user agent when visible=false'
		];

		yield 'android_uppercase_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Linux; ANDROID 10; SM-G975F) AppleWebKit/537.36',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false for uppercase Android user agent when visible=true'
		];

		yield 'android_mixed_case_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Linux; AnDrOiD 10; SM-G975F) AppleWebKit/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for mixed case Android user agent when visible=false'
		];

		yield 'android_in_middle' => [
			'user_agent' => 'Something Android Something',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false when Android is in middle of user agent string'
		];
	}

	/**
	 * Test that maybe_hide_subscribe_link returns true for non-Android user agents on non-single pages.
	 *
	 * @since TBD
	 * @dataProvider non_android_non_single_page_provider
	 */
	public function test_maybe_hide_subscribe_link_returns_true_for_non_android_user_agents(
		string $user_agent,
		bool $visible,
		bool $expected,
		string $expected_message
	) {
		$link = $this->make_instance();

		// Mock is_single() to return false
		$this->set_fn_return( 'is_single', false );

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $link->maybe_hide_subscribe_link( $visible );

		$this->assertEquals( $expected, $result, $expected_message );
	}

	/**
	 * Data provider for non-Android user agent scenarios on non-single pages.
	 *
	 * @since TBD
	 * @return \Generator
	 */
	public function non_android_non_single_page_provider(): \Generator {
		yield 'chrome_windows_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true for Chrome Windows user agent when visible=true'
		];

		yield 'chrome_windows_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for Chrome Windows user agent when visible=false'
		];

		yield 'firefox_windows_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true for Firefox Windows user agent when visible=true'
		];

		yield 'safari_macos_visible_false' => [
			'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for Safari macOS user agent when visible=false'
		];

		yield 'edge_windows_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true for Edge Windows user agent when visible=true'
		];

		yield 'ios_safari_visible_true' => [
			'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true for iOS Safari user agent when visible=true'
		];

		yield 'empty_string_visible_false' => [
			'user_agent' => '',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for empty user agent string when visible=false'
		];

		yield 'generic_browser_visible_true' => [
			'user_agent' => 'GenericBrowser/1.0',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true for generic browser user agent when visible=true'
		];
	}

	/**
	 * Test edge cases and special scenarios for maybe_hide_subscribe_link.
	 *
	 * @since TBD
	 * @dataProvider edge_cases_provider
	 */
	public function test_maybe_hide_subscribe_link_edge_cases(
		bool $is_single,
		string $user_agent,
		bool $visible,
		bool $expected,
		string $expected_message,
		bool $unset_user_agent = false
	) {
		$link = $this->make_instance();

		// Mock is_single() based on test data
		$this->set_fn_return( 'is_single', $is_single );

		// Handle missing user agent scenario
		if ( $unset_user_agent ) {
			unset( $_SERVER['HTTP_USER_AGENT'] );
		} else {
			$_SERVER['HTTP_USER_AGENT'] = $user_agent;
		}

		$result = $link->maybe_hide_subscribe_link( $visible );

		$this->assertEquals( $expected, $result, $expected_message );
	}

	/**
	 * Data provider for edge cases and special scenarios.
	 *
	 * @since TBD
	 * @return \Generator
	 */
	public function edge_cases_provider(): \Generator {
		// Missing user agent scenarios
		yield 'missing_user_agent_non_single_visible_true' => [
			'is_single' => false,
			'user_agent' => '', // will be unset
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true when HTTP_USER_AGENT is not set on non-single page with visible=true',
			'unset_user_agent' => true
		];

		yield 'missing_user_agent_non_single_visible_false' => [
			'is_single' => false,
			'user_agent' => '', // will be unset
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false when HTTP_USER_AGENT is not set on non-single page with visible=false',
			'unset_user_agent' => true
		];

		// Test that visible parameter doesn't affect Android detection
		yield 'android_non_single_visible_true' => [
			'is_single' => false,
			'user_agent' => 'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false for Android regardless of visible=true parameter'
		];

		yield 'android_non_single_visible_false' => [
			'is_single' => false,
			'user_agent' => 'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false for Android regardless of visible=false parameter'
		];

		// Test that visible parameter doesn't affect non-Android on single pages
		yield 'non_android_single_visible_true' => [
			'is_single' => true,
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			'visible' => true,
			'expected' => true,
			'expected_message' => 'Should return true on single page with non-Android regardless of visible=true parameter'
		];

		yield 'non_android_single_visible_false' => [
			'is_single' => true,
			'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			'visible' => false,
			'expected' => false,
			'expected_message' => 'Should return false on single page with non-Android regardless of visible=false parameter'
		];

		// Additional Android position tests
		yield 'android_at_start' => [
			'is_single' => false,
			'user_agent' => 'Android Mozilla/5.0',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false when Android is at start of user agent string'
		];

		yield 'android_at_end' => [
			'is_single' => false,
			'user_agent' => 'Mozilla/5.0 Android',
			'visible' => true,
			'expected' => false,
			'expected_message' => 'Should return false when Android is at end of user agent string'
		];
	}

	/**
	 * Restore $_SERVER state after each test.
	 *
	 * @since TBD
	 */
	public function tearDown(): void {
		// Restore original $_SERVER state
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$_SERVER['HTTP_USER_AGENT'] = '';
		}

		parent::tearDown();
	}
}
