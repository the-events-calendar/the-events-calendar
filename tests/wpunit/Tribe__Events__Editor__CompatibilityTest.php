<?php

use Tribe\Events\Test\Traits\With_Uopz;

use Tribe__Events__Editor__Compatibility as Compatibility;

class Tribe__Events__Editor__CompatibilityTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function cache_values_data_provider(): Generator {
		yield 'default cache value empty string, false option' => [
			'',
			false,
			false,
			0
		];
		yield 'default cache value empty string, true option' => [
			'',
			true,
			true,
			1
		];
		yield 'default cache value null, false option' => [
			null,
			false,
			false,
			0
		];
		yield 'default cache value null, true option' => [
			null,
			true,
			true,
			1
		];
	}

	/**
	 * @dataProvider cache_values_data_provider
	 */
	public function test_cold_cache_is_blocks_editor_toggled_on( $default_cache_value, $option_value, $expected, $expected_cached ): void {
		$mock_cache = new class( $default_cache_value ) {
			private $default_cache_value;
			private $cache = [];

			public function __construct( $default_cache_value ) {
				$this->default_cache_value = $default_cache_value;
			}

			public function get( $key, $default = false ) {
				return $this->cache[ $key ] ?? $this->default_cache_value;
			}

			public function set( $key, $value, $expiration = 0 ) {
				$this->cache[ $key ] = $value;
			}
		};
		$this->uopz_set_return( 'tribe_cache', $mock_cache );
		tribe_update_option( 'toggle_blocks_editor', $option_value );
		$cache_key = 'tec_editor_compatibility_' . Compatibility::$blocks_editor_key;

		$compatibility               = new Compatibility();
		$is_blocks_editor_toggled_on = $compatibility->is_blocks_editor_toggled_on();

		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );

		// Run the checks a second time to ensure stability.
		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );

		// Run the checks a third time to ensure stability.
		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );
	}

	/**
	 * @dataProvider cache_values_data_provider
	 */
	public function test_warm_cache_is_blocks_editor_toggled_on( $cached_value, $option_value, $expected, $expected_cached ): void {
		$mock_cache = new class( $cached_value ) {
			private $cache = [];

			public function __construct( $cached_value ) {
				$this->cache['tec_editor_compatibility_' . Compatibility::$blocks_editor_key] = $cached_value;
			}

			public function get( $key, $default = false ) {
				return $this->cache[ $key ] ?? $default;
			}

			public function set( $key, $value, $expiration = 0 ) {
				$this->cache[ $key ] = $value;
			}
		};
		$this->uopz_set_return( 'tribe_cache', $mock_cache );
		tribe_update_option( 'toggle_blocks_editor', $option_value );
		$cache_key = 'tec_editor_compatibility_' . Compatibility::$blocks_editor_key;

		$compatibility               = new Compatibility();
		$is_blocks_editor_toggled_on = $compatibility->is_blocks_editor_toggled_on();

		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );

		// Run the checks a second time to ensure stability.
		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );

		// Run the checks a third time to ensure stability.
		$this->assertEquals( $expected, $is_blocks_editor_toggled_on );
		$this->assertEquals( $expected_cached, $mock_cache->get( $cache_key ) );
	}
}
