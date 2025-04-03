<?php
/**
 * The Events Calendar Advanced Functions for the Venue Post Type
 *
 * These functions can be used to manipulate Venue data. These functions may be useful for integration with other WordPress plugins and extended functionality.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Create a Venue using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_venue()->create()` method](https://docs.theeventscalendar.com/apis/orm/create/venues/)
	 *
	 * $args accepts all the args that can be passed to wp_insert_post().
	 * In addition to that, the following args can be passed specifically
	 * for the process of creating a Venue:
	 *
	 * - Venue string - Title of the Venue. (required)
	 * - Country string - Country code for the Venue country.
	 * - Address string - Street address of the Venue.
	 * - City string - City of the Venue.
	 * - State string - Two letter state abbreviation.
	 * - Province string - Province of the Venue.
	 * - Zip string - Zip code of the Venue.
	 * - Phone string - Phone number for the Venue.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Elements that make up post to insert.
	 *
	 * @see      wp_insert_post()
	 * @link     http://codex.wordpress.org/Function_Reference/wp_insert_post
	 *
	 * @return int|false ID of the Venue that was created. False if insert failed.
	 */
	function tribe_create_venue( $args ) {
		$postId = Tribe__Events__API::createVenue( $args );

		return $postId;
	}

	/**
	 * Update a Venue using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_venue()->save()` method](https://docs.theeventscalendar.com/apis/orm/update)
	 *
	 * @since 3.0.0
	 *
	 * @see      wp_update_post()
	 * @see      tribe_create_venue()
	 * @link     http://codex.wordpress.org/Function_Reference/wp_update_post
	 *
	 * @param int   $postId ID of the Venue to be modified.
	 * @param array $args   Args for updating the post. See {@link tribe_create_venue()} for more info.
	 * @return int ID of the Venue that was created. False if update failed.
	 */
	function tribe_update_venue( $postId, $args ) {
		$postId = Tribe__Events__API::updateVenue( $postId, $args );

		return $postId;
	}

	/**
	 * Delete a Venue using the legacy method.
	 *
	 * Note: This function is outdated and should be replaced with the [TEC ORM `tribe_venue()->delete()` method](https://docs.theeventscalendar.com/apis/orm/delete)
	 *
	 * @since 3.0.0
	 *
	 * @link     http://codex.wordpress.org/Function_Reference/wp_delete_post
	 * @see      wp_delete_post()
	 *
	 * @param int  $postId       ID of the Venue to be deleted.
	 * @param bool $force_delete Whether to bypass trash and force deletion. Defaults to false.
	 *
	 * @return bool false if delete failed.
	 */
	function tribe_delete_venue( $postId, $force_delete = false ) {
		$success = Tribe__Events__API::deleteVenue( $postId, $args );

		return $success;
	}

}
