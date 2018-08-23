<?php
namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Main as Events;
use Tribe__Main as Common;

/**
 * Test that Common is being loaded correctly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class CommonTest extends Events_TestCase {
	/**
	 * Common should be loaded
	 *
	 * @test
	 * @since 4.6.22
	 */
	public function it_is_loading_common() {

		$this->assertFalse(
			defined( Common::VERSION ),
			'Tribe Common is not loading, you probably need to check that'
		);
	}

	/**
	 * Common must be, at least in MIN_COMMON_VERSION
	 *
	 * @test
	 * @since 4.6.22
	*/
	public function it_is_loading_common_required_version() {

		$this->assertTrue(
			version_compare( Common::VERSION, Events::MIN_COMMON_VERSION, '>=' ),
			'Tribe Common version should be at least ' . Events::MIN_COMMON_VERSION . ', currently on ' . Common::VERSION
		);
	}

	}
