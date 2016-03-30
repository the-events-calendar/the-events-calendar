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
clean_post_cache($event_id);
$I->assertEquals('A new title', get_post($event_id)->post_title);


