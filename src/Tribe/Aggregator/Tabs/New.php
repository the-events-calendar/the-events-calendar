<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs__New extends Tribe__Events__Aggregator__Tabs__Abstract {
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

		// Configure this tab ajax calls
		add_action( 'wp_ajax_tribe_aggregator_dropdown_origins', array( $this, 'ajax_origins' ) );
		add_action( 'wp_ajax_tribe_aggregator_save_credentials', array( $this, 'ajax_save_credentials' ) );
		add_action( 'wp_ajax_tribe_aggregator_create_import', array( $this, 'ajax_create_import' ) );
		add_action( 'wp_ajax_tribe_aggregator_fetch_import', array( $this, 'ajax_fetch_import' ) );

		// We need to enqueue Media scripts like this
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );

		add_action( 'tribe_aggregator_page_request', array( $this, 'handle_submit' ) );

		// hooked at priority 9 to ensure that notices are injected before notices get hooked in Tribe__Admin__Notices
		add_action( 'current_screen', array( $this, 'maybe_display_notices' ), 9 );
	}

	public function maybe_display_notices() {
		if ( ! $this->is_active() ) {
			return;
		}

		$has_license_key = ! empty( Tribe__Events__Aggregator__Service::instance()->api()->key );
		$license_info = get_option( 'external_updates-event-aggregator' );

		if ( ! $has_license_key || ( isset( $license_info->update->api_invalid ) && $license_info->update->api_invalid ) ) {
			Tribe__Admin__Notices::instance()->register( 'tribe-missing-aggregator-license', array( $this, 'render_notice_missing_aggregator_license' ), 'type=warning' );

			return;
		}

		$license_info = get_option( 'external_updates-event-aggregator' );
		if ( isset( $license_info->update->api_expired ) && $license_info->update->api_expired ) {
			Tribe__Admin__Notices::instance()->register( 'tribe-expired-aggregator-license', array( $this, 'render_notice_expired_aggregator_license' ), 'type=warning' );
		}
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
		return 'new';
	}

	public function get_label() {
		return esc_html__( 'New Import', 'the-events-calendar' );
	}

	public function handle_submit() {
		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! $this->is_active() ) {
			return;
		}

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['aggregator'] ) ) {
			return;
		}

		// validate nonce
		if ( empty( $_POST['tribe_aggregator_nonce'] ) || ! wp_verify_nonce( $_POST['tribe_aggregator_nonce'], 'tribe-aggregator-save-import' ) ) {
			$data = array(
				'message' => __( 'There was a problem processing your import. Please try again.', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		$post_data = $_POST['aggregator'];

		if ( empty( $post_data['origin'] ) || empty( $post_data[ $post_data['origin'] ] ) ) {
			return;
		}

		$data = $post_data[ $post_data['origin'] ];

		if ( ! empty( $post_data['import_id'] ) ) {
			$this->handle_import_finalize( $post_data );
			return;
		}

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $post_data['origin'] );

		$meta = array(
			'origin'       => $post_data['origin'],
			'type'         => empty( $data['import_type'] )      ? 'manual' : $data['import_type'],
			'frequency'    => empty( $data['import_frequency'] ) ? null     : $data['import_frequency'],
			'file'         => empty( $data['file'] )             ? null     : $data['file'],
			'keywords'     => empty( $data['keywords'] )         ? null     : $data['keywords'],
			'location'     => empty( $data['location'] )         ? null     : $data['location'],
			'start'        => empty( $data['start'] )            ? null     : $data['start'],
			'radius'       => empty( $data['radius'] )           ? null     : $data['radius'],
			'source'       => empty( $data['source'] )           ? null     : $data['source'],
			'content_type' => empty( $data['content_type'] )     ? null     : $data['content_type'],
		);

		$post = $record->create( $meta['type'], array(), $meta );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$result = $record->queue_import();

		return $result;
	}

	public function handle_import_finalize( $data ) {
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $data['import_id'] );
		$this->messages = array(
			'error',
			'success',
			'warning',
		);

		$record->update_meta( 'post_status', empty( $data['post_status'] ) ? 'draft' : $data['post_status'] );
		$record->update_meta( 'category', empty( $data['category'] ) ? null : $data['post_status'] );
		$record->update_meta( 'ids_to_import', empty( $data['selected_rows'] ) ? 'all' : stripslashes( $data['selected_rows'] ) );
		$record->finalize();

		if ( 'schedule' === $record->meta['type'] ) {
			$this->messages['success'][] = __( '1 schedule import successfully added.', 'the-events-calendar' );
			$create_schedule_result = $record->create_schedule_record();

			if ( is_wp_error( $create_schedule_result ) ) {
				$this->messages[ 'error' ][] = $create_schedule_result->get_error_message();

				Tribe__Admin__Notices::instance()->register( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

				$record->set_status_as_failed( $create_schedule_result );
				return $create_schedule_result;
			}
		}

		$result = $record->insert_posts();

		if ( is_wp_error( $result ) ) {
			$this->messages[ 'error' ][] = $result->get_error_message();

			Tribe__Admin__Notices::instance()->register( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

			$record->set_status_as_failed( $result );
			return $result;
		}

		if ( ! empty( $result['updated'] ) ) {
			$this->messages['success'][] = sprintf(
				_n( '%1$d event has been updated.', '%1$d events have been updated.', $result['updated'], 'the-events-calendar' ),
				$result['updated']
			);
		}

		if ( ! empty( $result['created'] ) ) {
			$this->messages['success'][] = sprintf(
				_n( '%1$d event has been successfully added.', '%1$d events have been successfully added.', $result['created'], 'the-events-calendar' ),
				$result['created']
			);
		}

		if ( ! empty( $result['skipped'] ) ) {
			$this->messages['success'][] = sprintf(
				_n( '%1$d event has been skipped.', '%1$d events have been skipped.', $result['skipped'], 'the-events-calendar' ),
				$result['skipped']
			);
		}

		if ( $result && ! $this->messages ) {
			$this->messages['success'][] = __( '0 events have been added.', 'the-events-calendar' );
		}

		if (
			! empty( $this->messages['error'] )
			|| ! empty( $this->messages['success'] )
			|| ! empty( $this->messages['warning'] )
		) {
			Tribe__Admin__Notices::instance()->register( 'tribe-aggregator-import-complete', array( $this, 'render_notice_import_complete' ), 'type=success' );
		}
	}

	public function ajax_save_credentials() {
		if ( empty( $_POST['tribe_credentials_which'] ) ) {
			$data = array(
				'message' => __( 'Invalid credential save request', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		$which = $_POST['tribe_credentials_which'];

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], "tribe-save-{$which}-credentials" ) ) {
			$data = array(
				'message' => __( 'Invalid credential save nonce', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		if ( 'facebook' === $which ) {
			if ( empty( $_POST['fb_api_key'] ) || empty( $_POST['fb_api_secret'] ) ) {
				$data = array(
					'message' => __( 'The Facebook API key and API secret are both required.', 'the-events-calendar' ),
				);

				wp_send_json_error( $data );
			}

			tribe_update_option( 'fb_api_key', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['fb_api_key'] ) ) );
			tribe_update_option( 'fb_api_secret', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['fb_api_secret'] ) ) );

			$data = array(
				'message' => __( 'Credentials have been saved', 'the-events-calendar' ),
			);

			wp_send_json_success( $data );
		} elseif ( 'meetup' === $which ) {
			if ( empty( $_POST['meetup_api_key'] ) ) {
				$data = array(
					'message' => __( 'The Meetup API key is required.', 'the-events-calendar' ),
				);

				wp_send_json_error( $data );
			}

			tribe_update_option( 'meetup_api_key', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['meetup_api_key'] ) ) );

			$data = array(
				'message' => __( 'Credentials have been saved', 'the-events-calendar' ),
			);

			wp_send_json_success( $data );
		}

		$data = array(
			'message' => __( 'Unable to save credentials', 'the-events-calendar' ),
		);

		wp_send_json_error( $data );
	}

	public function ajax_create_import() {
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

	public function ajax_fetch_import() {
		$import_id = $_GET['import_id'];

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $import_id );

		if ( is_wp_error( $record ) ) {
			wp_send_json_error( $record );
		}

		$result = $record->get_import_data();

		wp_send_json_success( $result );
	}

	/**
	 * Renders the "Missing Aggregator License" notice
	 *
	 * @return string
	 */
	public function render_notice_missing_aggregator_license() {
		ob_start();
		?>
		<p>
			<?php
			esc_html_e(
				"
					Need to import events from other sources? Buy an Event Aggregator license and you'll
					be able to import events from Facebook, iCalendar, Google, and Meetup.com! Import
					individual events or set up saved auto imports to fill your calendar regularly. Use
					filters to get just the events you want.
				",
				'the-events-calendar'
			);
			?>
		</p>
		<p>
			<a href="" class="tribe-license-link"><?php esc_html_e( 'Buy your Event Aggregator license today!', 'the-events-calendar' ); ?></a>
		</p>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-missing-aggregator-license', $html );
	}

	/**
	 * Renders the "Expired Aggregator License" notice
	 *
	 * @return string
	 */
	public function render_notice_expired_aggregator_license() {
		ob_start();
		?>
		<p>
			<?php
			echo sprintf(
				esc_html__(
					'
						%1$sYour Event Aggregator license is expired.%2$s Renew your license in order to import
						events from Facebook, iCalendar, Google, or Meetup.com.
					',
					'the-events-calendar'
				),
				'<b>',
				'</b>'
			);
			?>
		</p>
		<p>
			<a href="" class="tribe-license-link"><?php esc_html_e( 'Renew your Event Aggregator license', 'the-events-calendar' ); ?></a>
		</p>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-expired-aggregator-license', $html );
	}

	/**
	 * Renders any of the "import complete" messages
	 */
	public function render_notice_import_complete() {
		ob_start();
		?>
		<p>
			<?php echo implode( ' ', $this->messages['success'] ); ?>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Tribe__Events__Main::POSTTYPE ) ); ?>" ><?php esc_html_e( 'View All Events', 'the-events-calendar' ); ?></a>
		</p>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-import-complete', $html );
	}

	/**
	 * Renders failed import messages
	 */
	public function render_notice_import_failed() {
		ob_start();
		?>
		<p>
			<?php echo implode( ' ', $this->messages['error'] ); ?>
		</p>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-import-failed', $html );
	}
}
