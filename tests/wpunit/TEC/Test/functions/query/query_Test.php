<?php

namespace TEC\Test\functions\query;

use Codeception\TestCase\WPTestCase;

class query_Test extends WPTestCase {

	public static function test_tec_query_batch_size_data_provider(): array {
		return [
			'no filtering'           => [
				fn( $input ) => $input,
				null,
			],
			'not integer'            => [
				fn() => 'foo-bar',
				null,
			],
			'negative integer'       => [
				fn() => - 10,
				null,
			],
			'zero'                   => [
				fn() => 0,
				null,
			],
			'positive integer'       => [
				fn() => 10,
				10,
			],
			'postive integer string' => [
				fn() => '10',
				10,
			],
		];
	}

	/**
	 * @dataProvider test_tec_query_batch_size_data_provider
	 */
	public function test_tec_query_batch_size( $filter, ?int $expected ): void {
		if ( null === $expected ) {
			// The default value.
			$expected = tec_query_batch_size();
		}

		add_filter( 'tec_events_query_batch_size', $filter );

		$this->assertEquals( $expected, tec_query_batch_size() );
	}
}
