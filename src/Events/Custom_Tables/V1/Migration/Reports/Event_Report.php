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
	const META_KEY_IN_PROGRESS = '_tec_ct1_migrating';
	const META_KEY_COMPLETE = '_tec_ct1_migrated';

	/**
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
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Will fetch its data from the database and populate it's internal state.
	 */
	public function hydrate() {
		$source_post = $this->get_source_event_post();
		$data        = get_post_meta( $source_post->ID, self::META_KEY_IN_PROGRESS );
		if ( empty( $data ) ) {
			$data = get_post_meta( $source_post->ID, self::META_KEY_COMPLETE );
		}
		if ( empty( $data ) ) {
			$data = [];
		}
		$this->data = array_merge( $this->data, $data );
	}

	/**
	 * @return array
	 */
	public function get_created_events() {
		return $this->data['created_events'];
	}

	/**
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
	 * @return $this
	 */
	public function start_event_migration() {
		$this->data['start_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * When finished will update appropriate state.
	 *
	 * @return $this
	 */
	public function end_event_migration() {
		$this->data['end_timestamp'] = microtime( true );

		return $this;
	}

	/**
	 * @return null|float
	 */
	public function get_end_timestamp() {
		return $this->data['end_timestamp'];
	}

	/**
	 * @return null|float
	 */
	public function get_start_timestamp() {
		return $this->data['start_timestamp'];
	}

	/**
	 * @return array
	 */
	public function get_series() {
		return $this->data['series'];
	}

	/**
	 * @param bool $is_recurring
	 *
	 * @return $this
	 */
	public function set_is_recurring( bool $is_recurring ) {
		$this->data['is_recurring'] = $is_recurring;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function get_is_recurring() {
		return $this->data['is_recurring'];
	}

	/**
	 * @return mixed
	 */
	public function get_source_event_post() {
		return $this->data['source_event_post'];
	}

	/**
	 * @return null
	 */
	public function get_error() {
		return $this->data['error'];
	}

	public function set_error( string $reason ) {
		$this->data['error'] = $reason;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_status() {
		return $this->data['status'];
	}

	public function set_status( string $status ) {
		$this->data['status'] = $status;

		return $this;
	}

	public function add_series( WP_Post $post ) {
		$this->data['series'][] = (object) [
			'ID'         => $post->ID,
			'post_title' => $post->post_title,
		];

		return $this;
	}

	/**
	 * @return array
	 */
	public function get_strategies_applied() {
		return $this->data['strategies_applied'];
	}


	public function add_strategy( string $strategy ) {
		$this->data['strategies_applied'][] = $strategy;

		return $this;
	}


	/**
	 * @return string
	 */
	public function get_tickets_provider() {
		return $this->data['tickets_provider'];
	}

	public function get_has_tickets() {
		return $this->data['has_tickets'];
	}

	public function set_tickets_provider( string $tickets_provider ) {
		$this->data['has_tickets']      = (bool) $tickets_provider;
		$this->data['tickets_provider'] = $tickets_provider;

		return $this;
	}

	public function jsonSerialize() {
		return $this->data;
	}

	public function save() {
		// Done?
		if ( $this->get_end_timestamp() ) {
			update_post_meta( $this->get_source_event_post()->ID, self::META_KEY_COMPLETE, $this->data );
			delete_post_meta( $this->get_source_event_post()->ID, self::META_KEY_IN_PROGRESS );

			return $this;
		}
		// In progress
		update_post_meta( $this->get_source_event_post()->ID, self::META_KEY_IN_PROGRESS, $this->data );

		return $this;
	}
}