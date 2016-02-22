<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that a venue can be created" );

// arrange
$I->activate_tec();
$post_title     = 'House of mine';
$existing_venue = get_page_by_title( $post_title, OBJECT, 'tribe_venue' );
if ( $existing_venue ) {
	wp_delete_post( $existing_venue->ID );
}

// act
$I->amOnAdminPage( '/post-new.php?post_type=tribe_venue' );
$I->fillField( 'post_title', $post_title );
$I->click( '#publish' );
// TODO: additional fields

// assert
$I->amOnAdminPage( '/edit.php?post_type=tribe_venue' );
$created_venue = get_page_by_title( $post_title, OBJECT, 'tribe_venue' );
$row           = 'tr.type-tribe_venue.post-' . $created_venue->ID;
$I->seeElement( $row );
$I->see( $post_title, $row );
// TODO: check additional fields
