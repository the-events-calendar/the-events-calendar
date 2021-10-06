<?php
/**
 * Class in charge of processing the persistence of the reference of deleted occurrence ID and a post ID, this is required
 * as we need a mechanism to hold references in cases a post is requested in the middle of a redirection or to
 * prevent additional issues of requesting not found events.
 */

namespace TEC\Custom_Tables\V1\Edits\Event;

use Tribe__Events__Main as TEC;

use function delete_transient;
use function get_post_type;
use function get_transient;
use function set_transient;
use function tribe_get_request_var;

/**
 * Class Unstable_Occurrence
 *
 * @since   TBD
 *
 * @package TEC\Pro\Custom_Tables\V1\Edits\Event
 */
class Unstable_Occurrence {
	const TRANSIENT_DELETED_OCCURRENCES = 'tribe_recurrence_deleted_occurrences';

	/**
	 * Add an occurrence ID into a transient option, to hold the value "presence" for a moment,
	 *
	 * @since TBD
	 *
	 * @param $occurrence_id
	 * @param $post_id
	 */
	public function add( $occurrence_id, $post_id ) {
		$deleted_occurrences = get_transient( self::TRANSIENT_DELETED_OCCURRENCES );

		if ( ! is_array( $deleted_occurrences ) ) {
			$deleted_occurrences = [];
		}

		$deleted_occurrences[ $occurrence_id ] = $post_id;

		set_transient( self::TRANSIENT_DELETED_OCCURRENCES, $deleted_occurrences, DAY_IN_SECONDS );
	}

	/**
	 * Redirect the Query to the main post ID if the occurrence_id is present on the transient options.
	 *
	 * @since TBD
	 *
	 * @param $occurrence_id
	 *
	 * @return string|null
	 */
	public function replace_query( $occurrence_id ) {
		$deleted_occurrences = get_transient( self::TRANSIENT_DELETED_OCCURRENCES );

		if ( ! is_array( $deleted_occurrences ) ) {
			return null;
		}

		if ( array_key_exists( $occurrence_id, $deleted_occurrences ) ) {
			global $wpdb;

			$post_id = $deleted_occurrences[ $occurrence_id ];

			unset( $deleted_occurrences[ $occurrence_id ] );

			// Redirect the query to the original event ID.
			return $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $post_id );
		}

		return null;
	}

	/**
	 * Clear the transient as soon as the real post ID is opened.
	 *
	 * @since TBD
	 */
	public function clear() {
		$post_id = (int) tribe_get_request_var( 'post', 0 );
		$action  = tribe_get_request_var( 'action', '' );

		if ( $action !== 'edit' ) {
			return false;
		}

		if ( $post_id <= 0 ) {
			return false;
		}

		// Not an event.
		if ( get_post_type( $post_id ) !== TEC::POSTTYPE ) {
			return false;
		}

		$occurrences = get_transient( self::TRANSIENT_DELETED_OCCURRENCES );

		// Not array meaning this has not been set.
		if ( ! is_array( $occurrences ) ) {
			return false;
		}

		// If is empty just remove it.
		if ( empty( $occurrences ) ) {
			return delete_transient( self::TRANSIENT_DELETED_OCCURRENCES );
		}

		$values = array_flip( $occurrences );
		// This was not stored on the transient move on.
		if ( ! array_key_exists( $post_id, $values ) ) {
			return false;
		}

		$key = $values[ $post_id ];
		unset( $occurrences[ $key ] );

		if ( empty( $occurrences ) ) {
			return delete_transient( self::TRANSIENT_DELETED_OCCURRENCES );
		}

		return set_transient( self::TRANSIENT_DELETED_OCCURRENCES, $occurrences, DAY_IN_SECONDS );
	}
}
