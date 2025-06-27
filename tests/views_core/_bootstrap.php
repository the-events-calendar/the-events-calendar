<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Core Views specific setup
class ViewsCoreTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_core' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
