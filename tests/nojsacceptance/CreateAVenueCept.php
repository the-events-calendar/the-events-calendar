<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that a venue can be created" );

$post_title     = 'House of mine';

$I->dontSeePostInDatabase(['post_type' => 'tribe_venue', 'post_title' => $post_title]);

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post-new.php?post_type=tribe_venue' );
$I->fillField( 'post_title', $post_title );
$I->click( '#publish' );
// TODO: additional fields

// assert
$I->seePostInDatabase(['post_type' => 'tribe_venue', 'post_title' => $post_title]);
// TODO: check additional fields
