<?php

namespace TEC\Events\Custom_Tables\V1;

use Codeception\TestCase\WPTestCase;

class ProviderTest extends WPTestCase {

	private function silence_logs(): void {
		global $wp_filter;
		$wp_filter['tribe_log'] = new \WP_Hook();
	}

	/**
	 * It should log an error and move on if activation error happens
	 *
	 * @test
	 */
	public function should_log_an_error_and_move_on_if_activation_error_happens(): void {
		$this->silence_logs();
		$throwing_container = new class extends \Tribe__Container {
			public function singleton( $id, $implementation = null, array $afterBuildMethods = null ) {
				if ( $id !== 'tec.custom-tables.v1.provider' ) {
					return parent::singleton( $id, $implementation, $afterBuildMethods );
				}
				throw new \RuntimeException( 'Something went wrong' );
			}

		};
		$provider = new Provider( $throwing_container );

		$logged = [];
		add_action( 'tribe_log', function () use ( &$logged ) {
			$logged[] = func_get_args();
		}, 10, 10 );

		$provider->register();

		// Remove duplicates.
		$logged = array_unique( $logged, SORT_REGULAR );

		$this->assertCount( 1, $logged );
		$this->assertArrayHasKey( 0, $logged );
		$this->assertEquals( 'error', $logged[0][0] );
		$this->assertEquals( 'Caught Custom Tables V1 activation error.', $logged[0][1] );
		$this->assertEquals( 'Something went wrong', $logged[0][2]['message'] );
		$this->assertEquals( __FILE__, $logged[0][2]['file'] );
		$this->isType( 'int', $logged[0][2]['line'] );
	}
}