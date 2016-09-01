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

	protected $content_type;
	protected $content_type_plural;
	protected $content_type_object;
	protected $content_post_type;

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

		$license_info = get_option( 'external_updates-event-aggregator' );
		if ( isset( $license_info->update->api_expired ) && $license_info->update->api_expired ) {
			tribe_notice( 'tribe-expired-aggregator-license', array( $this, 'render_notice_expired_aggregator_license' ), 'type=warning' );
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
		if ( ! empty( $_POST['ea-facebook-credentials'] ) ) {
			return $this->handle_facebook_credentials();
		}

		if ( empty( $_POST['aggregator']['action'] ) || 'new' !== $_POST['aggregator']['action'] ) {
			return;
		}

		$submission = parent::handle_submit();

		if ( empty( $submission['record'] ) || empty( $submission['post_data'] ) || empty( $submission['meta'] ) ) {
			return;
		}

		$record    = $submission['record'];
		$post_data = $submission['post_data'];
		$meta      = $submission['meta'];

		if ( ! empty( $post_data['import_id'] ) ) {
			$this->handle_import_finalize( $post_data );
			return;
		}

		// Prevents Accidents
		if ( 'manual' === $meta['type'] ) {
			$meta['frequency'] = null;
		}

		$post = $record->create( $meta['type'], array(), $meta );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$result = $record->queue_import();

		return $result;
	}

	public function handle_facebook_credentials() {
		/**
		 * @todo  include a way to handle errors on the Send back URL
		 */

		if ( empty( $_POST['aggregator'] ) ) {
			return false;
		}
		$data = (object) $_POST['aggregator'];
		$api = Tribe__Events__Aggregator__Service::instance()->api();

		$response = Tribe__Events__Aggregator__Service::instance()->get_facebook_token();
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( empty( $response->data ) ) {
			return false;
		}

		if ( empty( $response->data->expires ) ||  empty( $response->data->token ) || empty( $response->data->scopes ) ) {
			return false;
		}

		$expires = absint( trim( preg_replace( '/[^0-9]/', '', $response->data->expires ) ) );
		$expires += time();
		tribe_update_option( 'fb_token', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $response->data->token ) ) );
		tribe_update_option( 'fb_token_expires', $expires );
		tribe_update_option( 'fb_token_scopes', trim( preg_replace( '/[^a-zA-Z0-9\,_-]/', '', $response->data->scopes ) ) );

		if ( 'new' === $data->type ) {
			$url = Tribe__Events__Aggregator__Page::instance()->get_url( array( 'tab' => $this->get_slug(), 'ea-auth' => 'facebook' ) );
		} elseif ( 'settings' === $data->type ) {
			$url = Tribe__Settings::instance()->get_url( array( 'tab' => 'addons', 'ea-auth' => 'facebook' ) );
		}

		wp_redirect( $url );
		exit;
	}

	public function handle_import_finalize( $data ) {
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $data['import_id'] );
		$this->messages = array(
			'error',
			'success',
			'warning',
		);

		$record->update_meta( 'post_status', empty( $data['post_status'] ) ? 'draft' : $data['post_status'] );
		$record->update_meta( 'category', empty( $data['category'] ) ? null : $data['category'] );
		$record->update_meta( 'ids_to_import', empty( $data['selected_rows'] ) ? 'all' : json_decode( stripslashes( $data['selected_rows'] ) ) );

		// if we get here, we're good! Set the status to pending
		$record->set_status_as_pending();

		$record->finalize();

		if ( 'schedule' === $record->meta['type'] ) {
			$this->messages['success'][] = __( '1 import was scheduled.', 'the-events-calendar' );
			$create_schedule_result = $record->create_schedule_record();

			if ( is_wp_error( $create_schedule_result ) ) {
				$this->messages[ 'error' ][] = $create_schedule_result->get_error_message();

				tribe_notice( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

				$record->set_status_as_failed( $create_schedule_result );
				return $create_schedule_result;
			}
		}

		$record->update_meta( 'interactive', true );

		if ( 'csv' === $data['origin'] ) {
			$result = $record->process_posts( $data );
		} else {
			$result = $record->process_posts();
		}

		$this->messages = $this->get_result_messages( $record, $result );

		if (
			! empty( $this->messages['error'] )
			|| ! empty( $this->messages['success'] )
			|| ! empty( $this->messages['warning'] )
		) {
			tribe_notice( 'tribe-aggregator-import-complete', array( $this, 'render_notice_import_complete' ), 'type=success' );
		}
	}

	public function get_result_messages( $record, $result ) {
		$messages = array();
		$is_queued = ! empty( $result['remaining'] );

		$content_type = tribe_get_event_label_singular_lowercase();
		$content_type_plural = tribe_get_event_label_plural_lowercase();
		$content_post_type = Tribe__Events__Main::POSTTYPE;

		if ( 'csv' === $record->meta['origin'] && 'tribe_events' !== $record->meta['content_type'] ) {
			$content_type_object = get_post_type_object( $record->meta['content_type'] );
			$content_type = $content_type_object->labels->singular_name_lowercase;
			$content_type_plural = $content_type_object->labels->plural_name_lowercase;
			$content_post_type = $content_type_object->name;
		}

		if ( is_wp_error( $result ) ) {
			$messages[ 'error' ][] = $result->get_error_message();

			tribe_notice( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

			$record->set_status_as_failed( $result );
			return $result;
		}

		if ( ! $is_queued ) {
			if ( ! empty( $result['created'] ) ) {
				$content_label = 1 === $result['created'] ? $content_type : $content_type_plural;

				$messages['success'][] = sprintf(
					_n( '%1$d new %2$s was imported.', '%1$d new %2$s were imported.', $result['created'], 'the-events-calendar' ),
					$result['created'],
					$content_label
				);
			}

			if ( ! empty( $result['updated'] ) ) {
				$content_label = 1 === $result['updated'] ? $content_type : $content_type_plural;

				// @todo: include a part of sentence like: ", including %1$d %2$signored event%3$s.", <a href="/wp-admin/edit.php?post_status=tribe-ignored&post_type=tribe_events">, </a>
				$messages['success'][] = sprintf(
					_n( '%1$d existing %2$s was updated.', '%1$d existing %2$s were updated.', $result['updated'], 'the-events-calendar' ),
					$result['updated'],
					$content_label
				);
			}

			if ( ! empty( $result['skipped'] ) ) {
				$content_label = 1 === $result['skipped'] ? $content_type : $content_type_plural;

				$messages['success'][] = sprintf(
					_n( '%1$d already-imported %2$s was skipped.', '%1$d already-imported %2$s were skipped.', $result['skipped'], 'the-events-calendar' ),
					$result['skipped'],
					$content_label
				);
			}

			if ( ! empty( $result['images'] ) ) {
				$messages['success'][] = sprintf(
					_n( '%1$d new image imported.', '%1$d new images imported.', $result['images'], 'the-events-calendar' ),
					$result['images']
				);
			}

			if ( $result && ! $messages ) {
				__( 'No events were imported or updated.', 'the-events-calendar' );
			}

			// append a URL to view all records for the given post type
			$url = admin_url( 'edit.php?post_type=' . $content_post_type );
			$link_text = sprintf( __( 'View all %s', 'the-events-calendar' ), $content_type_plural );
			$messages['success'][ count( $messages['success'] ) - 1 ] .= ' <a href="' . esc_url( $url ) . '" >' . esc_html( $link_text ) . '</a>';

			// if not CSV, pull counts for venues and organizers that were auto-created
			if ( 'csv' !== $record->meta['origin'] ) {
				if ( ! empty( $result['venues'] ) ) {
					$messages['success'][] = '<br/>' . sprintf(
						_n( '%1$d new venue imported.', '%1$d new venues imported.', $result['venues'], 'the-events-calendar' ),
						$result['venues']
					) .
					' <a href="' . admin_url( 'edit.php?post_type=tribe_venue' ) . '">' .
					__( 'View your event venues', 'the-events-calendar' ) .
					'</a>';
				}

				if ( ! empty( $result['organizers'] ) ) {
					$messages['success'][] = '<br/>' . sprintf(
						_n( '%1$d new organizer imported.', '%1$d new organizers imported.', $result['organizers'], 'the-events-calendar' ),
						$result['organizers']
					) .
					' <a href="' . admin_url( 'edit.php?post_type=tribe_organizer' ) . '">' .
					__( 'View your event organizers', 'the-events-calendar' ) .
					'</a>';
					;
				}
			}
		}

		if ( ! empty( $result['category'] ) ) {
			$messages['success'][] = '<br/>' . sprintf(
				_n( '%1$d new event category was created.', '%1$d new event categories were created.', $result['category'], 'the-events-calendar' ),
				$result['category']
			) .
			' <a href="' . admin_url( 'edit.php?post_type=tribe_organizer' ) . '">' .
			__( 'View your event categories', 'the-events-calendar' ) .
			'</a>';
			;
		}

		if (
			! empty( $messages['error'] )
			|| ! empty( $messages['success'] )
			|| ! empty( $messages['warning'] )
		) {
			if ( 'manual' == $record->type ) {
				array_unshift( $messages['success'], __( 'Import complete!', 'the-events-calendar' ) . '<br/>' );
			} else {
				array_unshift( $messages['success'], __( 'Your scheduled import was saved and the first import is complete!', 'the-events-calendar' ) . '<br/>' );

				$scheduled_time = strtotime( $record->post->post_modified ) + $record->frequency->interval;
				$scheduled_time_string = date( get_option( 'date_format' ), $scheduled_time ) .
					_x( ' at ', 'separator between date and time', 'the-events-calendar' ) .
					date( get_option( 'time_format' ), $scheduled_time );

				$messages['success'][] = '<br/>' .
					sprintf(
						__( 'The next import is scheduled for %1$s.', 'the-events-calendar' ),
						esc_html( $scheduled_time_string )
					) .
					' <a href="' . admin_url( 'edit.php?page=aggregator&post_type=tribe_events&tab=scheduled' ) . '">' .
					__( 'View your scheduled imports.', 'the-events-calendar' ) .
					'</a>';
			}
		}

		return $messages;
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

		if ( 'meetup' === $which ) {
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

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result );
		}

		// if we've received a source name, let's set that in the record as soon as possible
		if ( ! empty( $result->data->source_name ) ) {
			$record->update_meta( 'source_name', $result->data->source_name );

			if ( ! empty( $record->post->post_parent ) ) {
				$parent_record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record->post->post_parent );
				$parent_record->update_meta( 'source_name', $result->data->source_name );
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Renders the "Missing Aggregator License" notice
	 *
	 * @return string
	 */
	public function maybe_display_aggregator_upsell() {
		if ( defined( 'TRIBE_HIDE_UPSELL' ) ) {
			return;
		}

		$has_license_key = ! empty( Tribe__Events__Aggregator__Service::instance()->api()->key );
		$license_info = get_option( 'external_updates-event-aggregator' );

		if ( $has_license_key && empty( $license_info->update->api_invalid ) ) {
			return;
		}

		ob_start();
		?>
		<div class="notice inline notice-info tribe-dependent tribe-notice-tribe-missing-aggregator-license" data-ref="tribe-missing-aggregator-license" data-depends="#tribe-ea-field-origin" data-condition-empty>
			<p>
				<strong><?php esc_html_e( 'Upgrade to Event Aggregator to unlock access to multiple import sources.', 'the-events-calendar' ); ?></strong></p>
			<p>
				<?php echo sprintf(
						esc_html__( 'With Event Aggregator, you can import events from Facebook, iCalendar, Google, and Meetup in a jiffy. Head over to %1$sTheEventsCalendar.com%2$s to purchase instant access, including a year of premium support, updates, and upgrades.', 'the-events-calendar' ),
						'<a href="https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importpage&utm_medium=plugin-tec&utm_campaign=in-app">',
						'</a>'
					); ?>
			</p>
			<p>
				<a href="https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importpage&utm_medium=plugin-tec&utm_campaign=in-app" class="tribe-license-link button button-primary"><?php esc_html_e( 'Buy Event Aggregator Now', 'the-events-calendar' ); ?></a>
			</p>
		</div>
		<?php

		return ob_get_clean();
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
					'%1$sYour Event Aggregator license is expired.%2$s Renew your license in order to import events from Facebook, iCalendar, Google, or Meetup.',
					'the-events-calendar'
				),
				'<b>',
				'</b>'
			);
			?>
		</p>
		<p>
			<a href="https://theeventscalendar.com/license-keys/?utm_campaign=in-app&utm_source=renewlink&utm_medium=event-aggregator" class="tribe-license-link"><?php esc_html_e( 'Renew your Event Aggregator license', 'the-events-calendar' ); ?></a>
		</p>
		<?php

		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-expired-aggregator-license', $html );
	}

	/**
	 * Renders any of the "import complete" messages
	 */
	public function render_notice_import_complete() {
		$html = '<p>' . implode( ' ', $this->messages['success'] ) . '</p>';
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
