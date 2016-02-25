<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that an event title can be modified" );

// arrange
$I->activate_tec();
$old_title = 'A test event';
$event     = get_page_by_title( $old_title, OBJECT, 'tribe_events' );
if ( $event ) {
	// mind the status
	wp_delete_post( $event );
}
$event_id = wp_insert_post( [ 'post_title' => $old_title ] );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post.php?post=' . $event_id . '&action=edit' );
$I->fillField( 'post_title', 'A new title' );
$I->click( '#publish' );

// assert
$I->amOnPage( get_post_permalink( $event_id ) );
$I->see( 'A new title' );


