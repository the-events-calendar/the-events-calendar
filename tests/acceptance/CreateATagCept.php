<?php

// @group: settings

// @group: settings
$I = new AcceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that a category can be created" );

// arrange
$I->activate_tec();
$term_slug = 'some-event-term';
$term      = get_term_by( 'slug', $term_slug, 'post_tag' );
if ( $term ) {
	wp_delete_term( $term->term_id, 'post_tag' );
}

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit-tags.php?taxonomy=post_tag&post_type=tribe_events' );
$I->fillField( 'tag-name', 'Some event term' );
$I->fillField( 'slug', $term_slug );
$I->fillField( 'description', 'Yet another event term' );
$I->click( '#submit' );

// assert
$I->waitForJqueryAjax( 10 );
$term = get_term_by( 'slug', $term_slug, 'post_tag' );
$I->assertNotEmpty( $term );
$I->assertEquals( 'Some event term', $term->name );
$I->assertEquals( 'Yet another event term', $term->description );
