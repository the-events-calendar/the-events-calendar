<?php

/**************************************************
 * TABLE OF CONTENTS
 * Event Functions
 * Venue Functions
 * Organizer Functions
 **************************************************/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) && !function_exists( 'tribe_create_event' ) ) {

	/**************************************************
	 * SECTION: Event Functions
	 **************************************************/
	
	/**
	 * Create an Event
	 *
	 * @param array $args - Elements that make up post to insert.
	 * @return int - ID of the event that was created. False if insert failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see wp_insert_post()
	 */
	function tribe_create_event($args) {
		$postId = TribeEventsAPI::createEvent($args);
		return $postId;
	}

	/**
	 * Update an Event
	 *
	 * @param int $postId - ID of the event to be modified.
	 * @param array $args - Args for updating the post.
	 * @return int - ID of the event that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 */
	function tribe_update_event($postId, $args) {
		$postId = TribeEventsAPI::updateEvent($postId, $args);
		return $postId;
	}

	/**
	 * Delete an Event
	 *
	 * @param int $postId - ID of the event to be deleted.
	 * @param bool $force_delete - Whether to bypass trash and force deletion. Defaults to false.
	 * @return bool false if delete failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see wp_delete_post()
	 */
	function tribe_delete_event($postId, $force_delete = false) {
		$success = TribeEventsAPI::deleteEvent($postId, $args);
		return $success;
	}

	/**************************************************
	 * SECTION: Venue Functions
	 **************************************************/

	/**
	 * Create a Venue
	 *
	 * @param array $args - Elements that make up post to insert.
	 * @return int - ID of the Venue that was created. False if insert failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see wp_insert_post()
	 */
	function tribe_create_venue($args) {
		$postId = TribeEventsAPI::createEvent($args);
		return $postId;
	}

	/**
	 * Update a Venue
	 *
	 * @param int $postId - ID of the Venue to be modified.
	 * @param array $args - Args for updating the post.
	 * @return int - ID of the Venue that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 */
	function tribe_update_venue($postId, $args) {
		$postId = TribeEventsAPI::updateEvent($postId, $args);
		return $postId;
	}

	/**
	 * Delete a Venue
	 *
	 * @param int $postId - ID of the Venue to be deleted.
	 * @param bool $force_delete - Whether to bypass trash and force deletion. Defaults to false.
	 * @return bool false if delete failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see wp_delete_post()
	 */
	function tribe_delete_venue($postId, $force_delete = false) {
		$success = TribeEventsAPI::deleteVenue($postId, $args);
		return $success;
	}

	/**************************************************
	 * SECTION: Organizer Functions
	 **************************************************/
	
	/**
	 * Create an Organizer
	 *
	 * @param array $args - Elements that make up post to insert.
	 * @return int - ID of the Organizer that was created. False if insert failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see wp_insert_post()
	 */
	function tribe_create_organizer($args) {
		$postId = TribeEventsAPI::createOrganizer($args);
		return $postId;
	}

	/**
	 * Update an Organizer
	 *
	 * @param int $postId - ID of the Organizer to be modified.
	 * @param array $args - Args for updating the post.
	 * @return int - ID of the Organizer that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 */
	function tribe_update_organizer($postId, $args) {
		$postId = TribeEventsAPI::updateOrganizer($postId, $args);
		return $postId;
	}

	/**
	 * Delete an Organizer
	 *
	 * @param int $postId - ID of the Organizer to be deleted.
	 * @param bool $force_delete - Whether to bypass trash and force deletion. Defaults to false.
	 * @return bool false if delete failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see wp_delete_post()
	 */
	function tribe_delete_organizer($postId, $force_delete = false) {
		$success = TribeEventsAPI::deleteOrganizer($postId, $args);
		return $success;
	}	
}
?>