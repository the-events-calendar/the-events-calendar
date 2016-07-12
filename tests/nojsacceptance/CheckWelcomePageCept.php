<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that the Welcome Page has the right content" );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=tribe-common&tec-welcome-message=1' );

// assert
$I->seeElement('.tribe_welcome_page');
$I->seeElement('.tribe-welcome-message');
$I->seeElement('.tribe-welcome-video-wrapper');
//$I->seeInSource( '<div id="player" class="player player-1449844242682 js-player-fullscreen with-fullscreen">');
//$I->see('Keep The Events Calendar Core FREE');
// TO DO - Check to make sure newsletter subscription options work 
