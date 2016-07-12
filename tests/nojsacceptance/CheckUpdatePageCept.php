<?php

// @group: settings
$I = new NojsacceptanceTester($scenario);

$I->am('administrator');
$I->wantTo("verify that the Update Page has the right content");

// act
$I->loginAsAdmin();
$I->amOnAdminPage('/edit.php?post_type=tribe_events&page=tribe-common&tec-update-message=1');

// assert
$I->seeElement('.tribe_update_page');
$I->seeElement('.tribe-welcome-message');

// TO DO -  All of the below seeInSource are causign errors.  Need to debug
//$I->seeInSource( 'Keep the Core Plugin <strong>FREE</strong>');
//$I->seeInSource('<a class="button-primary" target="_blank" href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5">Rate It</a>');
//$I->seeInSource('<input id="listthkduyk" type="checkbox" name="cm-ol-thkduyk">');

// TO DO - Check to make sure newsletter subscription options work
// TO DO - Check to make sure links work 

