<?php
/**
 * Primarily focused on helping users who established a site with 3.12.1 or
 * an even earlier release to migrate ECP custom meta/additional field data
 * to a new more readily searchable form.
 */
class Tribe__Events__Pro__Admin__Custom_Meta_Tools {
	/**
	 * Tracks the number of events updated, if any.
	 *
	 * @var int
	 */
	protected $updated = 0;

	/**
	 * Flags if the ajax update loop failed.
	 *
	 * @var bool
	 */
	protected $rerun_needed = false;


	public function __construct() {
		add_action( 'admin_init', array( $this, 'updater_listen' ) );
		add_action( 'wp_ajax_additional_fields_update', array( $this, 'updater_listen' ) );
		add_action( 'tribe_settings_above_tabs', array( $this, 'update_ui' ) );
	}

	/**
	 * Listens for requests to run the additional field update process.
	 */
	public function updater_listen() {
		// Inspect any post/get data (but not cookie data) for updater requests
		$request = array_merge( array( 'do_additional_fields_update' => false ), $_POST, $_GET );

		if ( ! wp_verify_nonce( $request[ 'do_additional_fields_update' ], 'custom_meta_tools:updater' ) ) {
			return;
		}

		$this->updater_run();

		// If we're within an ajax loop...
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json( array(
				'continue' => $this->are_updates_needed(),
				'updated'  => $this->updated,
				'check'    => wp_create_nonce( 'custom_meta_tools:updater' )
			) );
		}

