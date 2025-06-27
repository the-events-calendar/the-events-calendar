<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Modules specific setup
class ViewsModulesTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_modules' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
