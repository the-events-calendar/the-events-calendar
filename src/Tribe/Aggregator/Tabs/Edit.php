<?php

class Tribe__Events__Aggregator__Tabs__Edit extends Tribe__Events__Aggregator__Tabs__Abstract {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	public $priority = 10;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		// Setup Abstract hooks
		parent::__construct();

		add_action( 'wp_ajax_tribe_aggregator_preview_import', array( $this, 'ajax_preview_import' ) );

		add_action( 'tribe_aggregator_page_request', array( $this, 'handle_submit' ) );
	}

	public function enqueue_media() {
		if ( ! $this->is_active() ) {
			return;
		}

		wp_enqueue_media();
	}

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'edit';
	}

	public function get_label() {
		return esc_html__( 'Edit Import', 'the-events-calendar' );
	}

	public function handle_submit() {
		$this->messages = array(
			'error',
			'success',
			'warning',
		);

		if ( empty( $_POST['aggregator']['action'] ) || 'edit' !== $_POST['aggregator']['action'] ) {
			return;
		}

		$submission = parent::handle_submit();

		if ( empty( $submission['record'] ) || empty( $submission['post_data'] ) || empty( $submission['meta'] ) ) {
			return;
		}

		$record    = $submission['record'];
		$post_data = $submission['post_data'];
		$meta      = $submission['meta'];

		if ( ! empty( $post_data['post_id'] ) ) {
			$this->finalize_schedule_edit( $record, $post_data, $meta );
			return;
		} else {
			$post = $record->create( $meta['type'], array(), $meta );

			if ( is_wp_error( $post ) ) {
				return $post;
			}

			$result = $record->queue_import();
		}

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		return $result;
	}

	/**
	 * Finalizes the saving of a scheduled import
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Record object
	 * @param array $post_data Massaged POSTed data
	 * @param array $meta Meta to be saved to the schedule
	 */
	public function finalize_schedule_edit( $record, $post_data, $meta ) {
		$this->messages = array(
			'error' => array(),
			'success' => array(),
			'warning' => array(),
		);

		$meta[ 'post_status' ] = empty( $post_data['post_status'] ) ? 'draft' : $post_data['post_status'];
		$meta[ 'category' ] = empty( $post_data['category'] ) ? null : $post_data['category'];
		$result = $record->save( $post_data['post_id'], array(), $meta );

		if ( is_wp_error( $result ) ) {
			$this->messages['error'][] = $result->get_error_message();

			ob_start();
			?>
			<p>
				<?php echo implode( ' ', $this->messages['error'] ); ?>
			</p>
			<?php

			$html = ob_get_clean();

			tribe_notice( 'tribe-aggregator-schedule-edit-failed', $html, 'type=error' );
			return $result;
		}

		$this->messages['success'][] = esc_html__( 'Scheduled import was successfully updated.' );

		ob_start();
		?>
		<p>
			<?php echo implode( ' ', $this->messages['success'] ); ?>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Tribe__Events__Main::POSTTYPE . '&page=aggregator&tab=scheduled' ) ); ?>" ><?php esc_html_e( 'View All Scheduled Imports', 'the-events-calendar' ); ?></a>
		</p>
		<?php

		$html = ob_get_clean();

		$this->messages['success'][] = __( 'Your Scheduled Import has been updated!', 'the-events-calendar' );
		tribe_notice( 'tribe-aggregator-schedule-edit-complete', $html, 'type=success' );

		return $result;
	}


	/**
	 * Handles the previewing of a scheduled import edit
	 */
	public function ajax_preview_import() {
		$result = $this->handle_submit();

		if ( is_wp_error( $result ) ) {
			$result = (object) array(
				'message_code' => $result->get_error_code(),
				'message' => $result->get_error_message(),
			);
			wp_send_json_error( $result );
		}

		wp_send_json_success( $result );
	}
}
