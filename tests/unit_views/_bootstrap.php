<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Unit tests specific setup
class UnitViewsTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'unit_views' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
