<?php


/**
 * Test that things are deprecated properly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Tribe_Deprecated_Test extends \Codeception\TestCase\WPTestCase {

	public function deprecated_classes_4_0() {
		return array(
			array( 'Tribe__Events__Abstract_Deactivation' ),
			array( 'Tribe__Events__Admin__Helpers' ),
			array( 'Tribe__Events__App_Shop' ),
			array( 'Tribe__Events__Autoloader' ),
			array( 'Tribe__Events__Cache' ),
			array( 'Tribe__Events__Cache_Listener' ),
			array( 'Tribe__Events__Changelog_Reader' ),
			array( 'Tribe__Events__Credits' ),
			array( 'Tribe__Events__Date_Utils' ),
			array( 'Tribe__Events__Field' ),
			array( 'Tribe__Events__Settings' ),
			array( 'Tribe__Events__Settings_Tab' ),
			array( 'Tribe__Events__Support' ),
			array( 'Tribe__Events__Template_Part_Cache' ),
			array( 'Tribe__Events__Validate' ),
			array( 'Tribe__Events__View_Helpers' ),
		);
	}

	public function deprecated_classes_3_10() {
		return array(
			array( 'TribeAppShop' ),
			array( 'TribeDateUtils' ),
			array( 'TribeEvents' ),
			array( 'TribeEventsAPI' ),
			array( 'TribeEventsAdminList' ),
			array( 'TribeEventsBar' ),
			array( 'TribeEventsCache' ),
			array( 'TribeEventsCacheListener' ),
			array( 'TribeEventsImporter_AdminPage' ),
			array( 'TribeEventsImporter_ColumnMapper' ),
			array( 'TribeEventsImporter_FileImporter' ),
			array( 'TribeEventsImporter_FileImporter_Events' ),
			array( 'TribeEventsImporter_FileImporter_Organizers' ),
			array( 'TribeEventsImporter_FileImporter_Venues' ),
			array( 'TribeEventsImporter_FileReader' ),
			array( 'TribeEventsImporter_FileUploader' ),
			array( 'TribeEventsImporter_Plugin' ),
			array( 'TribeEventsListWidget' ),
			array( 'TribeEventsOptionsException' ),
			array( 'TribeEventsPostException' ),
			array( 'TribeEventsQuery' ),
			array( 'TribeEventsSupport' ),
			array( 'TribeEventsTemplates' ),
			array( 'TribeEventsUpdate' ),
			array( 'TribeEventsViewHelpers' ),
			array( 'TribeEvents_EmbeddedMaps' ),
			array( 'TribeField' ),
			array( 'TribePluginUpdateEngineChecker' ),
			array( 'TribePluginUpdateUtility' ),
			array( 'TribeRecurringEventCleanup' ),
			array( 'TribeSettings' ),
			array( 'TribeSettingsTab' ),
			array( 'TribeValidate' ),
			array( 'Tribe_Amalgamator' ),
			array( 'Tribe_Events_Day_Template' ),
			array( 'Tribe_Events_List_Template' ),
			array( 'Tribe_Events_Month_Template' ),
			array( 'Tribe_Events_Single_Event_Template' ),
			array( 'Tribe_Meta_Factory' ),
			array( 'Tribe_PU_PluginInfo' ),
			array( 'Tribe_Register_Meta' ),
			array( 'Tribe_Template_Factory' ),
			array( 'TribeiCal' ),
		);
	}

	public function deprecated_classes_4_2() {
		return array(
			array( 'Tribe__Events__PUE__Checker' ),
			array( 'Tribe__Events__PUE__Utility' ),
			array( 'Tribe__Events__PUE__Plugin_Info' ),
		);
	}

	public function deprecated_classes_4_3() {
		return array(
			array( 'Tribe__Events__Meta_Factory' ),
			array( 'Tribe__Events__Advanced_Functions__Register_Meta' ),
		);
	}


	public function test_main_set_option() {
		$this->expected_deprecated[] = 'Tribe__Events__Main::setOption';
		Tribe__Events__Main::instance()->setOption( 'schema-version', 0 );
	}

	/**
	 * Test if a class exists was deprecated in 4.3 exists but is deprecated.
	 *
	 * @dataProvider deprecated_classes_4_3
	 */
	public function test_deprecated_class_4_3( $class ) {
		if ( class_exists( $class, false ) ) {
			$this->markTestSkipped( $class . 'was already loaded' );
		}

		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/src/deprecated/' . $class . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists was deprecated in 4.2 exists but is deprecated.
	 *
	 * @dataProvider deprecated_classes_4_2
	 */
	public function test_deprecated_class_4_2( $class ) {
		if ( class_exists( $class, false ) ) {
			$this->markTestSkipped( $class . 'was already loaded' );
		}

		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/src/deprecated/' . $class . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists was deprecated in 4.0 exists but is deprecated.
	 *
	 * @dataProvider deprecated_classes_4_0
	 */
	public function test_deprecated_class_4_0( $class ) {
		if ( class_exists( $class, false ) ) {
			$this->markTestSkipped( $class . 'was already loaded' );
		}

		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/common/src/deprecated/' . $class
		                                    . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists was deprecated in 3.10 exists but is deprecated.
	 *
	 *     * @dataProvider deprecated_classes_3_10
	 */
	public function test_deprecated_classes_3_10( $class ) {
		if ( class_exists( $class, false ) ) {
			$this->markTestSkipped( $class . 'was already loaded' );
		}

		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/src/deprecated/' . $class . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}
}
