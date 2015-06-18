<?php
/**
 * The Events Calendar Advanced Functions for the Event Post Type
 *
 * These functions can be used to manipulate Event data. These functions may be useful for integration with other WordPress plugins and extended functionality.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Create an Event
	 *
	 * $args accepts all the args that can be passed to wp_insert_post().
	 * In addition to that, the following args can be passed specifically
	 * for the process of creating an Event:
	 *
	 * - EventStartDate date string (required) - Start date of the Event.
	 * - EventEndDate date string (required) - End date of the Event.
	 * - EventAllDay bool - Set to true if event has no start / end time and should run all day.
	 * - EventStartHour string - Event start hour (01 - 12).
	 * - EventStartMinute string - Event start minute (01 - 60).
	 * - EventStartMeridian string - Event start meridian (am or pm).
	 * - EventEndHour string - Event end hour (01 - 12).
	 * - EventEndMinute string - Event end minute (01 - 60).
	 * - EventEndMeridian string - Event end meridian (am or pm).
	 * - EventHideFromUpcoming bool - Set to true to hide this Event from the upcoming list view.
	 * - EventShowMapLink bool - Set to true to display a link to the map in the Event view.
	 * - EventShowMap bool - Set to true to embed the map in the Event view.
	 * - EventCost string - Default cost of the Event.
	 * - Venue array - Array of data to create or update an Venue to be associated with the Event. {@link tribe_create_venue}.
	 * - Organizer array - Array of data to create or update an Organizer to be associated with the Event. {@link tribe_create_organizer}.
	 *
	 * Note: If ONLY the 'VenueID'/'OrganizerID' value is set in the 'Venue'/'Organizer' array,
	 * then the specified Venue/Organizer will be associated with this Event without attempting
	 * to edit the Venue/Organizer. If NO 'VenueID'/'OrganizerID' is passed, but other Venue/Organizer
	 * data is passed, then a new Venue/Organizer will be created.
	 *
	 * Also note that this function can be used only for the creation of events, supplying
	 * a post_type argument therefore is superfluous as it will be reset to the events post
	 * type in any case.
	 *
	 * @param array $args Elements that make up post to insert.
	 *
	 * @return int ID of the event that was created. False if insert failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_insert_post
	 * @see      wp_insert_post()
	 * @see      tribe_create_venue()
	 * @see      tribe_create_organizer()
	 * @category Events
	 */
	function tribe_create_event( $args ) {
		$args['post_type'] = Tribe__Events__Main::POSTTYPE;
		$postId = Tribe__Events__API::createEvent( $args );

		return $postId;
	}

	/**
	 * Update an Event
	 *
	 * @param int   $postId ID of the event to be modified.
	 * @param array $args   Args for updating the post. See {@link tribe_create_event()} for more info.
	 *
	 * @return int ID of the event that was created. False if update failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_update_post
	 * @see      wp_update_post()
	 * @see      tribe_create_event()
	 * @category Events
	 */
	function tribe_update_event( $postId, $args ) {
		$postId = Tribe__Events__API::updateEvent( $postId, $args );

		return $postId;
	}

	/**
	 * Delete an Event
	 *
	 * @param int  $postId       ID of the event to be deleted.
	 * @param bool $force_delete Whether to bypass trash and force deletion. Defaults to false.
	 *
	 * @return bool false if delete failed.
	 * @link     http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see      wp_delete_post()
	 * @category Events
	 */
	function tribe_delete_event( $postId, $force_delete = false ) {
		$success = Tribe__Events__API::deleteEvent( $postId, $force_delete );

		return $success;
	}

}
