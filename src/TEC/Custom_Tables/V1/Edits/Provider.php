<?php
/**
 * Handles The Events Calendar edit operations in the context of Custom Tables.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Edits
 */

namespace TEC\Custom_Tables\V1\Edits;

use tad_DI52_ServiceProvider as Service_Provider;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Edits
 */
class Provider extends Service_Provider {
	/**
	 * Binds the implementations, and hooks the filters, required to update the custom
	 * tables on Event edit operations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( Event::class, Event::class );

		if ( ! has_action( 'tribe_events_update_meta', [ $this, 'upsert_event_data' ] ) ) {
			add_action( 'tribe_events_update_meta', [ $this, 'upsert_event_data' ], 100 );
		}
		if ( ! has_action( 'delete_post', [ $this, 'delete_event_data' ] ) ) {
			add_action( 'delete_post', [ $this, 'delete_event_data' ], 100, 2 );
		}
	}

	/**
	 * Removes the filters added by the Provider.
	 *
	 * @since TBD
	 */
	public function unregister() {
		remove_action( 'tribe_events_update_meta', [ $this, 'upsert_event_data' ], 100 );
		remove_action( 'delete_post', [ $this, 'delete_event_data' ] );
	}

	/**
	 * On update or insertion of a Single Event, update or insert its information in the custom tables.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the Event the Event was correctly inserted or updated or not.
	 */
	public function upsert_event_data( $post_id ) {
		return $this->container->make( Event::class )->upsert( $post_id );
	}

	/**
	 * On deletion (NOT trashing) of a Single Event, remove its data from the custom tables.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 * @param WP_Post $post A reference to the Post object being deleted, might not be an Event
	 *                      post.
	 *
	 * @return bool Whether the deletion of the Event data from the custom tables was successful
	 *              or not.
	 */
	public function delete_event_data( $post_id, WP_Post $post ) {
		if ( TEC::POSTTYPE !== $post->post_type ) {
			// We should not handle the deletion at all, we're done.
			return true;
		}

		return $this->container->make( Event::class )->delete( $post_id );
	}
}
