<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Partials specific setup
class ViewsPartialsTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_partials' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
