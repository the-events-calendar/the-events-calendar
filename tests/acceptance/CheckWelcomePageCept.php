<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that the Welcome Page has the right content" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Get to the Welcome Page page
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common&tec-welcome-message=1' );

//Check content - To Do - maybe create a list of correct content to check for
$I->see( 'Welcome to The Events Calendar');
$I->see( 'You are running Version 4.0.1 and deserve a hug :-)');
$I->seeInSource( '<div id="player" class="player player-1449844242682 js-player-fullscreen with-fullscreen">');
$I->see('Keep The Events Calendar Core FREE');
// TO DO - Check to make sure newsletter subscription options work 
