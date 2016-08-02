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
			Tribe__Admin__Notices::instance()->register(
				'tribe-missing-aggregator-license',
				array( $this, 'render_notice_missing_aggregator_license' ),
				array(
					'type' => 'warning',
				)
			);

			return;
		}

		$license_info = get_option( 'external_updates-event-aggregator' );
		if ( isset( $license_info->update->api_expired ) && $license_info->update->api_expired ) {
			Tribe__Admin__Notices::instance()->register(
				'tribe-expired-aggregator-license',
				array( $this, 'render_notice_expired_aggregator_license' ),
				array(
					'type' => 'warning',
				)
			);
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

		// @todo: validate nonce

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

		if ( 'csv' === $data['origin'] ) {
			$result = $record->csv_insert_posts( $data );

			if ( is_wp_error( $result ) ) {
				$this->messages[ 'error' ][] = $result->get_error_message();

				Tribe__Admin__Notices::instance()->register(
					'tribe-aggregator-import-failed',
					array( $this, 'render_notice_import_failed' ),
					array(
						'type' => 'error',
					)
				);
				return $result;
			}

			if ( isset( $result['updated'] ) ) {
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

				if ( ! $this->messages ) {
					$this->messages['success'][] = __( '0 events have been added.', 'the-events-calendar' );
				}

				Tribe__Admin__Notices::instance()->register(
					'tribe-aggregator-import-complete',
					array( $this, 'render_notice_import_complete' ),
					array(
						'type' => 'success',
					)
				);

			}
			return $result;
		}

		// @TODO do somethign with the events
	}

	public function ajax_save_credentials() {
		if ( empty( $_GET['which'] ) ) {
			$data = array(
				'message' => __( 'Invalid credential save request', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tribe-save-credentials' ) ) {
			$data = array(
				'message' => __( 'Invalid credential save nonce', 'the-events-calendar' ),
			);

			wp_send_json_error( $data );
		}

		if ( 'facebook' === $_GET['which'] ) {
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
