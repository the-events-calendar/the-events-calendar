<?php
/**
 * Models an event (the post type) in the context of the Block Editor.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Editory\Objects
 */

namespace Tribe\Events\Editor\Objects;

use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;

/**
 * Class Event
 *
 * @since   TBD
 *
 * @package Tribe\Events\Editory\Objects
 */
class Event implements Editor_Object_Interface {
	/**
	 * The event data in the format required by the block editor.
	 *
	 * @since TBD
	 *
	 * @var array<string,mixed>
	 */
	protected $data;
	/**
	 * The post object to model the data on.
	 *
	 * @since TBD
	 *
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * Event constructor.
	 *
	 * @since TBD
	 *
	 * @param int|\WP_Post|null $event The event post ID or object, or `null` to use the global `post` object.
	 */
	public function __construct( $event = null ) {
		$event_candidate = $event ?: \tribe_get_request_var( 'post', false );
		$this->post      = \get_post( $event_candidate );
	}

	/**
	 * {@inheritDoc}
	 */
	public function data( $key = null, $default = null ) {
		if ( $this->data === null ) {
			$this->data = [
				'is_new_post' => true,
			];

			if ( $this->post instanceof \WP_Post && TEC::POSTTYPE === $this->post->post_type ) {
				$meta = Arr::flatten( (array) \get_post_meta( $this->post->ID ) );

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
