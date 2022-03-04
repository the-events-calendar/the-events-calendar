<?php

use tad\WPBrowser\Module\WPLoader\FactoryStore;

class CT1_Migration_Test_Case extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public static function setUpBeforeClass(  ) {
		// This will load all the factories.
		$factories = new FactoryStore();
		$factories->getThingFactory( 'post' );
	}
}
