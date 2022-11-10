<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\WPBrowser;

class Rewrite_functional extends \Codeception\Module {

	/**
	 * Asserts the current response content equals the expected value.
	 *
	 * @param mixed $expected The expected value.
	 *
	 * @return void
	 * @throws \Codeception\Exception\ModuleException On failure.
	 */
	public function seeResponseIs( $expected ): void {
		/** @var WPBrowser $module */
		$module = $this->getModule( 'WPBrowser' );
		$this->assertEquals( $expected, $module->_getResponseContent() );
	}
}
