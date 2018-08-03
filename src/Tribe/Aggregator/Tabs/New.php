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
	protected $messages;

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
		add_action( 'wp_ajax_tribe_aggregator_save_credentials', array( $this, 'ajax_save_credentials' ) );
		add_action( 'wp_ajax_tribe_aggregator_create_import', array( $this, 'ajax_create_import' ) );
		add_action( 'wp_ajax_tribe_aggregator_fetch_import', array( $this, 'ajax_fetch_import' ) );

		// We need to enqueue Media scripts like this
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );

		add_action( 'tribe_aggregator_page_request', array( $this, 'handle_submit' ) );
		add_action( 'tribe_aggregator_page_request', array( $this, 'handle_facebook_credentials' ) );

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
		if ( empty( $_POST['aggregator']['action'] ) || 'new' !== $_POST['aggregator']['action'] ) {
			return;
		}

		$submission = parent::handle_submit();

		if ( empty( $submission['record'] ) || empty( $submission['post_data'] ) || empty( $submission['meta'] ) ) {
			return;
		}

		/** @var Tribe__Events__Aggregator__Record__Abstract $record */
		$record    = $submission['record'];
		$post_data = $submission['post_data'];
		$meta      = $submission['meta'];

		// mark the record creation as a preview record
		$meta['preview'] = true;


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
		 * Verify that we are dealing with a FB token Request
		 */
		if ( ! isset( $_GET['ea-fb-token'] ) ) {
			return false;
		}

		/**
		 * @todo  include a way to handle errors on the Send back URL
		 */
		$api      = tribe( 'events-aggregator.service' )->api();
		$response = tribe( 'events-aggregator.service' )->get_facebook_token();
		$type     = $_GET['ea-fb-token'];

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( empty( $response->data ) ) {
			return false;
		}

		if ( empty( $response->data->expires ) ||  empty( $response->data->token ) || empty( $response->data->scopes ) ) {
			return false;
		}

		$url_map = array(
			'new'      => Tribe__Events__Aggregator__Page::instance()->get_url( array( 'tab' => $this->get_slug(), 'ea-auth' => 'facebook' ) ),
			'settings' => Tribe__Settings::instance()->get_url( array( 'tab' => 'addons', 'ea-auth' => 'facebook' ) ),
		);

		if ( ! isset( $url_map[ $type ] ) ) {
			return false;
		}

		// Calculate when will this Token Expire
		$expires = absint( trim( preg_replace( '/[^0-9]/', '', $response->data->expires ) ) );
		$expires += time();

		// Save the Options
		tribe_update_option( 'fb_token', trim( preg_replace( '/[^a-zA-Z0-9]/', '', $response->data->token ) ) );
		tribe_update_option( 'fb_token_expires', $expires );
		tribe_update_option( 'fb_token_scopes', trim( preg_replace( '/[^a-zA-Z0-9\,_-]/', '', $response->data->scopes ) ) );

		// Send it back to the Given Url
		wp_redirect( $url_map[ $type ] );
		exit;
	}

	public function handle_import_finalize( $data ) {
		$this->messages = array(
			'error'   => array(),
			'success' => array(),
			'warning' => array(),
		);

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $data['import_id'] );

		if ( tribe_is_error( $record ) ) {
			$this->messages['error'][] = $record->get_error_message();
			return $this->messages;
		}

		// Make sure we have a post status set no matter what
		if ( empty( $data['post_status'] ) ) {
			$data['post_status'] = tribe( 'events-aggregator.settings' )->default_post_status( $data['origin'] );
		}

		// If the submitted category is null, that means the user intended to de-select the default
		// category if there is one, so setting it to null is ok here
		$record->update_meta( 'category', empty( $data['category'] ) ? null : $data['category'] );
		$record->update_meta( 'post_status', $data['post_status'] );
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
            // here generate a global_id for the data
			$result = $record->process_posts( $data );
		} else {
			// let the record fetch the data and start immediately if possible
			$result = $record->process_posts( array(), true );
		}

		$result->record = $record;

		$this->messages = $this->get_result_messages( $result );

		if (
			empty( $this->messages['error'] )
			|| ! empty( $this->messages['success'] )
			|| ! empty( $this->messages['warning'] )
		) {
			tribe_notice( 'tribe-aggregator-import-complete', array( $this, 'render_notice_import_complete' ), 'type=success' );
		}
	}

	/**
	 * Parses the queue for errors and informations.
	 *
	 * @param Tribe__Events__Aggregator__Record__Queue_Interface|WP_Error|Tribe__Events__Aggregator__Record__Activity $queue
	 *
	 * @return array
	 */
	public function get_result_messages( $queue ) {
		$messages = array();

		if ( is_wp_error( $queue ) ) {
			$messages[ 'error' ][] = $queue->get_error_message();

			tribe_notice( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

			return $messages;
		}

		if (
			$queue instanceof Tribe__Events__Aggregator__Record__Queue_Interface
			&& $queue->has_errors()
		) {
			/** @var Tribe__Events__Aggregator__Record__Queue_Interface $queue */
			$messages['error'][] = $queue->get_error_message();

			tribe_notice( 'tribe-aggregator-import-failed', array( $this, 'render_notice_import_failed' ), 'type=error' );

			return $messages;
		}

		$is_queued = $queue->count();

		$content_post_type = empty( $queue->record->meta['content_type'] ) ? Tribe__Events__Main::POSTTYPE : $queue->record->meta['content_type'];
		$content_type = tribe_get_event_label_singular_lowercase();
		$content_type_plural = tribe_get_event_label_plural_lowercase();

		if ( 'csv' === $queue->record->meta['origin'] && 'tribe_events' !== $queue->record->meta['content_type'] ) {
			$content_type_object = get_post_type_object( $queue->record->meta['content_type'] );
			$content_type = empty( $content_type_object->labels->singular_name_lowercase ) ? $content_type_object->labels->singular_name : $content_type_object->labels->singular_name_lowercase;
			$content_type_plural = empty( $content_type_object->labels->plural_name_lowercase ) ? $content_type_object->labels->name : $content_type_object->labels->plural_name_lowercase;
			$content_post_type = $content_type_object->name;
		}

		if ( ! $is_queued ) {
			$item_created = $queue->activity->get( $content_post_type, 'created' );
			if ( ! empty( $item_created ) ) {
				$content_label = 1 === $queue->activity->count( $content_post_type, 'created' ) ? $content_type : $content_type_plural;

				$messages['success'][] = sprintf( // add created event count
					_n( '%1$d new %2$s was imported.', '%1$d new %2$s were imported.', $queue->activity->count( $content_post_type, 'created' ), 'the-events-calendar' ),
					$queue->activity->count( $content_post_type, 'created' ),
					$content_label
				);
			}

			$item_updated = $queue->activity->get( $content_post_type, 'updated' );
			if ( ! empty( $item_updated ) ) {
				$content_label = 1 === $queue->activity->count( $content_post_type, 'updated' ) ? $content_type : $content_type_plural;

				// @todo: include a part of sentence like: ", including %1$d %2$signored event%3$s.", <a href="/wp-admin/edit.php?post_status=tribe-ignored&post_type=tribe_events">, </a>
				$messages['success'][] = sprintf( // add updated event count
					_n( '%1$d existing %2$s was updated.', '%1$d existing %2$s were updated.', $queue->activity->count( $content_post_type, 'updated' ), 'the-events-calendar' ),
					$queue->activity->count( $content_post_type, 'updated' ),
					$content_label
				);
			}

			$item_skipped = $queue->activity->get( $content_post_type, 'skipped' );
			if ( ! empty( $item_skipped ) ) {
				$content_label = 1 === $queue->activity->count( $content_post_type, 'skipped' ) ? $content_type : $content_type_plural;

				$messages['success'][] = sprintf( // add skipped event count
					_n( '%1$d already-imported %2$s was skipped.', '%1$d already-imported %2$s were skipped.', $queue->activity->count( $content_post_type, 'skipped' ), 'the-events-calendar' ),
					$queue->activity->count( $content_post_type, 'skipped' ),
					$content_label
				);
			}

			$images_created = $queue->activity->get( 'images', 'created' );
			if ( ! empty( $images_created ) ) {
				$messages['success'][] = sprintf( // add image import count
					_n( '%1$d new image was imported.', '%1$d new images were imported.', $queue->activity->count( 'images', 'created' ), 'the-events-calendar' ),
					$queue->activity->count( 'images', 'created' )
				);
			}

			if ( $queue && ! $messages ) {
				$messages['success'][] = sprintf(
					__( 'No %1$s were imported or updated.', 'the-events-calendar' ),
					$content_type_plural
				);
			}

			if ( ! empty( $messages['success'] ) && ! empty( $content_type_object->show_ui ) ) {
				// append a URL to view all records for the given post type
				$url = admin_url( 'edit.php?post_type=' . $content_post_type );
				$link_text = sprintf( __( 'View all %s', 'the-events-calendar' ), $content_type_plural );
				$messages['success'][ count( $messages['success'] ) - 1 ] .= ' <a href="' . esc_url( $url ) . '" >' . esc_html( $link_text ) . '</a>';
			}

			// if not CSV, pull counts for venues and organizers that were auto-created
			if ( 'csv' !== $queue->record->meta['origin'] ) {
				$venue_created = $queue->activity->get( 'venue', 'created' );
				if ( ! empty( $venue_created ) ) {
					$messages['success'][] = '<br/>' .
					sprintf( // add activity count
						_n( '%1$d new venue was imported.', '%1$d new venues were imported.', $queue->activity->count( 'venue', 'created' ), 'the-events-calendar' ),
						$queue->activity->count( 'venue', 'created' )
					) .
					' <a href="' . admin_url( 'edit.php?post_type=tribe_venue' ) . '">' .
					__( 'View your event venues', 'the-events-calendar' ) .
					'</a>';
				}

				$organizer_created = $queue->activity->get( 'organizer', 'created' );
				if ( ! empty( $organizer_created ) ) {
					$messages['success'][] = '<br/>' .
					sprintf( // add organizer count
						_n( '%1$d new organizer was imported.', '%1$d new organizers were imported.', $queue->activity->count( 'organizer', 'created' ), 'the-events-calendar' ),
						$queue->activity->count( 'organizer', 'created' )
					) .
					' <a href="' . admin_url( 'edit.php?post_type=tribe_organizer' ) . '">' .
					__( 'View your event organizers', 'the-events-calendar' ) .
					'</a>';
				}
			}

			$category_created = $queue->activity->get( 'category', 'created' );
			if ( ! empty( $category_created ) ) {
				$messages['success'][] = '<br/>' .
					sprintf( // add category count
						_n( '%1$d new event category was created.', '%1$d new event categories were created.', $queue->activity->count( 'category', 'created' ), 'the-events-calendar' ),
						$queue->activity->count( 'category', 'created' )
					) .
					' <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ) ) . '">' .
					__( 'View your event categories', 'the-events-calendar' ) .
					'</a>';
			}

			$tags_created = $queue->activity->get( 'tag', 'created' );
			if ( ! empty( $tags_created ) ) {
				$messages['success'][] = '<br/>' .
					 sprintf( // add category count
						 _n( '%1$d new event tag was created.', '%1$d new event tags were created.', $queue->activity->count( 'tag', 'created' ), 'the-events-calendar' ),
						 $queue->activity->count( 'tag', 'created' )
					 ) .
					 ' <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=post_tag&post_type=tribe_events' ) ) . '">' .
					 __( 'View your event tags', 'the-events-calendar' ) .
					 '</a>';
			}
		}

		if (
			! empty( $messages['error'] )
			|| ! empty( $messages['success'] )
			|| ! empty( $messages['warning'] )
		) {
			if ( 'manual' == $queue->record->type ) {
				array_unshift( $messages['success'], __( 'Import complete!', 'the-events-calendar' ) . '<br/>' );
			} else {
				array_unshift( $messages['success'], __( 'Your scheduled import was saved and the first import is complete!', 'the-events-calendar' ) . '<br/>' );

				$scheduled_time = strtotime( $queue->record->post->post_modified ) + $queue->record->frequency->interval;
				$scheduled_time_string = date( get_option( 'date_format' ), $scheduled_time ) .
										 _x( ' at ', 'separator between date and time', 'the-events-calendar' ) .
										 date( get_option( 'time_format' ), $scheduled_time );

                if ( 'on_demand' !== $queue->record->frequency->id ) {

                    $messages['success'][] = '<br/>' .
                                             sprintf( // add in timing
                                                 __( 'The next import is scheduled for %1$s.', 'the-events-calendar' ),
                                                 esc_html( $scheduled_time_string )
                                             ) .
                                             ' <a href="' . admin_url( 'edit.php?page=aggregator&post_type=tribe_events&tab=scheduled' ) . '">' .
                                             __( 'View your scheduled imports.', 'the-events-calendar' ) .
                                             '</a>';
                }
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
			/** @var Tribe__Events__Aggregator__Service $service */
			$service      = tribe( 'events-aggregator.service' );
			$result       = (object) array(
				'message_code' => $result->get_error_code(),
				'message'      => $service->get_service_message( $result->get_error_code(), $result->get_error_data() ),
			);
			wp_send_json_error( $result );
		}

		wp_send_json_success( $result );
	}

	public function ajax_fetch_import() {
		$import_id = $_GET['import_id'];

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_import_id( $import_id );

		if ( tribe_is_error( $record ) ) {
			wp_send_json_error( $record );
		}

		$result = $record->get_import_data();

		if ( isset( $result->data ) ) {
			$result->data->origin = $record->origin;
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result );
		}

		// if we've received a source name, let's set that in the record as soon as possible
		if ( ! empty( $result->data->source_name ) ) {
			$record->update_meta( 'source_name', $result->data->source_name );

			if ( ! empty( $record->post->post_parent ) ) {
				$parent_record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record->post->post_parent );

				if ( tribe_is_error( $parent_record ) ) {
					$parent_record->update_meta( 'source_name', $result->data->source_name );
				}
			}
		}

		// if there is a warning in the data let's localize it
		if ( ! empty( $result->warning_code ) ) {
			/** @var Tribe__Events__Aggregator__Service $service */
			$service         = tribe( 'events-aggregator.service' );
			$default_warning = ! empty( $result->warning ) ? $result->warning : null;
			$result->warning = $service->get_service_message( $result->warning_code, array(), $default_warning );
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

		if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		ob_start();
		?>
		<div class="notice inline notice-info tribe-dependent tribe-notice-tribe-missing-aggregator-license" data-ref="tribe-missing-aggregator-license" data-depends="#tribe-ea-field-origin" data-condition-empty>

			<div class="upsell-banner">
				<img src="<?php echo esc_url( tribe_events_resource_url( 'images/aggregator/upsell-banner.png' ) ) ; ?>">
			</div>

			<h3><?php esc_html_e( 'Import Using Event Aggregator', 'the-events-calendar' ); ?></h3>

			<p><?php esc_html_e( 'With Event Aggregator, you can import events from Facebook, iCalendar, Google, and Meetup.com in a jiffy.', 'the-events-calendar' ); ?></p>

			<a href="https://m.tri.be/196y" class="tribe-license-link tribe-button tribe-button-primary" target="_blank">
				<?php esc_html_e( 'Buy It Now', 'the-events-calendar' );?>
				<span class="screen-reader-text">
					<?php esc_html_e( 'opens in a new window', 'the-events-calendar' );?>
				</span>
			</a>

			<a href="https://m.tri.be/196z" class="tribe-license-link tribe-button tribe-button-secondary" target="_blank">
				<?php esc_html_e( 'Learn More', 'the-events-calendar' ); ?>
				<span class="screen-reader-text">
					<?php esc_html_e( 'opens in a new window', 'the-events-calendar' );?>
				</span>
			</a>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the "Eventbrite Tickets" upsell
	 *
	 * @since 4.6.19
	 *
	 * @return string
	 */
	public function maybe_display_eventbrite_upsell() {
		if ( defined( 'TRIBE_HIDE_UPSELL' ) ) {
			return;
		}

		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		if ( class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {
			return;
		}

		ob_start();

		include_once Tribe__Events__Main::instance()->pluginPath . 'src/admin-views/aggregator/banners/eventbrite-upsell.php';

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
			<b><?php esc_html_e( 'Your Event Aggregator license is expired.', 'the-events-calendar' ); ?></b>
			<?php esc_html_e( 'Renew your license in order to import events from Facebook, iCalendar, Google, or Meetup.', 'the-events-calendar' ); ?>
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
		if ( empty( $this->messages['success'] ) ) {
			return null;
		}

		$html = '<p>' . implode( ' ', $this->messages[ 'success' ] ) . '</p>';
		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-import-complete', $html );
	}

	/**
	 * Renders failed import messages
	 */
	public function render_notice_import_failed() {
		if ( empty( $this->messages['error'] ) ) {
			return null;
		}

		$html = '<p>' . implode( ' ', $this->messages['error'] ) . '</p>';
		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-import-failed', $html );
	}
}
