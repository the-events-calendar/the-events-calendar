<?php

/**
 * Facilitates "realtime" processing of an import result insertion queue while the user
 * remains within the editor by means of an ajax update loop.
 */
class Tribe__Events__Aggregator__Record__Queue_Realtime {

	/** @var Tribe__Events__Aggregator__Record__Queue_Interface */
	protected $queue;

	/** @var int */
	protected $record_id;
	/**
	 * @var Tribe__Events__Ajax__Operations
	 */
	private $ajax_operations;

	/**
	 * @var Tribe__Events__Aggregator__Record__Queue_Processor
	 */
	private $queue_processor;

	/**
	 * The Queue_Realtime constructor method.
	 *
	 * @param Tribe__Events__Aggregator__Record__Queue_Interface|null           $queue An optional Queue instance.
	 * @param Tribe__Events__Ajax__Operations|null                    $ajax_operations An optional Ajax Operations instance.
	 * @param Tribe__Events__Aggregator__Record__Queue_Processor|null $queue_processor An optional Queue_Processor instance.
	 */
	public function __construct(
		Tribe__Events__Aggregator__Record__Queue_Interface $queue = null,
		Tribe__Events__Ajax__Operations $ajax_operations = null,
		Tribe__Events__Aggregator__Record__Queue_Processor $queue_processor = null
	) {
		tribe_notice( 'aggregator-update-msg', [ $this, 'render_update_message' ], 'type=warning&dismiss=0' );

		add_filter( 'heartbeat_received', [ $this, 'receive_heartbeat' ], 10, 2 );

		add_action( 'wp_ajax_tribe_aggregator_realtime_update', [ $this, 'ajax' ] );

		$this->queue           = $queue;
		$this->ajax_operations = $ajax_operations ? $ajax_operations : new Tribe__Events__Ajax__Operations;
		$this->queue_processor = $queue_processor ? $queue_processor : tribe( 'events-aggregator.main' )->queue_processor;
	}

	/**
	 * Adds additional data to the tribe_aggregator object (available to our JS).
	 */
	public function update_loop_vars() {
		$percentage = $this->queue->progress_percentage();

		$progress = $this->sanitize_progress( $percentage );
		$data = [
			'record_id'    => $this->record_id,
			'check'        => $this->get_ajax_nonce(),
			'completeMsg'  => __( 'Completed!', 'the-events-calendar' ),
			'progress'     => $progress,
			'progressText' => sprintf( __( '%d%% complete', 'the-events-calendar' ), $progress ),
		];

		wp_localize_script( 'tribe-ea-notice', 'tribe_aggregator_save', $data );

		return $data;
	}

