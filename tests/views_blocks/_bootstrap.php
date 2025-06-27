<?php

require_once __DIR__ . '/../_bootstrap_base.php';

// Blocks specific setup
class ViewsBlocksTestSuite extends ViewsTestSuite {

	public function setUp() {
		parent::setUp();
		$this->setupDatabase( 'views_blocks' );
		$this->resetGlobalState();

		// Enable block editor
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
	}

	public function tearDown() {
		$this->cleanupDatabase();
		parent::tearDown();
	}
}
