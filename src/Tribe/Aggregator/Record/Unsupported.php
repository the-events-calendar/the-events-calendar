<?php
/**
 * Models a record for a no longer, or not still, supported origin.
 *
 * Passing around an instance of an unsupported origin record should not break the code.
 *
 * @since 4.6.25
 */

/**
 * Class Tribe__Events__Aggregator__Record__Unsupported
 *
 * @since 4.6.25
 */
class Tribe__Events__Aggregator__Record__Unsupported extends Tribe__Events__Aggregator__Record__Abstract {
	/**
	 * Tribe__Events__Aggregator__Record__Unsupported constructor.
	 *
	 * Overrides the base method to play along nicely for the request context
	 * that builds this post and then remove it, if clean is allowed, on `shutdown`.
	 *
	 * @param int|WP_Post|null $post The record post or post ID.
	 */
	public function __construct( $post = null ) {
		parent::__construct( $post );

		/**
		 * Whether unsupported origin records should be removed or not.
		 *
		 * If set to `true` then the post will be deleted on shutdown.
		 *
		 * @since 4.6.25
		 *
		 * @param bool $should_delete Whether the unsupported post should be deleted or not; defaults
		 *                            to `true`.
		 * @param self $this          This record object.
		 * @param WP_Post This record post object.
		 */
		$should_delete = apply_filters( 'tribe_aggregator_clean_unsupported', true, $this, $post );

		if ( $should_delete ) {
			/*
			 * Let's delay the deletion to avoid client code from relying on
			 * a deleted post for this request.
			 */
			add_action( 'shutdown', array( $this, 'delete_post' ) );
		}
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @since 4.6.25
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Unsupported', 'the-events-calendar' );
	}

	/**
	 * Overrides the base method short-circuiting the check for the
	 * schedule time to return false.
	 *
	 * @since 4.6.25
	 *
	 * @return bool An indication that it's never time for an unsupported record to run.
	 */
	public function is_schedule_time() {
		// It's never time for an unsupported record to run.
		return false;
	}

	/**
	 * Returns the unsupported record hash.
	 *
	 * The hash is usually built from the record meta; in the case
	 * of an unsupported record that's skipped and a default string
	 * is returned. Since the hash is usually compared to strings built
	 * the same way the returned fixed hash will never match.
	 *
	 * @since 4.6.25
	 *
	 * @return string The record fixed hash.
	 */
	public function get_data_hash() {
		return 'unsupported';
	}

	/**
	 * Deletes the base post for this record.
	 *
	 * @since 4.6.25
	 */
	public function delete_post() {
		$this->delete();
	}
}
