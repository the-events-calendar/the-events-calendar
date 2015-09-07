<?php


class Tribe__Events__WP_UnitTestCase extends WP_UnitTestCase {

	// avoid errors with singletons and closures
	protected $backupGlobals = false;

	/**
	 * Gets the path to the _data folder without trailing slash.
	 *
	 * @return string
	 */
	public function get_data_folder_path() {
		return dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . '_data';
	}

	public function setUp() {
		parent::setUp();
		tribe_load_active_plugins();
	}

}