<?php

/**
 * Simple test to make sure PHPUnit is functioning properly, even without the wordpress setup.
 *
 * @package TribeEvents
 * @since 2.0.5
 * @author Paul Hughes
 */
class PhpUnitTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Tests if true is true, just to make sure PHPUnit is working.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */
    public function testPhpUnit()
    {
        $this->assertTrue( true );
    }
}