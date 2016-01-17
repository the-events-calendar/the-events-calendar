<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that a category can be created" );

$I->activate_tec();
$I->set_pretty_permalinks();

//Create a tag and test it exists
$I->createTag( array( 'tagName' => 'New Tag Name', 'tagSlug' => 'New Slug', 'tagDescription' => 'The tag descripton' ) );

//Delete Tag
