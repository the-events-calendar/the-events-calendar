<?php

/**
 * Class Tribe__Events__Aggregator__Record__Void_Queue
 *
 * @since 4.6.22
 */
class Tribe__Events__Aggregator__Record__Void_Queue
	implements Tribe__Events__Aggregator__Record__Queue_Interface {

	/**
	 * The error string for the queue.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * The WP_Error instance used to build the void queue, if any.
	 *
	 * @var WP_Error
	 */
	protected $wp_error;

	/**
	 * Tribe__Events__Aggregator__Record__Void_Queue constructor.
	 *
	 * @param string|WP_Error $error The reason, in form of a string or
	 *                               `WP_Error` object, why this queue
	 *                               is void.
	 */
	public function __construct( $error ) {
		if ( $error instanceof WP_Error ) {
			$this->error    = $error->get_error_message();
			$this->wp_error = $error;

			return;
		}

		$this->error = $error;
	}

	/**
	 * {@inheritdoc}
	 */
	public function activity() {
		return new Tribe__Events__Aggregator__Record__Activity();
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() {
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_empty() {
		// A void queue is not empty, it's void.
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process( $batch_size = null ) {
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function progress_percentage() {
		// return a 0% progress percentage to make sure the queue processor will process it.
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_in_progress_flag() {
		// no-op
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear_in_progress_flag() {
		// no-op
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_in_progress() {
		// mark the queue as in progress to make the queue processor process it.
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_queue_type() {
		// not really important, still let's maintain coherence.
		return Tribe__Events__Main::POSTTYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_stuck() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function kill_queue() {
		if ( empty( $this->error ) ) {
			$this->error = __( 'Unable to process this import - a breakage or conflict may have resulted in the import halting.', 'the-events-calendar' );
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_errors() {
		return null !== $this->error;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_message() {
		return $this->error;
	}

	/**
	 * Returns the `WP_Error` instance used to build this void queue, if any.
	 *
	 * @since 4.6.22
	 *
	 * @return WP_Error|null The `WP_Error` used to build this void queue or `null`
	 *                       if no `WP_Error` object was used to build this void queue.
	 */
	public function get_wp_error() {
		return $this->wp_error;
	}
}
