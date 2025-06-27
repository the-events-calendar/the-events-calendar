<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Components specific setup
class ViewsComponentsTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_components' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
