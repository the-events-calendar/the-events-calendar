<?php
/**
 * Facilitates "realtime" processing of a recurring event queue while the user
 * remains within the event editor by means of an ajax update loop.
 */
class Tribe__Events__Pro__Recurrence__Queue_Realtime {
	/** @var Tribe__Events__Pro__Recurrence__Queue */
	protected $queue;

	/** @var int */
	protected $event_id;


	public function __construct() {
		add_action( 'admin_head-post.php', array( $this, 'post_editor' ) );
		add_action( 'wp_ajax_tribe_events_pro_recurrence_realtime_update', array( $this, 'ajax' ) );
	}

	public function post_editor() {
		global $post;

		$is_an_event = class_exists( 'Tribe__Events__Main' ) && Tribe__Events__Main::POSTTYPE === $post->post_type;
		$is_a_parent = 0 == $post->post_parent;

		if ( ! $is_an_event || ! $is_a_parent ) {
			return;
		}

		$this->event_id = $post->ID;
		$this->queue    = new Tribe__Events__Pro__Recurrence__Queue( $this->event_id );

		if ( $this->queue->is_empty() ) {
			return;
		}

		$this->init_update_loop();
	}

	protected function init_update_loop() {
		$this->update_loop_vars();
		add_action( 'admin_notices', array( $this, 'add_notice' ) );
	}

	/**
	 * Adds additional data to the TribeEventsProAdmin object (available to our JS).
	 */
	public function update_loop_vars() {
		$percentage = $this->queue->progress_percentage();

		$data = array(
			'eventID'      => $this->event_id,
			'check'        => wp_create_nonce( 'generate_recurring_instances_' . $this->event_id . get_current_user_id() ),
			'completeMsg'  => __( 'Completed!', 'tribe-events-pro' ),
			'progress'     => $percentage,
			'progressText' => sprintf( __( '%d%% complete', 'tribe-events-pro' ), $percentage ),
		);

		wp_localize_script( Tribe__Events__Main::POSTTYPE.'-premium-admin', 'TribeEventsProRecurrenceUpdate', $data );
	}

	public function add_notice() {
		$update = $this->user_update();
		echo '<div class="tribe-events-recurring-update-msg updated updating">' . $update . '</div>';
	}

	public function user_update() {
		$notice    = __( 'Recurring event data is still being generated for this event. Don&#146;t worry, you can safely navigate away &ndash; the process will resume in a bit in the background.', 'tribe-events-pro' );
		$percent   = (int) $this->queue->progress_percentage();
		$spinner   = '<img src="' . get_admin_url( null, '/images/spinner.gif' ) . '">';
		$indicator = '<div> <div class="progress" title="' . sprintf( __( '%d%% complete', 'tribe-events-pro' ), $percent ) . '"> <div class="bar"></div> </div>' . $spinner . '</div>';

		return "<p> $notice </p> $indicator";
	}

	public function ajax() {
		$event_id = (int) $_POST['event'];

		// Nonce check
		if ( ! wp_verify_nonce( $_POST['check'], 'generate_recurring_instances_' . $event_id . get_current_user_id() ) ) {
			exit( json_encode( array(
				'html'     => __( 'Unable to continue processing recurring event data. Please reload this page to continue/try again.', 'tribe-events-pro' ),
				'progress' => false,
				'continue' => false,
				'complete' => false,
			) ) );
		}

		// Load the queue
		$queue = new Tribe__Events__Pro__Recurrence__Queue( $event_id );

		if ( ! $queue->is_empty() ) {
			Tribe__Events__Pro__Main::instance()->queue_processor->process_batch( $event_id );
		}

		$done       = $queue->is_empty();
		$percentage = $queue->progress_percentage();

		exit( json_encode( array(
			'html'         => false,
			'progress'     => $percentage,
			'progressText' => sprintf( __( '%d%% complete', 'tribe-events-pro' ), $percentage ),
			'continue'     => ! $done,
			'complete'     => $done,
		) ) );
	}
}
