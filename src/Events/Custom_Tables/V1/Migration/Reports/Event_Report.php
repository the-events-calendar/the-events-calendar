<?php
/**
 * A value object providing information about an Event migration.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use WP_Post;
use JsonSerializable;

/**
 * Class Event_Report.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Event_Report implements JsonSerializable {

	/**
	 * Key used to flag this event is in progress and already assigned
	 * to a strategy worker.
	 */
	const META_KEY_IN_PROGRESS = '_tec_ct1_migrating';
	/**
	 * Key used to store the Event_Report data.
	 */
	const META_KEY_REPORT_DATA = '_tec_ct1_migrated_report';
	/**
	 * Key used to flag the migration succeeded.
	 */
	const META_KEY_SUCCESS = '_tec_ct1_migration_success';
	/**
	 * Key used to flag the migration failed.
	 */
	const META_KEY_FAILURE = '_tec_ct1_migration_failure';

	/**
	 * Status flags for a particular operation. This is not tied to the action,
	 * it should denote a high level failure.
	 */
	const ALLOWED_STATUSES = [
		'success',
		'failure'
	];

	/**
	 * Status for failed migration.
	 */
	const FAILURE_STATUS = 'failure';

	/**
	 * Status for successful migration.
	 */
	const SUCCESS_STATUS = 'success';

	/**
	 * @since TBD
	 * @var array<string, mixed> Report data.
	 */
	protected $data = [
		'start_timestamp'    => null,
		'end_timestamp'      => null,
		'has_tickets'        => false,
		'tickets_provider'   => '',
		'is_recurring'       => false,
		'created_events'     => [],
		'status'             => '',
		'error'              => null,
		'series'             => [],
		'strategies_applied' => [],
		'source_event_post'  => null,
	];

	/**
	 * Construct and hydrate the Event_Report for this WP_Post
	 *
	 * @since TBD
	 *
	 * @param WP_Post $source_post
	 */
	public function __construct( WP_Post $source_post ) {
		$this->data['source_event_post'] = (object) [
			'ID'         => $source_post->ID,
			'post_title' => $source_post->post_title,
		];

		$this->hydrate();
	}

	/**
	 * @since TBD
	 * @return array<string, mixed>
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Will fetch its data from the database and populate it's internal state.
	 *
	 * @since TBD
	 * @return Event_Report
	 */
	public function hydrate() {
		$source_post = $this->source_event_post;
		$data        = get_post_meta( $source_post->ID, self::META_KEY_REPORT_DATA );
		if ( empty( $data ) ) {
			$data = [];
		}
		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param WP_Post $post
	 * @param         $occurrences_generated
	 *
	 * @return $this
	 */
	public function add_created_event( WP_Post $post, $occurrences_generated ) {
		$this->data['created_events'][] = (object) [
			'ID'                    => $post->ID,
			'post_title'            => $post->post_title,
			'occurrences_generated' => $occurrences_generated,
		];

		return $this;
	}

	/**
	 * When you start the migration process, will set appropriate state.
	 *
	 * @since TBD
	 * @return $this
	 */
	public function start_event_migration() {
		return $this->set_start_timestamp();
	}

	/**
	 * Setup the microtime for when the migration starts.
	 *
	 * @since TBD
	 * @return $this
	 */
	protected function set_start_timestamp() {
		$this->data['start_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * Setup the microtime for when the migration ends.
	 *
	 * @since TBD
	 * @return $this
	 */
	protected function set_end_timestamp() {
		$this->data['end_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param bool $is_recurring
	 *
	 * @return $this
	 */
	public function set_is_recurring( bool $is_recurring ) {
		//@todo Should we infer this from the created_events data...? Or statically apply / require being applied?
		$this->data['is_recurring'] = $is_recurring;

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param string $reason
	 *
	 * @return $this
	 */
	protected function set_error( string $reason ) {
		$this->data['error'] = $reason;

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param string $status
	 *
	 * @return $this
	 */
	protected function set_status( string $status ) {
		if ( ! in_array( $status, self::ALLOWED_STATUSES ) ) {
			throw \Exception( "Invalid status applied: $status" );
		}
		$this->data['status'] = $status;

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param WP_Post $post
	 *
	 * @return $this
	 */
	public function add_series( WP_Post $post ) {
		$this->data['series'][] = (object) [
			'ID'         => $post->ID,
			'post_title' => $post->post_title,
		];

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param string $strategy
	 *
	 * @return $this
	 */
	public function add_strategy( string $strategy ) {
		// @todo validate strategies applied? Don't care in case of third party?
		$this->data['strategies_applied'][] = $strategy;

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param string $tickets_provider
	 *
	 * @return $this
	 */
	public function set_tickets_provider( string $tickets_provider ) {
		$this->data['has_tickets']      = (bool) $tickets_provider;
		$this->data['tickets_provider'] = $tickets_provider;

		return $this;
	}

	/**
	 * Mark this event undo migration as a success, and save in the database.
	 *
	 * @since TBD
	 * @return Event_Report
	 */
	public function undo_success() {
		// We clear our meta data when we are done.
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_FAILURE );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_SUCCESS );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_REPORT_DATA );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_IN_PROGRESS );

		return $this;
	}

	/**
	 * Mark this event migration as a success, and save in the database.
	 *
	 * @since TBD
	 * @return Event_Report
	 */
	public function migration_success() {
		// Track time immediately
		$this->set_end_timestamp();
		update_post_meta( $this->source_event_post->ID, self::META_KEY_SUCCESS, 1 );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_FAILURE );

		return $this
			->set_status( self::SUCCESS_STATUS )
			->save();
	}

	/**
	 * Mark this event migration as a failure, and save in database with a reason.
	 *
	 * @since TBD
	 *
	 * @param string $reason
	 *
	 * @return Event_Report
	 */
	public function migration_failed( string $reason ) {
		// Track time immediately
		$this->set_end_timestamp();
		update_post_meta( $this->source_event_post->ID, self::META_KEY_FAILURE, 1 );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_SUCCESS );

		return $this->set_error( $reason )
		            ->set_status( self::FAILURE_STATUS )
		            ->save();
	}

	/**
	 * Stores current state in the meta table.
	 *
	 * @since TBD
	 * @return $this
	 */
	protected function save() {
		update_post_meta( $this->source_event_post->ID, self::META_KEY_REPORT_DATA, $this->data );

		return $this;
	}

	/**
	 * @since TBD
	 *
	 * @param $prop
	 *
	 * @return mixed|null
	 */
	public function __get( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : null;
	}

	/**
	 * @since TBD
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->data;
	}

}