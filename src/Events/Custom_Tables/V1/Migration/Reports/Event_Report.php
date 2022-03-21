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
 * @property object      source_event_post
 * @property array       strategies_applied
 * @property array       series
 * @property null|string error
 * @property string      status
 * @property array       created_events
 * @property bool        is_single
 * @property string      tickets_provider
 * @property bool        has_tickets
 * @property null|float  end_timestamp
 * @property null|float  start_timestamp
 */
class Event_Report implements JsonSerializable {

	/**
	 * Key used to flag this event is in progress and already assigned
	 * to a strategy worker.
	 */
	const META_KEY_MIGRATION_LOCK_HASH = '_tec_ct1_migration_lock_uid';
	/**
	 * Key used to store the Event_Report data.
	 */
	const META_KEY_REPORT_DATA = '_tec_ct1_migrated_report';
	/**
	 * Flag to store the various reportable phases for an event.
	 */
	const META_KEY_MIGRATION_PHASE = '_tec_ct1_current_migration_phase';
	/**
	 * Flag for undo in progress.
	 */
	const META_VALUE_MIGRATION_PHASE_UNDO_IN_PROGRESS = 'UNDO_IN_PROGRESS';
	/**
	 * Flag for migration in progress.
	 */
	const META_VALUE_MIGRATION_PHASE_MIGRATION_IN_PROGRESS = 'MIGRATION_IN_PROGRESS';
	/**
	 * Flag for migration completed successfully.
	 */
	const META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS = 'MIGRATION_SUCCESS';
	/**
	 * Flag for migration completed with a failure.
	 */
	const META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE = 'MIGRATION_FAILURE';
	/**
	 * Flag for undo completed with a failure.
	 */
	const META_VALUE_MIGRATION_PHASE_UNDO_FAILURE = 'UNDO_FAILURE';

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
	const STATUS_FAILURE = 'failure';

	/**
	 * Status for successful migration.
	 */
	const STATUS_SUCCESS = 'success';

	/**
	 * @since TBD
	 * @var array<string, mixed> Report data.
	 */
	protected $data = [
		'start_timestamp'    => null,
		'end_timestamp'      => null,
		'has_tickets'        => false,
		'tickets_provider'   => '',
		'is_single'          => true,
		'created_events'     => [],
		'status'             => '', // @todo Do we really need this? This could be handled by the meta phase...
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
	 * Get all of the report data.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed>
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Will fetch its data from the database and populate it's internal state.
	 *
	 * @since TBD
	 *
	 * @return Event_Report
	 */
	public function hydrate() {
		$source_post = $this->source_event_post;
		$data        = get_post_meta( $source_post->ID, self::META_KEY_REPORT_DATA, true );
		if ( empty( $data ) ) {
			$data = [];
		}
		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * Add each WP_Post for events that will be created for this migration strategy.
	 *
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
	 * When you start the migration process set the appropriate state.
	 *
	 * @since TBD
	 *
	 * @return $this
	 */
	public function start_event_migration() {
		update_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE, self::META_VALUE_MIGRATION_PHASE_MIGRATION_IN_PROGRESS );

		return $this->set_start_timestamp();
	}

	/**
	 * When you start the undo process set the appropriate state.
	 *
	 * @since TBD
	 *
	 * @return $this
	 */
	public function start_event_undo_migration() {
		update_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE, self::META_VALUE_MIGRATION_PHASE_UNDO_IN_PROGRESS );

		return $this->set_start_timestamp();
	}

	/**
	 * Setup the microtime for when the migration starts.
	 *
	 * @since TBD
	 *
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
	 *
	 * @return $this
	 */
	protected function set_end_timestamp() {
		$this->data['end_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * Sets a key in the report data.
	 *
	 * @since TBD
	 *
	 * @param string     $key   The key to set in the report data.
	 * @param mixed|null $value The value to set for the key.
	 *
	 * @return $this A reference to this object, for chaining purposes.
	 */
	public function set( $key, $value = null ) {
		$this->data[ $key ] = $value;

		return $this;
	}

	/**
	 * Set the error message for migration failure events.
	 *
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
	 * Set the status flag for this report.
	 *
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
	 * Add each WP_Post for series that will be created for this migration strategy.
	 *
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
	 * Add each strategy applied for this migration.
	 *
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
	 * Set the ticket provider, when an ET event.
	 *
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
	 * Removes all of the migration metadata.
	 *
	 * @since TBD
	 *
	 * @return $this
	 */
	public function clear_meta() {
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_REPORT_DATA );
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_LOCK_HASH );

		return $this;
	}

	/**
	 * Mark this event migration as a success, and save in the database.
	 *
	 * @since TBD
	 *
	 * @return Event_Report
	 */
	public function migration_success() {
		// Track time immediately
		$this->set_end_timestamp();
		update_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE, self::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS );
		$this->unlock_event();

		return $this
			->set_status( self::STATUS_SUCCESS )
			->save();
	}

	/**
	 * Mark this event migration as a failure, and save in database with a reason.
	 *
	 * @since TBD
	 *
	 * @param string $reason A human-readable description of why the migration failed.
	 *
	 * @return Event_Report A reference to the Event Report object for the specific
	 *                      that is being processed.
	 */
	public function migration_failed( $reason ) {
		// Track time immediately
		$this->set_end_timestamp();
		update_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE, self::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE );
		$this->unlock_event();

		return $this->set_error( $reason )
		            ->set_status( self::STATUS_FAILURE )
		            ->save();
	}

	/**
	 * Will remove the lock from this Event.
	 *
	 * @return $this
	 */
	public function unlock_event() {
		// @todo this seems a bit off-place here.
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_LOCK_HASH );

		return $this;
	}

	/**
	 * Stores current state in the meta table.
	 *
	 * @since TBD
	 *
	 * @return $this
	 */
	protected function save() {
		update_post_meta( $this->source_event_post->ID, self::META_KEY_REPORT_DATA, $this->data );

		return $this;
	}

	/**
	 * Getter for the report data.
	 *
	 * @since TBD
	 *
	 * @param string $prop The property key.
	 *
	 * @return mixed|null
	 */
	public function __get( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : null;
	}

	/**
	 * The JSON serializer logic.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->data;
	}

}