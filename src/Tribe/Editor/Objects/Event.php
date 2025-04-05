<?php
/**
 * Models an event (the post type) in the context of the Block Editor.
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Editor\Objects
 */

namespace Tribe\Events\Editor\Objects;

use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;

/**
 * Class Event
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Editor\Objects
 */
class Event implements Editor_Object_Interface {
	/**
	 * The event data in the format required by the block editor.
	 *
	 * @since 5.1.0
	 *
	 * @var array<string,mixed>
	 */
	protected $data;

	/**
	 * The post object to model the data on.
	 *
	 * @since 5.1.0
	 *
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * Event constructor.
	 *
	 * @since 5.1.0
	 *
	 * @param int|\WP_Post|null $event The event post ID or object, or `null` to use the global `post` object.
	 */
	public function __construct( $event = null ) {
		$event_candidate = null !== $event ? $event : \tribe_get_request_var( 'post', false );
		$this->post      = $event_candidate ? \get_post( $event_candidate ) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function data( $key = null, $default = null ) {
		if ( null === $this->data ) {
			$this->data = [
				'is_new_post' => true,
			];

			$start_date = tribe_get_request_var( 'tribe-start-date' );

			/**
			 * Grabs the tribe-start-date query param from the url if it exists
			 * and if its a valid date then adds it to the global window object.
			 */
			if ( $start_date ) {
				$start_date = Dates::build_date_object( $start_date, null, false );

				if ( $start_date ) {
					$this->data['tribe_start_date'] = $start_date->format( Dates::DBDATEFORMAT );
				}
			}

			if ( $this->post instanceof \WP_Post && TEC::POSTTYPE === $this->post->post_type ) {
				$post_id = $this->post->ID;
				$meta = Arr::flatten( (array) \get_post_meta( $post_id ) );

				/**
				 * Filters the meta data that will be localized for an Event object in the context of the Blocks editor.
				 *
				 * @since 6.0.0
				 *
				 * @param array<string,mixed> $meta    The meta data to be localized.
				 * @param int                 $post_id The post ID of the Event.
				 */
				$meta = apply_filters( 'tec_events_custom_tables_v1_blocks_editor_event_meta', $meta, $post_id );

				$meta_fix_map = [
					'_EventAllDay'      => 'tribe_is_truthy',
					'_EventOrganizerID' => [ Arr::class, 'list_to_array' ],
					'_EventCost'        => static function () use ( $post_id ) {
						return tribe_get_cost( $post_id );
					},
					'_EventVenueID'     => [ Arr::class, 'list_to_array' ],
					'_EventShowMap'     => 'tribe_is_truthy',
					'_EventShowMapLink' => 'tribe_is_truthy',
				];

				foreach ( $meta_fix_map as $meta_key => $fix ) {
					if ( isset( $meta[ $meta_key ] ) ) {
						$meta[ $meta_key ] = $fix( $meta[ $meta_key ] );
					}
				}

				$this->data['is_new_post'] = false;
				$this->data['meta']        = $meta;
			}
		}

		if ( null !== $key ) {
			return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
		}

		return $this->data;
	}
}
