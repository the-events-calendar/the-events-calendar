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
	 * @since TBD
	 * @var array Report data.
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
	 * @return array
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
		$source_post = $this->get_source_event_post();
		$data        = get_post_meta( $source_post->ID, self::META_KEY_REPORT_DATA );
		if ( empty( $data ) ) {
			$data = [];
		}
		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * @since TBD
	 * @return array<array>
	 */
	public function get_created_events() {
		return $this->data['created_events'];
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
		$this->data['start_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * When finished will update appropriate state.
	 *
	 * @since TBD
	 * @return $this
	 */
	protected function end_event_migration() {
		$this->data['end_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * @since TBD
	 * @return null|float
	 */
	public function get_end_timestamp() {
		return $this->data['end_timestamp'];
	}

	/**
	 * @since TBD
	 * @return null|float
	 */
	public function get_start_timestamp() {
		return $this->data['start_timestamp'];
	}

	/**
	 * @since TBD
	 * @return array
	 */
	public function get_series() {
		return $this->data['series'];
	}

	/**
	 * @since TBD
	 *
	 * @param bool $is_recurring
	 *
	 * @return $this
	 */
	public function set_is_recurring( bool $is_recurring ) {
		$this->data['is_recurring'] = $is_recurring;

		return $this;
	}

	/**
	 * @since TBD
	 * @return bool
	 */
	public function get_is_recurring() {
		return $this->data['is_recurring'];
	}

	/**
	 * @since TBD
	 * @return mixed
	 */
	public function get_source_event_post() {
		return $this->data['source_event_post'];
	}

	/**
	 * @since TBD
	 * @return null
	 */
	public function get_error() {
		return $this->data['error'];
	}

	/**
	 * @since TBD
	 *
	 * @param string $reason
	 *
	 * @return $this
	 */
	public function set_error( string $reason ) {
		$this->data['error'] = $reason;

		return $this;
	}

	/**
	 * @since TBD
	 * @return string
	 */
	public function get_status() {
		return $this->data['status'];
	}

	/**
	 * @since TBD
	 *
	 * @param string $status
	 *
	 * @return $this
	 */
	public function set_status( string $status ) {
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
	 * @return array
	 */
	public function get_strategies_applied() {
		return $this->data['strategies_applied'];
	}

	/**
	 * @since TBD
	 *
	 * @param string $strategy
	 *
	 * @return $this
	 */
	public function add_strategy( string $strategy ) {
		$this->data['strategies_applied'][] = $strategy;

		return $this;
	}

	/**
	 * @since TBD
	 * @return string
	 */
	public function get_tickets_provider() {
		return $this->data['tickets_provider'];
	}

	/**
	 * @since TBD
	 * @return mixed
	 */
	public function get_has_tickets() {
		return $this->data['has_tickets'];
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
	 * @since TBD
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->data;
	}

	/**
	 * Mark this event migration as a success, and save in the database.
	 *
	 * @since TBD
	 * @return Event_Report
	 */
	public function success() {
		update_post_meta( $this->get_source_event_post()->ID, self::META_KEY_SUCCESS, 1 );
		delete_post_meta( $this->get_source_event_post()->ID, self::META_KEY_FAILURE );

		return $this->end_event_migration()
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
	public function failed( string $reason ) {
		update_post_meta( $this->get_source_event_post()->ID, self::META_KEY_FAILURE, 1 );
		delete_post_meta( $this->get_source_event_post()->ID, self::META_KEY_SUCCESS );

		return $this->set_error( $reason )
		            ->end_event_migration()
		            ->save();
	}

	/**
	 * Stores current state in the meta table.
	 *
	 * @since TBD
	 * @return $this
	 */
	protected function save() {
		update_post_meta( $this->get_source_event_post()->ID, self::META_KEY_REPORT_DATA, $this->data );

		return $this;
	}
}