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

		// @todo Full Validation of Event Properties based on passed flag
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

	/**
	 * Update Event
	 */
	public function edit_event( $updateEvent ) {
		$originalTitle = $updateEvent['originalTitle'];
		$newTitle      = $updateEvent['newTitle'];
		$content       = $updateEvent['content'];
		$allDay        = $updateEvent['allDay'];
		if ( empty( $updateEventp['originalTitle'] ) || empty( $updateEvent['newTitle'] ) ) {
			throw new \InvalidArgumentException( 'Title missing' );
		}

		$I = $this;

		$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events' );
		$I->fillField( 's', $originalTitle );
		$I->click( '#search-submit' );
		$I->click( $originalTitle );

		//We should now be on the edit page but check
		//$I->see( $updateEvent['originalTitle'] );

		$I->fillField( 'post_title', $updateEvent['newTitle'] );
		$I->fillField( 'content', $updateEvent['content'] ); // need to target WYSIWYG instance
		if ( $updateEvent['allDay'] ) {
			$I->checkOption( '#allDayCheckbox' );
		}
		$I->click( '#publish' );
		//$I->see( $updateEvent['newTitle'] );
		// @todo - Full Validation of Event Properties based of passed flag - also $event['name'] needs a default value
	}


	public function activate_tec() {
		$this->bootstrapWp();
		$this->loadWpComponent( 'plugins' );
		update_option( 'active_plugins', [ ] );
		activate_plugin( 'the-events-calendar/the-events-calendar.php' );
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

		$csv_file    = fopen( './tests/_data/' . $file, 'r' );
		$csv_headers = fgetcsv( $csv_file, 0, ',' );

		$count = count( $csv_headers );
		for ( $i = 0; $i < $count; $i ++ ) {
			$I->selectOption( 'column_map[' . $i . ']', $csv_headers[ $i ] );
		}

		$I->seeInField( '#submit', 'Perform Import' );
		$I->click( '#submit' );

		// Check that it imported (or rather, is in the process of doing so...)
		$I->see( 'Importing Data' );
	}

	public function getTribeOptionFromDatabase( $key, $default = '' ) {
		$options = $this->grabOptionFromDatabase( 'tribe_events_calendar_options' );
		if ( empty( $options ) ) {
			return $default;
		}

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	public function setTribeOption( $key, $value ) {
		$option_name = 'tribe_events_calendar_options';
		$options     = $this->grabOptionFromDatabase( $option_name );
		if ( empty( $options ) ) {
			$this->haveOptionInDatabase( $option_name, [ $key => $value ] );
		} else {
			$this->haveOptionInDatabase( $option_name, array_merge( $options, [ $key => $value ] ) );
		}
	}
}
