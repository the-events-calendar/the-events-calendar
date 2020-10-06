<?php

use Spatie\Snapshots\MatchesSnapshots;

class BaseSnapshotTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * It should correctly test a string snapshot
	 *
	 * @test
	 */
	public function should_correctly_test_a_string_snapshot() {
		uopz_set_return( 'time', '1577750400' );
		$string = 'test the date function can be mocked: ' . date( 'Y-m-d H:i:s', time() );

		$this->assertMatchesSnapshot( $string );
	}
}
