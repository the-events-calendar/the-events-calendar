<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// SEO specific setup
class ViewsSeoTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_seo' );
		$this->resetGlobalState();
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
