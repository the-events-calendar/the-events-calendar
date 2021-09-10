<?php

namespace TEC\Custom_Tables\V1;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Container as Container;

class Provider_Test extends WPTestCase {
	use With_Uopz;

	protected $clean_with = [];

	/**
	 * @after
	 */
	public function clean() {
		foreach ( $this->clean_with as $cleaner ) {
			$cleaner();
		}
	}

	/**
	 * It should not register if disable constant defined and truthy
	 *
	 * @test
	 */
	public function should_not_register_if_disable_constant_defined_and_truthy() {
		$this->set_const_value( Provider::DISABLED, true );

		$this->assertFalse( Provider::is_active() );
	}

	/**
	 * It should not register if disable env var set and truthy
	 *
	 * @test
	 */
	public function should_not_register_if_disable_env_var_set_and_truthy() {
		$this->clean_with[] = static function () {
			putenv( Provider::DISABLED . '=0' );
		};
		putenv( Provider::DISABLED . '=1' );

		$this->assertFalse( Provider::is_active() );
	}

	/**
	 * It should not register if enable filter is falsy
	 *
	 * @test
	 */
	public function should_not_register_if_enable_filter_is_falsy() {
		add_filter( 'tec_custom_tables_v1_enabled', '__return_false' );

		$provider = new Provider( new Container );

		$this->assertFalse( Provider::is_active() );
	}

	/**
	 * It should register if disable const set and falsy
	 *
	 * @test
	 */
	public function should_register_if_disable_const_set_and_falsy() {
		$this->set_const_value( Provider::DISABLED, false );

		$this->assertTrue( Provider::is_active() );
	}

	/**
	 * It should register if disable env var set and falsy
	 *
	 * @test
	 */
	public function should_register_if_disable_env_var_set_and_falsy() {
		putenv( Provider::DISABLED . '=0' );

		$this->assertTrue( Provider::is_active() );
	}

	/**
	 * It should register by default
	 *
	 * @test
	 */
	public function should_register_by_default() {
		$provider = new Provider( new Container );

		$this->assertTrue( Provider::is_active() );
	}
}
