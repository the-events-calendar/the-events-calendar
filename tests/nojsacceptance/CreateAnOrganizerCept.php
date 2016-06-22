<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that an organizer can be created" );

$post_title         = 'John Doe';

$I->dontSeePostInDatabase(['post_title' => $post_title, 'post_type' => 'tribe_organizer']);

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post-new.php?post_type=tribe_organizer' );
$I->fillField( 'post_title', $post_title );
$I->click( '#publish' );
// TODO: additional fields

// assert
$I->seePostInDatabase(['post_title' => $post_title, 'post_type' => 'tribe_organizer']);
