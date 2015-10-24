<?php
/**
 * Inherited Methods
 * @method void wantToTest( $text )
 * @method void wantTo( $text )
 * @method void execute( $callable )
 * @method void expectTo( $prediction )
 * @method void expect( $prediction )
 * @method void amGoingTo( $argumentation )
 * @method void am( $role )
 * @method void lookForwardTo( $achieveValue )
 * @method void comment( $description )
 * @method \Codeception\Lib\Friend haveFriend( $name, $actorClass = null )
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor {

	use \_generated\AcceptanceTesterActions;

	/**
	 * Default Event meta
	 */
	private static $defaultEvent = array(
		'title'   => 'My Event',
		'content' => 'Automated Event',
		'allDay'  => false,
	);

	/**
	 * Create new Event
	 */
	public function createEvent( $event = null ) {
		if ( is_null( $event ) ) {
			$event = $this->generateEvent();
		} else {
			$event = array_merge( self::$defaultEvent, $event );
		}

		$I = $this;
		$I->amOnPage( '/wp-admin/post-new.php?post_type=tribe_events' );
		$I->fillField( 'post_title', $event['title'] );
		//$I->fillField('content', $event['content'] ); // need to target WYSIWYG instance
		if ( $event['allDay'] ) {
			$I->checkOption( '#allDayCheckbox' );
		}
		$I->click( '#publish' );
		$I->see( 'Event published' );

		// TODO Full Validation of Event Properties based of passed flag
	}

	/**
	 * Delete Event
	 */
	public function deleteEvent() {
		$I = $this;
	}

	/**
	 * Generate random event meta
	 */
	public function generateEvent() {
		$random            = array();
		$random['title']   = 'Event ' + time();
		$random['content'] = 'Description ' + time();

		return array_merge( self::$defaultEvent, $random );
	}

	public function activate_tec() {
		$I = $this;

		$I->am( 'administrator' );
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$link_text = $I->grabTextFrom( '#the-events-calendar [aria-label*="ctivate"]' );

		if ( 'Activate' == $link_text ) {
			$I->activatePlugin( 'the-events-calendar' );
		}

		$I->seePluginActivated( 'the-events-calendar' );
	}

	public function set_pretty_permalinks() {
		$I = $this;

		//Set Permalinks to pretty
		$I->amOnPage( '/wp-admin/options-permalink.php' );
		$I->see( 'Permalink Settings' );
		$I->selectOption( 'form input[name=selection]', '/%postname%/' );
		$I->click( 'Save Changes' );
	}

	public function upload_csv( $type, $file ) {
		$I = $this;

		// Upload CSV
		$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=events-importer&tab=csv-importer' );

		// Might be an insufficient permissions page or some such
		$I->see( 'Events Import', 'h1' );
		$I->see( 'CSV', 'a.nav-tab-active' );

		$I->selectOption( 'import_type', $type );
		$I->attachFile( 'import_file', $file );
		//$I->checkOption('import_header');
		// @todo Wish there was something more specific to click on, patch core give element a name
		$I->click( '.tribe_settings input[type=submit]' );

		// make sure we didn't get an error uploading the file
		$I->cantSee( 'Could not save' );

		// Import CSV
		$I->see( 'Column Mapping', 'h2' );

		// For each column mapping element select its corresponding header from CSV file
		// IMPORTANT: Reliant on CSV Headers matching <option> headers, should one change this will fail
		// They will look something like this:
		// <select name="column_map[0]">
		// <option>CSV Header</option>

		$csv_file = fopen( './tests/_data/'.$file, 'r' );
		$csv_headers = fgetcsv( $csv_file, 0, ',' );

		$count = count( $csv_headers );
		for ( $i = 0; $i < $count; $i++ ) {
			$I->selectOption( 'column_map['.$i.']', $csv_headers[ $i ] );
		}

		$I->seeInField( '#submit', 'Perform Import' );
		$I->click( '#submit' );

		// Check that it imported (or rather, is in the process of doing so...)
		$I->see( 'Importing Data' );
	}

	public function edit_event( $name ) {
		$I = $this;

		$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events' );
		$I->fillField( 's', $name );
		$I->click( '#search-submit' );
		$I->click( $name );
	}
}
