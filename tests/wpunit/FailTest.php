<?php

class FailTest extends \Codeception\TestCase\WPTestCase {
	public function test_failure() {
		$this->assertFalse( true );
	}
}
