<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that a tag can be created" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Create a tag and test it exists
$I->createVenue( array( 'venueTitle' => 'New Venue Name', 'venueContent' => 'New Venue Details', 'venueAddress' => '111 Main St', 'venueCity' => 'New York' ) );

//Delete Venue
