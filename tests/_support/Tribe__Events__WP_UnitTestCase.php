<?php

class Tribe__Events__WP_UnitTestCase extends \Codeception\TestCase\WPTestCase {

	// avoid errors with singletons and closures
	protected $backupGlobals = false;

	// array of deprecated files we expect to encounter
	protected $expected_deprecated_file = [];

	// array of deprecated files we caught encounter
	protected $caught_deprecated_file = [];

	/**
	 * @var holds example data for the post
	 */
	protected $post_example_settings;

	/**
	 * Gets the path to the _data folder without trailing slash.
	 *
	 * @return string
	 */
	public function get_data_folder_path() {
		return dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . '_data';
	}

	public function setUp() {
		// set a permalink structure as soon as possible
		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );

		parent::setUp();

		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);
	}

	public function expectDeprecated() {
		parent::expectDeprecated();

		add_action( 'deprecated_file_included', array( $this, 'deprecated_file_run' ) );
		add_action( 'deprecated_file_trigger_error', '__return_false' );
	}

	public function deprecated_file_run( $file ) {
		if ( in_array( $file, $this->caught_deprecated_file ) ) {
			return;
		}

		$this->caught_deprecated_file[] = $file;
	}

	public function expectedDeprecated() {
		$not_caught_deprecated_file = array_diff( $this->expected_deprecated_file, $this->caught_deprecated_file );
		foreach ( $not_caught_deprecated_file as $not_caught ) {
			$this->fail( "Failed to assert that $not_caught triggered a deprecated file notice" );
		}

		$unexpected_deprecated_file = array_diff( $this->caught_deprecated_file, $this->expected_deprecated_file );
		foreach ( $unexpected_deprecated_file as $unexpected ) {
			$this->fail( "Unexpected deprecated file: $unexpected" );
		}

		parent::expectedDeprecated();
	}

	/**
	 * For compatibility until WP_Browser updates testcase.php
	 * See: https://github.com/lucatume/wp-browser/pull/23
	 */
	public function set_permalink_structure( $structure = '' ) {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );
		$wp_rewrite->flush_rules();
	}
}
