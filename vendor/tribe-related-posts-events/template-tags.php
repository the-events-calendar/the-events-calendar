<?php
/* Related Posts Template Tags */

/** 
 * Template tag to get related posts. 
 *
 * @since 1.1
 * @author Paul Hughes
 * @param string $tags comma-separated list of tags.
 * @param int $count number of related posts to return.
 * @param string $blog the blog from which to fetch the related posts.
 * @param bool $only_display_related whether to display only related posts or others as well.
 * @param string $post_type the type of post to return.
 * @return array the related posts.
 */
function tribe_get_related_posts( $tag = false, $count = 5, $blog = false, $only_display_related = false, $post_type = 'post' ) {
	return apply_filters( 'tribe-get-related-posts', TribeRelatedPosts::getPosts( $tag, $count, $blog, $only_display_related, $post_type ) );
}

/** 
 * Template Tag to display related posts.
 *
 * @since 1.1
 * @author Paul Hughes
 * @param string $tags comma-separated list of tags.
 * @param int $count number of related posts to return.
 * @param string $blog the blog from which to fetch the related posts.
 * @param bool $only_display_related whether to display only related posts or others as well.
 * @param bool $thumbnails whether to display thumbnails or not.
 * @param string $post_type the type of post to return.
 * @return void
 */
function tribe_related_posts( $tag = false, $count = 5, $blog = false, $only_display_related = false, $thumbnails = false, $post_type = 'post' ) {
	apply_filters( 'tribe-related-posts', TribeRelatedPosts::displayPosts( $tag, $count, $blog, $only_display_related, $thumbnails, $post_type ) );
}

/** 
 * Template tag to check if the current post has related posts. 
 *
 * @since 1.1
 * @author Paul Hughes
 * @param string $tags comma-separated list of tags.
 * @param int $count number of related posts to return.
 * @param string $blog the blog from which to fetch the related posts.
 * @param bool $only_display_related whether to display only related posts or others as well.
 * @param string $post_type the type of post to return.
 * @return bool whether the current post has related posts or not.
 */
function tribe_has_related_posts( $tag = false, $count = 5, $blog = false, $only_display_related = false, $post_type = 'post' ) {
	$posts = get_related_posts( $tag, $count, $blog, $only_display_related, $post_type );
	return count( $posts ) > 0;
}