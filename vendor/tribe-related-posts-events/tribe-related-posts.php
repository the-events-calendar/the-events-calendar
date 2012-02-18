<?php
/*
 Plugin Name: Tribe Related Posts
 Description: Template tags and shortcode to display related posts by taxonomy.
 Author: Modern Tribe, Inc., Paul Hughes
 Version: 1.1
 Author URI: http://tri.be
 */

// Include plugin files.
include( 'tribe-related-posts.class.php' );
include( 'tribe-related-posts-widget.php' );
include( 'template-tags.php' );

TribeRelatedPosts::instance();