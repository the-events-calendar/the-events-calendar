<?php

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'Test CSV import' );
$I->lookForwardTo( 'Seeing them events, organizers, and venues in the database' );

//$I->activate_tec();
// Upload Organizer
//$I->upload_csv( 'organizers', 'csv-test-organizers.csv' );
//
//// Go to the organizers page
//$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_organizer' );
//// make sure page loaded successfully
//$I->cantSee( 'Cheatin' );
//$I->click( 'George-Michael Bluth' );
//
//$I->seeInField( 'post_title', 'George-Michael Bluth' );
//$I->seeInField( '#OrganizerPhone', '+1-987-555-1238' );
//$I->seeInField( '#OrganizerWebsite', 'http://fakeblock.com' );
//$I->seeInField( '#OrganizerEmail', 'boygeorge@halliburtonteen.com' );
//
//// Upload Venues
//$I->upload_csv( 'venues', 'csv-test-venues.csv' );
//
//$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_venue' );
//// make sure page loaded successfully
//$I->cantSee( 'Cheatin' );
//$I->click( 'Soup Kitchen International' );
//
//$I->seeInField( 'post_title', 'Soup Kitchen International' );
//$I->seeInField( 'venue[Address]', '259-A West 55th Street' );
//$I->seeInField( 'venue[City]', 'New York' );
////$I->seeOptionIsSelected( 'venue[Country]', 'United States' ); 	//hidden using Chosen - build helper
////$I->seeOptionIsSelected( 'venue[State]', 'New York' ); 			//hidden using Chosen - build helper
//$I->seeInField( 'venue[Zip]', '10019' );
//$I->seeInField( 'venue[Phone]', '+1 (800) 555-8234' );
//
////@todo Test the following once it's importable
////$I->seeInField( 'venue[URL]', '' );
////$I->seeCheckboxIsChecked( 'venue[ShowMap]' );
////$I->seeCheckboxIsChecked( 'venue[ShowMapLink]' );
//
//// Upload Events
//$I->upload_csv( 'events', 'csv-test-events.csv' );
//$I->edit_event( 'Ankh-Sto Associates' );
//
//$I->seeInField( 'post_title', 'Ankh-Sto Associates' );
//$I->seeInField( 'content', 'Ankh-Sto Associates description goes here.' );
////$I->seeCheckboxIsChecked( 'EventAllDay' ); @todo this lines relies on the CSV importer understanding the value "Yes", as per our docs: http://tri.be/using-the-events-calendars-csv-importer/
//$I->seeInField( 'EventStartDate', '2014-11-25' );
////$I->seeInField('EventStartHour', '01');
////$I->seeInField('EventStartMinute', '00');
////$I->seeInField('EventStartMeridian', 'am');
//$I->seeInField( 'EventEndDate', '2014-11-25' );
////$I->seeInField('EventEndHour', '01');
////$I->seeInField('EventEndMinute', '00');
////$I->seeInField('EventEndMeridian', 'am');
//
////$I->seeOptionIsSelected( 'venue[VenueID]', 'The Shire' );  //hidden using Chosen - build helper
//$I->seeCheckboxIsChecked( '#EventShowMap' );
////$I->seeCheckboxIsChecked( 'venue[EventShowMapLink]' ); @todo this lines relies on the CSV importer understanding the value "1", as per our docs: http://tri.be/using-the-events-calendars-csv-importer/
////$I->seeOptionIsSelected( 'organizer[OrganizerID]', 'Elvis' ); //hidden using CHosen - build helper
//$I->seeInField( 'EventURL', 'https://ankh-sto-associates.gov' );
//
//// Codeception errors our when you try to check for blank fields
////$I->seeInField('EventCurrencySymbol', '');
////$I->seeInField('EventCurrencyPosition', '');
////$I->seeInField('EventCost', '');
//
//// these are commented out because we can't guarantee the Category IDs
////$I->seeCheckboxIsChecked( '#in-tribe_events_cat-2' ); // Convention
////$I->dontSeeCheckboxIsChecked( '#in-tribe_events_cat-4' ); // Concert
////$I->dontSeeCheckboxIsChecked( '#in-tribe_events_cat-3' ); // Conference
