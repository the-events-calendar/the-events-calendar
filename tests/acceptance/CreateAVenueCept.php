<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that a venue can be created" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Create a venue and test it exists
$I->createVenue( array( 'venueTitle' => 'New Venue Name', 'venueContent' => 'New Venue Details', 'venueAddress' => '111 Main St', 'venueCity' => 'New York' ) );

//Delete Venue
// TO DO - need this done so we don't end up with a giant list of venues
