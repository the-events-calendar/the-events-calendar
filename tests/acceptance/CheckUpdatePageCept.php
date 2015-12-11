<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that the Update Page has the right content" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Get to the Welcome Page page
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common&tec-update-message=1' );

//Check content - To Do - maybe create a list of correct content to check for
$I->see( 'Thanks for Updating The Events Calendar');
$I->see( 'You are running Version 4.0.1 and deserve a hug :-)');
$I->seeInSource( 'Keep the Core Plugin
<strong>FREE</strong>');
$I->seeInSource('<a class="button-primary" target="_blank" href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5">Rate It</a>');
$I->seeInSource('<input id="listthkduyk" type="checkbox" name="cm-ol-thkduyk">');

// TO DO - Check to make sure newsletter subscription options work
// TO DO - Check to make sure links work 

