<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that an event can be modified" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Set up test Event and test it exists
$I->createEvent( array( 'title' => 'An Event To Update', 'content' => 'Not yet updated', 'allDay' => 'true' ) );
$event_url = $I->grabTextFrom( '#sample-permalink' );
$I->amOnPage( $event_url );
$I->see( 'An Event To Update' );

//Modify the event 
$I->edit_event( array( 'originalTitle' => 'An Event To Update', 'newTitle' => 'Updated Event', 'content' => 'Not yet updated', 'allDay' => 'true' ) );


//Delete Event
$I->click( '#delete-action' );
$I->click( 'Move to Trash' );

$I->amOnPage( $event_url );
$I->see( 'Oops! That page' );