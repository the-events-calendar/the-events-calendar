<?php
/**
 * A value object providing information about an Event migration.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Multi_Rule_Event_Migration_Strategy;
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
	 * Key used to store a weighted numeric value for sorting report results.
	 */
	const META_KEY_ORDER_WEIGHT = '_tec_ct1_report_order_weight';
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
	 * The report key used to indicate whether the migration of an Event is a failure or not.
	 */
	const REPORT_KEY_FAILURE = 'report_failure';

	/**
	 * The report key used to indicate whether an Event is single and has tickets or not.
	 */
	const REPORT_KEY_SINGLE_WITH_TICKETS = 'report_single_event_with_tickets';

	/**
	 * The report key used to indicate whether an Event is single or not.
	 */
	const REPORT_KEY_SINGLE_EVENT = 'report_is_single_event';

	/**
	 * @since TBD
	 *
	 * @var int Sort order weight.
	 */
	private $report_order_weight = 0;

	/**
	 * @since TBD
	 *
	 * @var array<string, mixed> Report data.
	 */
	private $data = [
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
	 * A map from the supported report keys to their assigned weight.
	 * Initialized in the `__construct` method.
	 *
	 * @since TBD
	 *
	 * @var array<string,int>
	 */
	private $report_weights_map = [];

	/**
	 * Construct and hydrate the Event_Report for this WP_Post
	 *
	 * @since TBD
	 *
	 * @param WP_Post $source_post
	 */
	public function __construct( $source_post ) {
		// @todo Construct override ? Allow for passing report data directly..?
		if ( $source_post instanceof WP_Post ) {
			$this->data['source_event_post'] = (object) [
				'ID'         => $source_post->ID,
				'post_title' => $source_post->post_title,
			];
		}

		$this->report_weights_map = [
			self::REPORT_KEY_FAILURE             => 10 ** 5,
			self::REPORT_KEY_SINGLE_WITH_TICKETS => 10 ** 4,
			self::REPORT_KEY_SINGLE_EVENT        => 10 ** 3,
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
	 * This will set a weight on the current `report_order_weight`.
	 *
	 * @since TBD
	 *
	 * @param array<string,int> $key_bits A map from weight report entries to a base value
	 *                                    that has not been weighted yet.
	 *
	 * @return $this A reference to this objec, for chaining.
	 */
	public function set_report_order_weight( array $key_bits = []) {
		/**
		 * Filters the report weights map to allow other plugins to manipulate the order
		 * the reports should be sorted by.
		 *
		 * @param array<string,int> A map from report weight keys to their weight.
		 *
		 * @since TBD
		 *
		 */
		$report_weights_map = apply_filters( 'tec_events_custom_tables_v1_event_report_weights_map', $this->report_weights_map );

		$acc = 0;
		foreach ( $key_bits as $report_key => $bit ) {
			$report_key_weight = isset( $report_weights_map[ $report_key ] )
				? $report_weights_map[ $report_key ]
				: 0;
			$acc               += $bit * $report_key_weight;
		}
		$this->report_order_weight = $acc;

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
	 * @param string $status The status to set the migration state to, should be
	 *                       one of the `ALLOWED_STATUSES` constant.
	 *
	 * @return $this A reference to this object, for chaining.
	 *
	 * @throws Migration_Exception If the input status is not allowed.
	 */
	protected function set_status( $status ) {
		if ( ! in_array( $status, self::ALLOWED_STATUSES ) ) {
			throw new Migration_Exception( "Invalid status applied: $status" );
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
	 * @param string $strategy The slug of the applied migration strategy.
	 *
	 * @return $this A reference to this object, for chaining.
	 */
	public function add_strategy( $strategy ) {
		$this->data['strategies_applied'][] = $strategy;

		return $this;
	}

	/**
	 * Set the ticket provider, when an ET event.
	 *
	 * @since TBD
	 *
	 * @param string $tickets_provider The slug of the tickets provider, if any.
	 *
	 * @return $this A reference to this object, for chaining.
	 */
	public function set_tickets_provider( $tickets_provider ) {
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
		delete_post_meta( $this->source_event_post->ID, self::META_KEY_ORDER_WEIGHT );

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
	 * @param string $reason_key A reason key that is translated into the human-readable description of why the migration failed.
	 * @param array  $context    Context args that can be applied to the error message.
	 *
	 * @return Event_Report A reference to the Event Report object for the specific
	 *                      that is being processed.
	 */
	public function migration_failed( $reason_key, array $context = array() ) {
		// Track time immediately
		$this->set_end_timestamp();
		update_post_meta( $this->source_event_post->ID, self::META_KEY_MIGRATION_PHASE, self::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE );
		$this->unlock_event();

		// Expected exceptions have the message pre generated.
		if ( $reason_key !== 'expected-exception' ) {
			$text = tribe( String_Dictionary::class );
			array_unshift( $context, $text->get( "migration-error-k-$reason_key" ) );
		}

		// Parse message here, so we don't need to store the context.
		$message = call_user_func_array( 'sprintf', $context );

		return $this->set_error( $message )
		            ->set_status( self::STATUS_FAILURE )
		            ->save();
	}


	/**
	 * This will retrieve the translated text for the migration strategies being applied to this event.
	 *
	 * @since TBD
	 *
	 * @return string The translated migration strategy being applied.
	 */
	public function get_migration_strategy_text() {
		$text    = tribe( String_Dictionary::class );
		$message = '';
		foreach ( $this->strategies_applied as $action ) {
			switch ( $action ) {
				case Multi_Rule_Event_Migration_Strategy::get_slug():
					$message .= sprintf(
						esc_html( $text->get( "migration-prompt-strategy-$action" ) ),
						count( $this->created_events ),
						count( $this->created_events )
					);
					break;
				default:
					// Do we have language for this strategy?
					$output =  esc_html( $text->get( "migration-prompt-strategy-$action" ) ) ;
					if ( $output ) {
						$message .= $output;
					} else {
						$message .= esc_html( $text->get( "migration-prompt-unknown-strategy" ) );
					}
					break;
			}
		}

		return $message;
	}

	/**
	 * Will remove the lock from this Event.
	 *
	 * @since TBD
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
		$post_id = $this->source_event_post->ID;

		// @todo Not fully implemented. How do we detect tickets?

		$report_weights = [
			self::REPORT_KEY_FAILURE             => ! empty( $this->data['error'] ),
			self::REPORT_KEY_SINGLE_WITH_TICKETS => 0,
			self::REPORT_KEY_SINGLE_EVENT        => 1,
		];

		/**
		 * Filters the elements and bits associated with each report weight.
		 *
		 * @since TBD
		 *
		 * @param  array<string,int> A map from the report weight keys to their "bit", either 0 or 1.
		 * @param array<string,mixed>  $data The data of this migration report.
		 * @param int $post_id The post ID of the Event object of this report.
		 */
		$report_weights = apply_filters( 'tec_events_custom_tables_v1_event_report_element_weights', $report_weights, $this->data, $post_id );

		$this->set_report_order_weight( $report_weights );

		update_post_meta( $this->source_event_post->ID, self::META_KEY_REPORT_DATA, $this->data );
		update_post_meta( $this->source_event_post->ID, self::META_KEY_ORDER_WEIGHT, $this->report_order_weight );

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