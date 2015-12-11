<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that a tag can be created" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Create a tag and test it exists
$I->createOrganizer( array( 'organizerTitle' => 'New Organizer Name', 'organizerContent' => 'New Organizer Details', 'organizerPhone' => '111-111-1111', 'organizerWebsite' => 'www.test.com', 'organizerEmail' => "tommy@test.com" ) );
//$organizerTitle		= $updateEvent['originalTitle'];
//Delete Organizer
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_organizer' );
$I->click( 'New Organizer Name');
$I->click( 'Move to Trash');

// TO DO - would be nice to add some negative tests to test these form fields are validating
