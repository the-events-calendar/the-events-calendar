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
	 * Create's an Event.
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
	 * @category Events
	 *
	 * @link     http://codex.wordpress.org/Function_Reference/wp_insert_post
	 *
	 * @see      wp_insert_post()
	 * @see      tribe_create_venue()
	 * @see      tribe_create_organizer()
	 *
	 * @param array $args {
	 *     An array of elements that make up a post to update or insert. Accepts anything from {@see wp_insert_post()}.
	 *
	 *     @type int    $ID                      The post ID. If equal to something other than 0,
	 *                                           the post with that ID will be updated. Default 0.
	 *     @type int    $post_author             The ID of the user who added the post. Default is
	 *                                           the current user ID.
	 *     @type string $post_date               The date of the post. Default is the current time.
	 *     @type string $post_date_gmt           The date of the post in the GMT timezone. Default is
	 *                                           the value of `$post_date`.
	 *     @type mixed  $post_content            The post content. Default empty.
	 *     @type string $post_content_filtered   The filtered post content. Default empty.
	 *     @type string $post_title              The post title. Default empty.
	 *     @type string $post_excerpt            The post excerpt. Default empty.
	 *     @type string $post_status             The post status. Default 'draft'.
	 *     @type string $post_type               The post type. Default 'post'.
	 *     @type string $comment_status          Whether the post can accept comments. Accepts 'open' or 'closed'.
	 *                                           Default is the value of 'default_comment_status' option.
	 *     @type string $ping_status             Whether the post can accept pings. Accepts 'open' or 'closed'.
	 *                                           Default is the value of 'default_ping_status' option.
	 *     @type string $post_password           The password to access the post. Default empty.
	 *     @type string $post_name               The post name. Default is the sanitized post title
	 *                                           when creating a new post.
	 *     @type string $to_ping                 Space or carriage return-separated list of URLs to ping.
	 *                                           Default empty.
	 *     @type string $pinged                  Space or carriage return-separated list of URLs that have
	 *                                           been pinged. Default empty.
	 *     @type string $post_modified           The date when the post was last modified. Default is
	 *                                           the current time.
	 *     @type string $post_modified_gmt       The date when the post was last modified in the GMT
	 *                                           timezone. Default is the current time.
	 *     @type int    $post_parent             Set this for the post it belongs to, if any. Default 0.
	 *     @type int    $menu_order              The order the post should be displayed in. Default 0.
	 *     @type string $post_mime_type          The mime type of the post. Default empty.
	 *     @type string $guid                    Global Unique ID for referencing the post. Default empty.
	 *     @type array  $post_category           Array of category IDs.
	 *                                           Defaults to value of the 'default_category' option.
	 *     @type array  $tags_input              Array of tag names, slugs, or IDs. Default empty.
	 *     @type array  $tax_input               Array of taxonomy terms keyed by their taxonomy name. Default empty.
	 *     @type array  $meta_input              Array of post meta values keyed by their post meta key. Default empty.

	 *     @type string $EventStartDate          Start date of event (required).
	 *     @type string $EventEndDate            End date of event (required).
	 *     @type bool   $EventAllDay             Set to true if event has no start / end time and should run all day.
	 *     @type string $EventStartHour          Event start hour (01-12 if `EventStartMeridian` is also passed, else 00-23).
	 *     @type string $EventStartMinute        Event start minute (00-59).
	 *     @type string $EventStartMeridian      Event start meridian (am or pm).
	 *     @type string $EventEndHour            Event end hour (01-12 if `EventEndMeridian` is also passed, else 00-23).
	 *     @type string $EventEndMinute          Event end minute (00-59).
	 *     @type string $EventEndMeridian        Event end meridian (am or pm).
	 *     @type bool   $EventHideFromUpcoming   Set to true to hide this Event from the upcoming list view.
	 *     @type bool   $EventShowMapLink        Set to true to display a link to the map in the Event view.
	 *     @type string $EventShowMap            Set to true to embed the map in the Event view.
	 *     @type string $EventCost               Default cost of the Event.
	 *     @type string $EventURL                Link to the Event Website or Third-Party page.
	 *     @type string $FeaturedImage           URL or ID of a featured image.
	 *     @type string $Venue                   Array of data to create or update an Venue to be associated with the Event {@link tribe_create_venue}.
	 *     @type string $Organizer               Array of data to create or update an Organizer to be associated with the Event {@link tribe_create_organizer}.
	 *     @type string $_ecp_custom_[ID]        Pro Custom fields (Events Calendar Pro only).
	 * }
	 *
	 * @return int|bool ID of the event that was created. False if insert failed.
	 */
	function tribe_create_event( $args ) {
		$args['post_type'] = Tribe__Events__Main::POSTTYPE;
		$postId            = Tribe__Events__API::createEvent( $args );

		return is_wp_error( $postId ) ? false : $postId;
	}

	/**
	 * Update an Event.
	 *
	 * @category Events
	 *
	 * @link     http://codex.wordpress.org/Function_Reference/wp_update_post
	 *
	 * @see      wp_update_post()
	 * @see      tribe_create_event()
	 *
	 * @param int|bool   $postId  ID of the event to be modified.
	 * @param array      $args    Args for updating the post. See {@link tribe_create_event()} for more info.
	 *
	 * @return int|bool ID of the event that was created. False if update failed.
	 */
	function tribe_update_event( $postId, $args ) {
		$postId = Tribe__Events__API::updateEvent( $postId, $args );

		return is_wp_error( $postId ) ? false : $postId;
	}

	/**
	 * Delete an Event.
	 *
	 * @link     http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see      wp_delete_post()
	 * @category Events
	 *
	 * @param int  $post_id      Post ID of the Event.
	 * @param bool $force_delete Whether to bypass trash and force deletion. Defaults to false.
	 *
	 * @return bool false if delete failed.
	 */
	function tribe_delete_event( $post_id, $force_delete = false ) {
		return Tribe__Events__API::deleteEvent( $post_id, $force_delete );
	}

}
