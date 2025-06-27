<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Integration specific setup
class ViewsIntegrationTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_integration' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
