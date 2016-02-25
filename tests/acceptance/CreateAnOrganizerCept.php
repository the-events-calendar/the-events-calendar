<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that an organizer can be created" );

// arrange
$I->activate_tec();
$post_title         = 'John Doe';
$existing_organizer = get_page_by_title( $post_title, OBJECT, 'tribe_organizer' );
if ( $existing_organizer ) {
	wp_delete_post( $existing_organizer->ID );
}

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post-new.php?post_type=tribe_organizer' );
$I->fillField( 'post_title', $post_title );
$I->click( '#publish' );
// TODO: additional fields

// assert
$I->amOnAdminPage( '/edit.php?post_type=tribe_organizer' );
$created_organizer = get_page_by_title( $post_title, OBJECT, 'tribe_organizer' );
$row               = 'tr.type-tribe_organizer.post-' . $created_organizer->ID;
$I->seeElement( $row );
$I->see( $post_title, $row );
// TODO: check additional fields
