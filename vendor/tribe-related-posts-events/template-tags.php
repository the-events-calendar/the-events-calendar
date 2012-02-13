<?php
/* Related Posts Template Tags */

// Get related posts.
if ( !function_exists( 'tribe_get_related_posts' ) ) { 
	function tribe_get_related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false, $post_type='post' ) {
		return TribeRelatedPosts::getPosts( $tag, $count, $blog, $only_display_related, $post_type );
	}
}

// Display related posts.
if ( !function_exists( 'tribe_related_posts' ) ) { 
	function tribe_related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false, $thumbnails=false, $post_type='post' ) {
		TribeRelatedPosts::displayPosts( $tag, $count, $blog, $only_display_related, $thumbnails, $post_type );
	}
}

// Check if post has related posts.
if ( !function_exists( 'tribe_has_related_posts' ) ) { 
	function tribe_has_related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false, $post_type='post' ) {
		$posts = get_related_posts( $tag, $count, $blog, $only_display_related, $post_type );
		return count( $posts ) > 0;
	}
}