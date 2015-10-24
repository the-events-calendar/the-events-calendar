<?php
$I = new AcceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( 'verify change to TEC Events Slug Setting' );

$I->activate_tec();
$I->set_pretty_permalinks();

// Explicitly set the slug to the default
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common' );
$I->fillField( 'eventsSlug', 'events' );
$I->click( 'Save Changes' );

// Verify Events Calendar is at default URL
$I->amOnPage( '/events/list/' );
$I->see( 'Upcoming Events' );

// Change Events URL
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common' );
$I->fillField( 'eventsSlug', 'classes' );
$I->click( 'Save Changes' );

// Verify Events Calendar is at new URL.
$I->amOnPage( '/classes/list/' );
$I->see( 'Upcoming Events' );

// Change the slug back to the default
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common' );
$I->fillField( 'eventsSlug', 'events' );
$I->click( 'Save Changes' );
