<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that a category can be created" );

$term_slug = 'some-event-category';

$I->dontSeeTermInDatabase(['slug' => $term_slug, 'taxonomy' => 'tribe_events_cat']);

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' );
$I->fillField( 'tag-name', 'Some event category' );
$I->fillField( 'slug', $term_slug );
$I->fillField( 'description', 'Yet another event term' );
$I->click( '#submit' );

// assert
$I->seeTermInDatabase(['slug' => $term_slug, 'taxonomy' => 'tribe_events_cat']);
