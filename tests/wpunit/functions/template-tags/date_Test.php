<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;

class Date_Test extends WPTestCase {
	private array $tribe_options = [];

	/**
	 * @before
	 * @after
	 */
	public function reset_options_cache(): void {
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );
	}

	public function test_tec_events_get_time_range_separator_returns_default_when_option_not_set(): void {
		$output = tec_events_get_time_range_separator();

		$this->assertEquals( ' - ', $output );
	}

	public function tec_events_get_time_range_separator_data_provider(): array {
		return [
			'empty separator'   => [ '', '' ],
			'null separator'    => [ null, '' ],
			'default separator' => [ ' - ', ' - ' ],
			'custom separator'  => [ '|', '|' ],
			'numeric separator' => [ '1', '1' ],
			'script'            => [ '<script>alert("Hello")</script>', 'alert("Hello")' ],
			'allowed html tag'  => [ ' <strong> - </strong> ', ' <strong> - </strong> ' ],
			'script in img tag' => [
				'<img onload="alert(\'Hello\')" src="not-really" onerror="alert(\'Error\')">',
				'<img src="not-really">'
			],
		];
	}

	/**
	 * @dataProvider tec_events_get_time_range_separator_data_provider
	 */
	public function test_tec_events_get_time_range_separator( $separator, string $expected ): void {
		tribe_update_option( 'timeRangeSeparator', $separator );
		$output = tec_events_get_time_range_separator();

		$this->assertEquals( $expected, $output );
	}

	public function test_tec_events_get_time_range_separator_protects_from_cache_poisoning(): void {
		$cache                                        = tribe_cache();
		$cache['tec_events_get_time_range_separator'] = (object) [ 'value' => ' foo ' ];

		$output = tec_events_get_time_range_separator();

		$this->assertEquals( ' - ', $output );

		$cache['tec_events_get_time_range_separator'] = new class {
			public function __toString() {
				return ' foo ';
			}
		};

		$output = tec_events_get_time_range_separator();

		$this->assertEquals( ' - ', $output );
	}

	public function test_tec_events_get_date_time_separator_returns_default_when_option_not_set(): void {
		$output = tec_events_get_date_time_separator();

		$this->assertEquals( ' @ ', $output );
	}

	public function tec_events_get_date_time_separator_data_provider(): array {
		return [
			'empty separator'   => [ '', '' ],
			'null separator'    => [ null, '' ],
			'default separator' => [ ' @ ', ' @ ' ],
			'custom separator'  => [ '|', '|' ],
			'numeric separator' => [ '1', '1' ],
			'script'            => [ '<script>alert("Hello")</script>', 'alert("Hello")' ],
			'allowed html tag'  => [ ' <strong> @ </strong> ', ' <strong> @ </strong> ' ],
			'script in img tag' => [
				'<img onload="alert(\'Hello\')" src="not-really" onerror="alert(\'Error\')">',
				'<img src="not-really">'
			],
		];
	}

	/**
	 * @dataProvider tec_events_get_date_time_separator_data_provider
	 */
	public function test_tec_events_get_date_time_separator( $separator, string $expected ): void {
		tribe_update_option( 'dateTimeSeparator', $separator );
		$output = tec_events_get_date_time_separator();

		$this->assertEquals( $expected, $output );
	}

	public function test_tec_events_get_date_time_separator_protects_from_cache_poisoning(): void {
		$cache                                       = tribe_cache();
		$cache['tec_events_get_date_time_separator'] = (object) [ 'value' => ' foo ' ];

		$output = tec_events_get_date_time_separator();

		$this->assertEquals( ' @ ', $output );

		$cache['tec_events_get_date_time_separator'] = new class {
			public function __toString() {
				return ' foo ';
			}
		};

		$output = tec_events_get_date_time_separator();
	}
}