	public function render_update_message() {
		if ( ! Tribe__Events__Aggregator__Page::instance()->aggregator_should_load_scripts() ) {
			return false;
		}

		/** @var Tribe__Events__Aggregator__Record__Queue_Processor $processor */
		$processor = tribe( 'events-aggregator.main' )->queue_processor;

		if ( ! $this->record_id = $processor->next_waiting_record( true ) ) {
			return false;
		}

		$this->queue = $this->queue
			? $this->queue
			: Tribe__Events__Aggregator__Record__Queue_Processor::build_queue( $this->record_id );

		if ( $this->queue->is_empty() ) {
			return false;
		}

		$this->update_loop_vars();

		ob_start();
		$percent   = $this->sanitize_progress( $this->queue->progress_percentage() );
		?>
		<div class="tribe-message">
			<p>
				<?php esc_html_e(
						'Your import is currently in progress. Don\'t worry, you can safely navigate away&ndash;the import will continue in the background.',
						'the-events-calendar'
				); ?>
			</p>
		</div>
		<ul class="tracker">
			<li class="tracked-item track-created"><strong><?php esc_html_e( 'Created:', 'the-events-calendar' ); ?></strong> <span class="value"></span></li>
			<li class="tracked-item track-updated"><strong><?php esc_html_e( 'Updated:', 'the-events-calendar' ); ?></strong> <span class="value"></span></li>
			<li class="tracked-item track-skipped"><strong><?php esc_html_e( 'Skipped:', 'the-events-calendar' ); ?></strong> <span class="value"></span></li>
		</ul>
		<div class="progress-container">
			<div class="progress" title="<?php echo esc_html( sprintf( __( '%d%% complete', 'the-events-calendar' ), $percent ) ); ?>">
				<div class="bar"></div>
			</div>
			<img src="<?php echo esc_url( get_admin_url( null, '/images/spinner.gif' ) ); ?>">
		</div>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'aggregator-update-msg', $html );
	}

	/**
	 * Action to reply every time a heart beat is executed to send the progress of EA if an EA record is present.
	 *
	 * @since 5.4.0 Change the method signature to be a little bit less aggressive with enforcing types.
	 *
	 * @param array<string, mixed> $response The current response object.
	 * @param array<string, mixed> $data     An array with the data from the client.
	 *
	 * @return array<string, mixed> An array used to construct the heart beat response.
	 */
	public function receive_heartbeat( $response, $data ) {
		if ( empty( $data['ea_record'] ) ) {
			return $response;
		}

		$this->record_id = absint( $data['ea_record'] );

		if ( 0 === $this->record_id ) {
			return $response;
		}

		$data = $this->get_queue_progress_data();

		if ( empty( $data ) ) {
			return $response;
		}

		$response['ea_progress'] = $data;
		return $response;
	}

	/**
	 * Handle queue ajax requests
	 */
	public function ajax() {
		$this->record_id = (int) tribe_get_request_var( 'record' );

		// Nonce check.
		$this->ajax_operations->verify_or_exit(
			tribe_get_request_var( 'check' ),
			$this->get_ajax_nonce_action(),
			$this->get_unable_to_continue_processing_data()
		);

		$data      = $this->get_queue_progress_data();
		$exit_data = empty( $data ) ? '' : wp_json_encode( $data );
		$this->ajax_operations->exit_data( $exit_data );
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
	public function get_ajax_nonce_action( $record_id = null ) {
		$record_id = $record_id ? $record_id : $this->record_id;

		return 'tribe_aggregator_insert_items_' . $record_id . get_current_user_id();
	}

	/**
	 * @return mixed|string|void
	 */
	public function get_unable_to_continue_processing_data() {
		return json_encode(
			[
				'html'     => __(
					'Unable to continue inserting data. Please reload this page to continue/try again.',
					'the-events-calendar'
				),
				'progress' => false,
				'continue' => false,
				'complete' => false,
			]
		);
	}

	/**
	 * Returns the progress message data.
	 *
	 * @param Tribe__Events__Aggregator__Record__Queue_Interface $queue
	 * @param int $percentage
	 * @param bool $done
	 *
	 * @return mixed|string|void
	 */
	public function get_progress_message_data( $queue, $percentage, $done ) {
		return wp_json_encode( $this->get_progress_data( $queue, $percentage, $done ) );
	}

	/**
	 * Get the data that is used to construct the current status of the EA progress bar.
	 *
	 * @since 5.3.0
	 *
	 * @return array<string, mixed> An array with the details of the progress bar.
	 */
	private function get_queue_progress_data() {
		if ( (int) $this->record_id <= 0 ) {
			return [];
		}

		// Load the queue.
		/** @var \Tribe__Events__Aggregator__Record__Queue_Interface $queue */
		$queue = $this->queue ? $this->queue : Tribe__Events__Aggregator__Record__Queue_Processor::build_queue( $this->record_id );
		// We always need to set up the Current Queue.
		$this->queue_processor->set_current_queue( $queue );

		// Only if it's not empty that we care about processing.
		if ( ! $queue->is_empty() ) {
			$this->queue_processor->process_batch( $this->record_id );
		}

		/**
		 * Include current queue to prevent progress bar from sticking on csv imports
		 *
		 * @var \Tribe__Events__Aggregator__Record__Queue_Interface $current_queue
		 */
		$current_queue = $this->queue_processor->current_queue;
		$done          = $current_queue->is_empty() && empty( $current_queue->is_fetching() );

		$percentage = $current_queue->progress_percentage();
		return $this->get_progress_data( $current_queue, $percentage, $done );
	}

	/**
	 * Get the current Queue status for EA to consume the status of the progress bar.
	 *
	 * @since 5.3.0
	 *
	 * @param Tribe__Events__Aggregator__Record__Queue_Interface $queue      The Queue being processed.
	 * @param int                                                $percentage The amount of the percentage.
	 * @param bool                                               $done       If the Import was completed or not.
	 *
	 * @return array<string, mixed> Get an array with the details of the current Queue.
	 */
	private function get_progress_data( $queue, $percentage, $done ) {
		$queue_type = $queue->get_queue_type();

		$is_event_queue = $queue_type === Tribe__Events__Main::POSTTYPE;
		$activity = $queue->activity();

		$error = $queue->has_errors();

		$data = [
			'html'          => false,
			'progress'      => $percentage,
			'progress_text' => sprintf( __( '%d%% complete', 'the-events-calendar' ), $percentage ),
			'continue'      => ! $done,
			'complete'      => $done,
			'error'         => $error,
			'counts'        => [
				'total'      => $activity->count( $queue_type ),
				'created'    => $activity->count( $queue_type, 'created' ),
				'updated'    => $activity->count( $queue_type, 'updated' ),
				'skipped'    => $activity->count( $queue_type, 'skipped' ),
				'category'   => $activity->count( 'category', 'created' ),
				'images'     => $activity->count( 'images', 'created' ),
				'venues'     => $is_event_queue ? $activity->count( 'venues', 'created' ) : 0,
				'organizers' => $is_event_queue ? $activity->count( 'organizer', 'created' ) : 0,
				'remaining'  => $queue->count(),
			],
		];

		$messages = Tribe__Events__Aggregator__Tabs__New::instance()->get_result_messages( $queue );

		if ( $error ) {
			$data['error_text'] = '<p>' . implode( ' ', $messages['error'] ) . '</p>';
		} elseif ( $done ) {
			$data['complete_text'] = '<p>' . implode( ' ', $messages['success'] ) . '</p>';
		}

		return $data;
	}
}
