<?php
$I = new AcceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( 'verify change to TEC Events Slug Setting' );

// arrange
$I->activate_tec();
$I->setPermalinkStructureAndFlush( '/%postname%/' );
$current_slug = $I->getTribeOptionFromDatabase( 'eventsSlug', 'events' );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=tribe-common' );
$new_slug = 'classes-and-courses';
$I->fillField( 'eventsSlug', $new_slug );
$I->click( '#tribeSaveSettings' );

// assert
$I->amOnPage( '/' . $current_slug );
$I->seeElement( 'body.error404' );
$I->amOnPage( '/' . $new_slug );
$I->dontSeeElement( 'body.error404' );

// change the slug back to the default
$I->setTribeOption( 'eventsSlug', 'events' );
