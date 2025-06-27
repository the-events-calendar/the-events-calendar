<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Data layer specific setup
class ViewsDataTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_data' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