		// Or if for some reason ajax failed...
		if ( $this->are_updates_needed() ) {
			$this->rerun_needed = true;
		}
	}

	/**
	 * Processes a set of event posts in need of additional field updates (a count of how
	 * many are updated is maintained in the object's "updated" field).
	 */
	public function updater_run() {
		/**
		 * Controls the number of events processed in a single batch when additional field
		 * data is updated.
		 *
		 * @var int $batch_size
		 */
		$batch_size = (int) apply_filters( 'tribe_events_pro_additional_fields_update_batch_size', 20 );

		foreach ( $this->find_events_needing_update( $batch_size ) as $event_id ) {
			$this->rebuild_fields( $event_id );
			$this->updated++;
		}
	}

	public function update_ui() {
		// Only display the notice/update UI within the additional fields screen
		if ( 'additional-fields' !== Tribe__Settings::instance()->currentTab ) {
			return;
		}

		// No updates required? No need to bother anyone
		if ( ! $this->are_updates_needed() ) {
			return;
		}

		// Setup our supporting JS
		$this->update_js();

		$update_url = add_query_arg( array(
			'do_additional_fields_update' => wp_create_nonce( 'custom_meta_tools:updater' )
		) );

		$prompt = $this->rerun_needed
			? __( 'Some additional field data still needs to be updated (unfortunately, we were unable to continue to update things automatically).', 'tribe-events-calendar-pro' )
			: __( 'We need to update the additional field data for some of your events.', 'tribe-events-calendar-pro' );

		$message = $prompt
			. ' <span class="update-text"> <a href="' . $update_url . '">'
			. _x( 'Click here to run the updater.', 'additional fields update trigger', 'tribe-events-calendar-pro' )
			. '</a> </span>';

		echo "<div id='tribe-additional-field-update' class='notice notice-warning'> <p> $message </p> </div>";
	}

	protected function update_js() {
		$path = tribe_events_pro_resource_url( 'events-additional-fields-update.js' );
		$spinner = '<img src="' . esc_url( get_admin_url( null, 'images/spinner.gif' ) ) . '">';

		wp_enqueue_script( 'tribe-events-pro-additional-fields-update', $path, array( 'jquery' ), false, true );
		wp_localize_script( 'tribe-events-pro-additional-fields-update', 'tribe_additional_fields', array(
			'update_check'    => wp_create_nonce( 'custom_meta_tools:updater' ),
			'complete_msg'    => '<strong>' . _x( 'All fields have been updated!', 'additional field update', 'tribe-events-calendar-pro' ) . '</strong>',
			'failure_msg'     => '<strong>' . _x( 'An unexpected error stopped the update from completing.', 'additional field update', 'tribe-events-calendar-pro' ) . '</strong>',
			'in_progress_msg' => '<strong>' . _x( 'Working&hellip;', 'additional field update', 'tribe-events-calendar-pro' ) . "</strong> $spinner",
		) );
	}

	/**
	 * Tests to see if there are events with custom field data applied to them
	 * which require an update in respect of multichoice field support.
	 *
	 * @return bool
	 */
	public function are_updates_needed() {
		$needing_update = $this->find_events_needing_update( 1 );
		return ! empty( $needing_update );
	}

	/**
	 * Returns a list of event post IDs for those events believed to require an
	 * additional field update (ie, they have a single ECP additional field but
	 * not individual
	 *
	 * @param int    $limit  defaults to -1, representing "unlimited"
	 *
	 * @return array
	 */
	public function find_events_needing_update( $limit = -1 ) {
		$event_ids = array();

		foreach ( $this->multichoice_fields() as $custom_field ) {
			// Find any posts needing updates for a specific field type
			$event_ids += $this->find_events_needing_update_for( $custom_field[ 'name' ], $limit );
			$event_ids  = array_unique( $event_ids );

			// If we've reached our limit shorten the result set and bail out of the loop
			if ( $limit > 0 && count( $event_ids ) > $limit ) {
				array_splice( $event_ids, $limit );
				break;
			}
		}

		return $event_ids;
	}

	/**
	 * Returns a list of event post IDs that have the supplied custom field but
	 * only in it's "ordinary" form (ie, where multiple values are held in a single
	 * post meta record).
	 *
	 * @param string $field_name
	 * @param int    $limit       follows normal rules, ie -1 represents unlimited
	 *
	 * @return array
	 */
	public function find_events_needing_update_for( $field_name, $limit ) {
		global $wpdb;

		$limit = ( $limit > 0 )
			? ' LIMIT ' . absint( $limit ) . ' '
			: '';

		$query = $wpdb->prepare( "
				-- Find all post IDs associated with the specified legacy custom field key
				SELECT DISTINCT( post_id )
				FROM   $wpdb->postmeta
				WHERE  meta_key = %s

				-- Which have not yet been assigned to a new multichoice custom field key
				AND    post_id NOT IN (
						   SELECT DISTINCT( post_id )
						   FROM   $wpdb->postmeta
						   WHERE  meta_key = %s
					   )
				$limit
			", $field_name, "_$field_name"
		);

		return array_map( 'intval', (array) $wpdb->get_col( $query ) );
	}

	/**
	 * Rebuilds the (ECP) custom/additional field data for the specified event.
	 *
	 * This ensures that events last created/updated under ECP 3.12.x or earlier with
	 * "multichoice"-type  additional fields have all the expected entries in the post
	 * meta table.
	 *
	 * @param $event_id
	 */
	public function rebuild_fields( $event_id ) {
		$fields = array();

		foreach ( (array) tribe_get_option( 'custom-fields', array() ) as $custom_field ) {
			$value = get_post_meta( $event_id, $custom_field[ 'name' ], true );

			// If this is a multichoice field, break it down from a pipe-separated format to an array
			if ( Tribe__Events__Pro__Custom_Meta::is_multichoice( $custom_field ) ) {
				$value = explode( '|', $value );
			}

			$fields[ $custom_field[ 'name' ] ] = $value;
		}

		// Trigger an update
		Tribe__Events__Pro__Custom_Meta::save_single_event_meta( $event_id, $fields );
	}

	/**
	 * Provides a list of all currently defined (ECP) custom fields which are "multichoice"
	 * in nature (for example, checkbox-type fields would be included in this list by default).
	 *
	 * @return array
	 */
	public function multichoice_fields() {
		$multichoice_fields = array();
		$defined_fields     = (array) tribe_get_option( 'custom-fields', array() );

		foreach ( $defined_fields as $custom_field ) {
			if ( Tribe__Events__Pro__Custom_Meta::is_multichoice( $custom_field[ 'type' ] ) ) {
				$multichoice_fields[] = $custom_field;
			}
		}

		return $multichoice_fields;
	}
}
