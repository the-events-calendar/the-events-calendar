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
	 * Create an Organizer
	 *
	 * $args accepts all the args that can be passed to wp_insert_post().
	 * In addition to that, the following args can be passed specifically
	 * for the process of creating an Organizer:
	 *
	 * - Organizer string - Title of the Organizer. (required)
	 * - Email string - Email address of the Organizer.
	 * - Website string - URL of the Organizer.
	 * - Phone string - Phone number for the Organizer.
	 *
	 * @param array $args Elements that make up post to insert.
	 *
	 * @return int ID of the Organizer that was created. False if insert failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see      wp_insert_post()
	 * @category Organizers
	 */
	function tribe_create_organizer( $args ) {
		$postId = Tribe__Events__API::createOrganizer( $args );

		return $postId;
	}

	/**
	 * Update an Organizer
	 *
	 * @param int   $postId ID of the Organizer to be modified.
	 * @param array $args   Args for updating the post. See {@link tribe_create_organizer()} for more info.
	 *
	 * @return int ID of the Organizer that was created. False if update failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see      wp_update_post()
	 * @see      tribe_create_organizer()
	 * @category Organizers
	 */
	function tribe_update_organizer( $postId, $args ) {
		$postId = Tribe__Events__API::updateOrganizer( $postId, $args );

		return $postId;
	}

	/**
	 * Delete an Organizer
	 *
	 * @param int  $postId       ID of the Organizer to be deleted.
	 * @param bool $force_delete Whether to bypass trash and force deletion. Defaults to false.
	 *
	 * @return bool false if delete failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see      wp_delete_post()
	 * @category Organizers
	 */
	function tribe_delete_organizer( $postId, $force_delete = false ) {
		$success = Tribe__Events__API::deleteOrganizer( $postId, $args );

		return $success;
	}

}
?>