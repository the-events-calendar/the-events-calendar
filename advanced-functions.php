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
	 * $args accepts all the args that can be passed to wp_insert_post(). 
	 * In addition to that, the following args can be passed specifically 
	 * for the process of creating an Event:
	 * 
	 * 'EventStartDate' date string (required) - Start date of the Event.
	 * 'EventEndDate' date string (required) - End date of the Event.
	 * 'EventAllDay' bool - Set to true if event has no start / end time and should run all day.
	 * 'EventStartHour' string - Event start hour (01 - 12).
	 * 'EventStartMinute' string - Event start minute (01 - 60).
	 * 'EventStartMeridian' string - Event start meridian (am or pm).
	 * 'EventEndHour' string - Event end hour (01 - 12).
	 * 'EventEndMinute' string - Event end minute (01 - 60).
	 * 'EventEndMeridian' string - Event end meridian (am or pm).
	 * 'EventHideFromUpcoming' bool - Set to true to hide this Event from the upcoming list view.
	 * 'EventShowMapLink' bool - Set to true to display a link to the map in the Event view.
	 * 'EventShowMap' bool - Set to true to embed the map in the Event view.
	 * 'EventCost' string - Default cost of the Event.
	 * 'Venue' array - Array of data to create or update an Venue to be associated with the Event. 
	 * See {@link tribe_create_venue()}.
	 * 'Organizer' array - Array of data to create or update an Organizer to be associated with the Event. 
	 * See {@link tribe_create_organizer()}.
	 * 
	 * Note: If ONLY the 'VenueID'/'OrganizerID' value is set in the 'Venue'/'Organizer' array, 
	 * then the specified Venue/Organizer will be associated with this Event without attempting 
	 * to edit the Venue/Organizer. If NO 'VenueID'/'OrganizerID' is passed, but other Venue/Organizer
	 * data is passed, then a new Venue/Organizer will be created.
	 *
	 * @param array $args - Elements that make up post to insert.
	 * @return int - ID of the event that was created. False if insert failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see wp_insert_post()
	 * @see tribe_create_venue()
	 * @see tribe_create_organizer()
	 */
	function tribe_create_event($args) {
		$postId = TribeEventsAPI::createEvent($args);
		return $postId;
	}

	/**
	 * Update an Event
	 *
	 * @param int $postId - ID of the event to be modified.
	 * @param array $args - Args for updating the post. See {@link tribe_create_event()} for more info.
	 * @return int - ID of the event that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 * @see tribe_create_event()
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
	 * $args accepts all the args that can be passed to wp_insert_post(). 
	 * In addition to that, the following args can be passed specifically 
	 * for the process of creating a Venue:
	 *
	 * 'Venue' string - Title of the Venue. (required)
	 * 'Country' string - Country code for the Venue country.
	 * 'Address' string - Street address of the Venue.
	 * 'City' string - City of the Venue.
	 * 'State' string - Two letter state abbreviation.
	 * 'Province' string - Province of the Venue.
	 * 'Zip' string - Zip code of the Venue.
	 * 'Phone' string - Phone number for the Venue.
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
	 * @param array $args - Args for updating the post. See {@link tribe_create_venue()} for more info.
	 * @return int - ID of the Venue that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 * @see tribe_create_venue()
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
	 * $args accepts all the args that can be passed to wp_insert_post(). 
	 * In addition to that, the following args can be passed specifically 
	 * for the process of creating an Organizer:
	 *
	 * 'Organizer' string - Title of the Organizer. (required)
	 * 'Email' string - Email address of the Organizer.
	 * 'Website' string - URL of the Organizer.
	 * 'Phone' string - Phone number for the Organizer.
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
	 * @param array $args - Args for updating the post. See {@link tribe_create_organizer()} for more info.
	 * @return int - ID of the Organizer that was created. False if update failed.
	 * @link http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see wp_update_post()
	 * @see tribe_create_organizer()
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