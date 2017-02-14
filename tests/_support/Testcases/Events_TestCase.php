<?php

namespace Tribe\Events\Tests\Testcases;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Organizer;
use Tribe\Events\Tests\Factories\Venue;

class Events_TestCase extends WPTestCase {
	/**
	 * @var array An array of bound implementations we could replace during tests.
	 */
	protected $backups = [];

	/**
	 * @var array An associative array of backed up alias and bound implementations.
	 */
	protected $implementation_backups = [];

	function setUp() {
		parent::setUp();

		$this->factory()->event     = new Event();
		$this->factory()->venue     = new Venue();
		$this->factory()->organizer = new Organizer();

		foreach ( $this->backups as $alias ) {
			$this->implementation_backups[ $alias ] = tribe( $alias );
		}
	}

	public function tearDown() {
		foreach ( $this->implementation_backups as $alias => $value ) {
			tribe_singleton( $alias, $value );
		}
		parent::tearDown();
	}
}
