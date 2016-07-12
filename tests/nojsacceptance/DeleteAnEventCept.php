<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that deleted events can't be viewed on front end" );

// arrange
$title = 'An event of mine';
$event_id = $I->havePostInDatabase(['post_type' =>'tribe_events', 'post_title' => $title]);

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post.php?post=' . $event_id . '&action=edit' );
$I->click( '#delete-action > a' );

// assert
$I->useTheme( 'twentyfifteen' );
$I->amOnPage( '/index.php?p='. $event_id .'$post_type=tribe_events');
$I->seeElement( 'body.error404' );
