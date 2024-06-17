<?php
/**
 * The Events Calendar Advanced Functions for the Organizer Post Type
 *
 * These functions can be used to manipulate Organizer data. These functions may be useful for integration with other WordPress plugins and extended functionality.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Create an Organizer using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_organizers()->create()` method](https://docs.theeventscalendar.com/apis/orm/create/organizers/).
	 *
	 * Legacy Note:
	 * $args accepts all the args that can be passed to wp_insert_post().
	 * In addition to that, the following args can be passed specifically
	 * for the process of creating an Organizer:
	 *
	 * - Organizer string - Title of the Organizer. (required)
	 * - Email string - Email address of the Organizer.
	 * - Website string - URL of the Organizer.
	 * - Phone string - Phone number for the Organizer.
	 *
	 * @since 3.0.0
	 *
	 * @see      wp_insert_post()
	 * @link     http://codex.wordpress.org/Function_Reference/wp_insert_post
	 *
	 * @param array $args Elements that make up post to insert.
	 *
	 * @return int|false ID of the Organizer that was created. False if insert failed.
	 */
	function tribe_create_organizer( $args ) {
		$postId = Tribe__Events__API::createOrganizer( $args );

		return $postId;
	}

	/**
	 * Update an Organizer using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_organizers()->save()` method](https://docs.theeventscalendar.com/apis/orm/update).
	 *
	 * @since 3.0.0
	 *
	 * @see      tribe_create_organizer()
	 * @see      wp_update_post()
	 * @link     http://codex.wordpress.org/Function_Reference/wp_update_post
	 *
	 * @param int   $postId ID of the Organizer to be modified.
	 * @param array $args Args for updating the post.
	 *
	 * @return int|false ID of the Organizer that was created. False if update failed.
	 */
	function tribe_update_organizer( $postId, $args ) {
		$postId = Tribe__Events__API::updateOrganizer( $postId, $args );

		return $postId;
	}

	/**
	 * Delete an Organizer using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_organizers()->delete()` method](https://docs.theeventscalendar.com/apis/orm/delete).
	 *
	 * @since 3.0.0
	 *
	 * @see      wp_delete_post()
	 * @link     http://codex.wordpress.org/Function_Reference/wp_delete_post
	 *
	 * @param  int  $post_id       ID of the Organizer to be deleted.
	 * @param  bool $force_delete Whether to bypass trash and force deletion. Defaults to false.
	 * @return WP_Post|false|null False if delete failed, null if delete succeeded.
	 */
	function tribe_delete_organizer( $post_id, $force_delete = false ) {
		return Tribe__Events__API::deleteOrganizer( $post_id, $force_delete );
	}
}
