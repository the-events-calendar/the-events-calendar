<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Errors {

	/**
	 * Comment Type for EA errors
	 *
	 * @since  4.3.2
	 *
	 * @var string
	 */
	public static $comment_type = 'tribe-ea-error';

	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		return self::$instance ? self::$instance : self::$instance = new self;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		// Prevent Comment Errors from Showing up on normal queries
		add_action( 'pre_get_comments', array( $this, 'hide_error_comments' ), 10 );
		add_filter( 'comments_clauses', array( $this, 'hide_error_comments_pre_41' ), 10, 2 );
		add_filter( 'comment_feed_where', array( $this, 'hide_error_comments_from_feeds' ), 10, 2 );
		add_filter( 'wp_count_comments', array( $this, 'remove_error_comments_from_wp_counts' ), 10, 2 );

		// Create the Errors
		tribe_register_error( 'core:aggregator:attachment-error', __( 'The image associated with your event could not be attached to the event.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:daily-limit-reached', __( 'The daily limit of %d import requests to the Event Aggregator service has been reached. Please try again later.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-failed', __( 'You cannot delete a history record (ID: "%d"). ', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:delete-record-permissions', __( 'You do not have permission to delete this record.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:http_request-limit', __( 'During scheduled import, the limit of HTTP requests was reached and the import was rescheduled.', 'the-events-calendar' ) );

		tribe_register_error( 'core:aggregator:invalid-create-record-type', __( 'An invalid import type was used when trying to create this import record.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-file', __( 'You must provide a valid CSV file to perform a CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-csv-parameters', __( 'Invalid data provided for CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-edit-record-type', __( 'Only scheduled import records can be edited.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-id', __( 'Unable to find an event with the ID of %s.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-event-json', __( 'The Event Aggregator API responded with bad data. Please <a href="https://theeventscalendar.com/support/post/" target="_blank">contact support</a>.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-facebook-token', __( 'We received an invalid Facebook Token from the Service.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-import-id', __( 'Unable to find an import record with the ID of %s.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-image-path', __( 'Unable to attach an image to the event', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-frequency', __( 'An invalid frequency was used when trying to create this scheduled import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-object', __( 'Unable to find a matching post.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-origin', __( 'The import record is missing the origin.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-record-post_type', __( 'Unable to get a post of the correct type.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-key', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses before using this service.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:invalid-service-response', __( 'There may be an issue with the Event Aggregator server. Please try your import again later.', 'the-events-calendar' ) );

		tribe_register_error( 'core:aggregator:missing-csv-column-map', __( 'You must map columns from the CSV file to specific fields in order to perform a CSV import.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:missing-csv-file', __( 'The CSV file cannot be found. You may need to re-upload the file.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:record-not-finalized', __( 'Import records must be finalized before posts can be inserted.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-child-failed', __( 'Unable to save scheduled import instance. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:save-schedule-failed', __( 'Unable to save scheduled import. Please try again.', 'the-events-calendar' ) );
		tribe_register_error( 'core:aggregator:queue-pending-events', __( 'The records you were attempting to import were still not available when this queue was processed. Please try again.', 'the-events-calendar' ) );
	}

	/**
	 * Exclude Aggregator Errors (comments) from showing in Recent Comments widgets
	 * Note: On WP 4.1 and above
	 *
	 * @since 4.3.2
	 *
	 * @param obj $query WordPress Comment Query Object
	 *
	 * @return void
	 */
	public function hide_error_comments( $query ) {
		global $wp_version;

		// Only Apply on 4.1 and above
		if ( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
			return;
		}

		// Prevent this happening if we don't have EA active
		if ( ! tribe( 'events-aggregator.main' )->is_active( true ) ) {
			return;
		}

		// If we passed this type is because we want to query it
		if ( false !== strpos( $query->query_vars['type'], self::$comment_type ) ) {
			return;
		}

		if (
			( // If We have passed type__in as string and we have the comment type, bail
				is_string( $query->query_vars['type__in'] ) &&
				false !== strpos( $query->query_vars['type__in'], self::$comment_type )
			) ||
			( // If we passed type__in as array and we have the comment type, bail
				is_array( $query->query_vars['type__in'] ) &&
				in_array( self::$comment_type, $query->query_vars['type__in'])
			)
		) {
			return;
		}

		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}

		$types[] = self::$comment_type;
		$query->query_vars['type__not_in'] = $types;
	}

	/**
	 * Exclude Aggregator Errors (comments) from showing in Recent Comments widgets
	 * Note: On Pre WP 4.1
	 *
	 * @since 4.3.2
	 *
	 * @param array $clauses Comment clauses for comment query
	 * @param obj $wp_comment_query WordPress Comment Query Object
	 *
	 * @return array $clauses Updated comment clauses
	 */
	public function hide_error_comments_pre_41( $clauses, $wp_comment_query ) {
		global $wpdb, $wp_version;

		// Prevent this happening if we don't have EA active
		if ( ! tribe( 'events-aggregator.main' )->is_active( true ) ) {
			return $clauses;
		}

		if( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
			$clauses['where'] .= $wpdb->prepare( ' AND comment_type != %s', self::$comment_type );
		}
		return $clauses;
	}

	/**
	 * Exclude Aggregator Errors (comments) from showing in comment feeds
	 *
	 * @since 4.3.2
	 *
	 * @param array $where
	 * @param obj $wp_comment_query WordPress Comment Query Object
	 *
	 * @return array $where
	 */
	public function hide_error_comments_from_feeds( $where, $wp_comment_query ) {
	    global $wpdb;

		// Prevent this happening if we don't have EA active
		if ( ! tribe( 'events-aggregator.main' )->is_active( true ) ) {
			return $where;
		}

		$where .= $wpdb->prepare( ' AND comment_type != %s', self::$comment_type );
		return $where;
	}

	/**
	 * Remove Aggregator Error Comments from the wp_count_comments function
	 *
	 * @since 4.3.2
	 *
	 * @param array $stats (empty from core filter)
	 * @param int $post_id Post ID
	 *
	 * @return array Array of comment counts
	*/
	public function remove_error_comments_from_wp_counts( $stats, $post_id ) {
		global $wpdb, $pagenow;

		if ( ! in_array( $pagenow, array( 'index.php', 'edit-comments.php' ) ) ) {
			return $stats;
		}

		// Prevent this happening if we don't have EA active
		if ( ! tribe( 'events-aggregator.main' )->is_active( true ) ) {
			return $stats;
		}

		$post_id = (int) $post_id;
		$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

		if ( false !== $stats ){
			return $stats;
		}

		$where = 'WHERE comment_type != "' . self::$comment_type . '"';

		if ( $post_id > 0 ) {
			$where .= $wpdb->prepare( ' AND comment_post_ID = %d', $post_id );
		}

		$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

		$total = 0;
		$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );
		foreach ( (array) $count as $row ) {
			// Don't count post-trashed toward totals
			if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ){
				$total += $row['num_comments'];
			}

			if ( isset( $approved[ $row['comment_approved'] ] ) ) {
				$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
			}
		}

		$stats['total_comments'] = $stats['all'] = $total;
		foreach ( $approved as $key ) {
			if ( empty( $stats[ $key ] ) ) {
				$stats[ $key ] = 0;
			}
		}

		$stats = (object) $stats;
		wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

		return $stats;
	}


	/**
	 * Maybe build message from args
	 *
	 * @since 4.3
	 * @param string $message
	 * @param array $args Message args
	 *
	 * @return string
	 */
	public static function build( $message, $args = array() ) {
		return vsprintf( $message, $args );
	}
}


