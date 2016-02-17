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
	/**
	 * @var Tribe__Events__Ajax__Operations
	 */
	private $ajax_operations;
	/**
	 * @var Tribe__Events__Pro__Recurrence__Queue_Processor
	 */
	private $queue_processor;

	/**
	 * The Queue_Realtime constructor method.
	 *
	 * @param Tribe__Events__Pro__Recurrence__Queue|null           $queue An optional Recurrence Queue instance.
	 * @param Tribe__Events__Ajax__Operations|null                 $ajax_operations An optional Ajax Operations instance.
	 * @param Tribe__Events__Pro__Recurrence__Queue_Processor|null $queue_processor An optional Queue_Processor instance.
	 */
	public function __construct( Tribe__Events__Pro__Recurrence__Queue $queue = null, Tribe__Events__Ajax__Operations $ajax_operations = null, Tribe__Events__Pro__Recurrence__Queue_Processor $queue_processor = null ) {
		add_action( 'admin_head-post.php', array(
			$this,
			'post_editor',
		) );
		add_action( 'wp_ajax_tribe_events_pro_recurrence_realtime_update', array(
			$this,
			'ajax',
		) );
		$this->queue           = $queue;
		$this->ajax_operations = $ajax_operations ? $ajax_operations : new Tribe__Events__Ajax__Operations();
		$this->queue_processor = $queue_processor ? $queue_processor : Tribe__Events__Pro__Main::instance()->queue_processor;
	}

	public function post_editor() {
		global $post;

		$is_an_event = class_exists( 'Tribe__Events__Main' ) && Tribe__Events__Main::POSTTYPE === $post->post_type;
		$is_a_parent = 0 == $post->post_parent;

		if ( ! $is_an_event || ! $is_a_parent ) {
			return false;
		}

		$this->event_id = $post->ID;
		$this->queue    = $this->queue ? $this->queue : new Tribe__Events__Pro__Recurrence__Queue( $this->event_id );

		if ( $this->queue->is_empty() ) {
			return false;
		}

		return $this->init_update_loop();
	}

	protected function init_update_loop() {
		$this->update_loop_vars();
		add_action( 'admin_notices', array(
			$this,
			'add_notice',
		) );

		return true;
	}

	/**
	 * Adds additional data to the TribeEventsProAdmin object (available to our JS).
	 */
	public function update_loop_vars() {
		if ( ! tribe_is_event( $this->event_id ) ) {
			return false;
		}

		$percentage = $this->queue->progress_percentage();

		$progress = $this->sanitize_progress( $percentage );
		$data     = array(
			'eventID'      => $this->event_id,
			'check'        => $this->get_ajax_nonce(),
			'completeMsg'  => __( 'Completed!', 'tribe-events-pro' ),
			'progress'     => $progress,
			'progressText' => sprintf( __( '%d%% complete', 'tribe-events-pro' ), $progress ),
		);

		wp_localize_script( Tribe__Events__Main::POSTTYPE . '-premium-admin', 'TribeEventsProRecurrenceUpdate', $data );

		return $data;
	}

	public function add_notice() {
		if ( ! tribe_is_event( $this->event_id ) ) {
			return;
		}
		$update = $this->user_update();
		echo '<div class="tribe-events-recurring-update-msg updated updating">' . $update . '</div>';
	}

	public function user_update() {
		if ( ! tribe_is_event( $this->event_id ) ) {
			return;
		}
		$notice    = __( 'Recurring event data is still being generated for this event. Don&#146;t worry, you can safely navigate away &ndash; the process will resume in a bit in the background.', 'tribe-events-pro' );
		$percent   = $this->sanitize_progress( $this->queue->progress_percentage() );
		$spinner   = '<img src="' . get_admin_url( null, '/images/spinner.gif' ) . '">';
		$indicator = '<div> <div class="progress" title="' . sprintf( __( '%d%% complete', 'tribe-events-pro' ), $percent ) . '"> <div class="bar"></div> </div>' . $spinner . '</div>';

		return "<p> $notice </p> $indicator";
	}

	public function ajax() {
		$this->event_id = (int) $_POST['event'];

		// Nonce check
		$this->ajax_operations->verify_or_exit( $_POST['check'], $this->get_ajax_nonce_action(), $this->get_unable_to_continue_processing_data() );

		// Load the queue
		$queue = $this->queue ? $this->queue : new Tribe__Events__Pro__Recurrence__Queue( $this->event_id );

		if ( ! $queue->is_empty() ) {
			$this->queue_processor->process_batch( $this->event_id );
		}

		$done       = $queue->is_empty();
		$percentage = $queue->progress_percentage();

		$this->ajax_operations->exit_data( $this->get_progress_message_data( $percentage, $done ) );
	}

	/**
	 * @param $percentage
	 *
	 * @return int|string
	 */
	private function sanitize_progress( $percentage ) {
		if ( $percentage === true ) {
			return 100;
		}

		return is_numeric( $percentage ) ? intval( $percentage ) : 0;
	}

	/**
	 * @return string
	 */
	public function get_ajax_nonce() {
		return wp_create_nonce( $this->get_ajax_nonce_action() );
	}

	/**
	 * Generates the nonce action string on an event and user base.
	 *
	 * @param int|null $event_id An event post ID to override the instance defined one.
	 *
	 * @return string
	 */
	public function get_ajax_nonce_action( $event_id = null ) {
		$event_id = $event_id ? $event_id : $this->event_id;

		return 'generate_recurring_instances_' . $event_id . get_current_user_id();
	}

	/**
	 * @return mixed|string|void
	 */
	public function get_unable_to_continue_processing_data() {
		return json_encode( array(
			'html'     => __( 'Unable to continue processing recurring event data. Please reload this page to continue/try again.', 'tribe-events-pro' ),
			'progress' => false,
			'continue' => false,
			'complete' => false,
		) );
	}

	/**
	 * @param $percentage
	 * @param $done
	 *
	 * @return mixed|string|void
	 */
	public function get_progress_message_data( $percentage, $done ) {
		return json_encode( array(
			'html'         => false,
			'progress'     => $percentage,
			'progressText' => sprintf( __( '%d%% complete', 'tribe-events-pro' ), $percentage ),
			'continue'     => ! $done,
			'complete'     => $done,
		) );
	}
}
