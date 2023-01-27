<?php
/**
 * Provides methods to alter how the Repository perform updates.
 *
 * @since   6.0.3.1
 *
 * @package tec\events\custom_tables\v1\repository
 */

namespace TEC\Events\Custom_Tables\V1\Repository;

use Closure;
use TEC\Events\Custom_Tables\V1\Models\Event;
use WP_Post;
use Tribe__Events__Main as TEC;
use RuntimeException;

/**
 * Provides methods to alter how the repository performs some operations.
 *
 * @since   6.0.3.1
 *
 * @package tec\events\custom_tables\v1\repository
 */
class Events {
	/**
	 * A map of the update data per post ID.
	 *
	 * @since 6.0.0
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $update_data = [];

	/**
	 * Returns the callback that should be used to update Events in the context of
	 * the Repository.
	 *
	 * @since 6.0.0
	 *
	 * @return Closure The callback that should be used to upsert the Event data in the custom tables.
	 */
	public function update_callback( callable $repository_callback, array $postarr = [] ): Closure {
		// Run the original callback, then ours.
		return function ( ...$args ) use ( $repository_callback, $postarr ) {
			$post_id = $repository_callback( ...$args );

			if ( ! $post_id ) {
				return $post_id;
			}

			return $this->update( $post_id, $postarr );
		};
	}

	/**
	 * Creates the database values for the Event.
	 *
	 * @since 6.0.0
	 *
	 * @param int|WP_Post         $event_id The Event post ID, or a reference to the Event post object.
	 * @param array<string,mixed> $data     The data, all of it, used to upsert the Event.
	 *
	 * @return int|null Either the updated Event post ID, or `null` if the Event could not be created.
	 */
	public function update( $event_id, array $data ): ?int {
		$event_post = get_post( $event_id );

		if ( ! ( $event_post instanceof WP_Post && TEC::POSTTYPE === $event_post->post_type ) ) {
			// Ok, this is weird, but let's play safe.
			return null;
		}

		try {
			$this->update_data[ $event_id ] = $data;
			$event = $this->upsert_event( $event_id );
			$this->save_occurrences( $event );
		} catch ( \Exception $e ) {
			do_action( 'tribe_log', 'error', $e->getMessage(), [
				'source'  => __CLASS__,
				'post_id' => $event_id,
			] );

			return null;
		}

		return $event->post_id;
	}

	/**
	 * Upserts the Event data in the Events custom table.
	 *
	 * @param int                 $post_id            The Event post ID.
	 *
	 * @return Event A reference to the Event model instance.
	 *
	 * @throws RuntimeException On failure.
	 */
	protected function upsert_event( $post_id ) {
		$data = Event::data_from_post( $post_id );

		if ( Event::upsert( [ 'post_id' ], $data ) === false ) {
			throw new RuntimeException( 'Failed to upsert Event data in repository.' );
		}

		/** @var Event $event */
		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			throw new RuntimeException( 'Failed to fetch Event model in repository.' );
		}

		return $event;
	}

	/**
	 * Upserts the Event data in the Occurrences custom table.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event A reference to the Event model.
	 *
	 * @return Event A reference to the Event model.
	 *
	 * @throws RuntimeException On failure.
	 */
	protected function save_occurrences( Event $event ) {
		try {
			$event->occurrences()->save_occurrences();
		} catch ( \Exception $e ) {
			throw new RuntimeException( 'Failed to insert Occurrences data in repository.' );
		}

		return $event;
	}
}
