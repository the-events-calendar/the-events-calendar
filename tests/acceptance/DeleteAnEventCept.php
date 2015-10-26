<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that deleted events can't be viewed on front end" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Set up test Event and test it exists
$I->createEvent( array( 'title' => 'Test Event A' ) );
$event_url = $I->grabTextFrom( '#sample-permalink' );
$I->amOnPage( $event_url );
$I->see( 'Test Event A' );

//Delete Event
$I->click( '.quicklinks #wp-admin-bar-edit a' );
$I->click( 'Move to Trash' );

$I->amOnPage( $event_url );
$I->see( 'Oops! That page' );