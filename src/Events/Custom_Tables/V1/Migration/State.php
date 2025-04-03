<?php
/**
 * Provides an API to read and write the Migration state.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use Tribe__Utils__Array as Arr;

/**
 * Class State.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class State {

	/**
	 * Indicates the migration failed and is enacting remediation steps.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_FAILURE_IN_PROGRESS = 'migration-failed-in-progress';

	/**
	 * Indicates the migration failed and remediation steps are finished.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_FAILURE_COMPLETE = 'migration-failed-complete';

	/**
	 * Indicates the migration is not required at all.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_NOT_REQUIRED = 'migration-not-required';

	/**
	 * Indicates the migration cancel has completed.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_CANCEL_COMPLETE = 'cancel-complete';

	/**
	 * Indicates the migration revert has completed.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_REVERT_COMPLETE = 'revert-complete';

	/**
	 * Indicates the migration preview is ready to start.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_PREVIEW_PROMPT = 'preview-prompt';

	/**
	 * Indicates the migration preview is in progress.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_PREVIEW_IN_PROGRESS = 'preview-in-progress';

	/**
	 * Indicates the migration is ready to start and waiting for user confirmation.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_PROMPT = 'migration-prompt';

	/**
	 * Indicates the migration is in progress.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_IN_PROGRESS = 'migration-in-progress';

	/**
	 * Indicates the migration is complete.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_MIGRATION_COMPLETE = 'migration-complete';

	/**
	 * Indicates a cancel migration is in progress.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_CANCEL_IN_PROGRESS = 'cancel-in-progress';

	/**
	 * Indicates a revert migration is in progress.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const PHASE_REVERT_IN_PROGRESS = 'revert-in-progress';

	/**
	 * The key used in the calendar options to store the current state.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const STATE_OPTION_KEY = 'tec_ct1_migration_state';

	/**
	 * An array of default data the migration state will be hydrated with if no
	 * corresponding option is set.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $default_data = [
		'complete_timestamp'  => null,
		'phase'               => null,
		'preview_unsupported' => false,
	];

	/**
	 * An array that will contain the migration state as hydrated from the database values,
	 * or from the default values.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $data = [];

	/**
	 * A reference to the Migration Events repository handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * State constructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct( Events $events ) {
		$option_data  = (array) get_option( self::STATE_OPTION_KEY, $this->default_data );
		$this->data   = wp_parse_args( $option_data, $this->default_data );
		$this->events = $events;
	}

	/**
	 * Returns whether the migration is completed or not.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the migration is completed or not.
	 */
	public function is_completed() {
		// @todo This what we want to check here...? Being used in Site_Report
		$completed_states = [
			self::PHASE_MIGRATION_COMPLETE,
			self::PHASE_MIGRATION_PROMPT, // AKA preview complete
		];

		return in_array( $this->get_phase(), $completed_states );
	}

	/**
	 * Returns whether the migration has been performed and has been successfully completed.
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 */
	public function is_migrated() {
		return in_array( $this->get_phase(), [
			static::PHASE_MIGRATION_NOT_REQUIRED,
			static::PHASE_MIGRATION_COMPLETE
		] );
	}

	/**
	 * Check if we should allow a reverse migration action to occur. There is an expiration period of time for how long
	 * we allow someone to reverse.
	 *
	 * @since 6.0.0
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function should_allow_reverse_migration() {
		// If we have not migrated yet, don't block reversing.
		if ( ! $this->is_migrated() ) {

			return true;
		}

		// Missing our timestamp for some reason?
		if ( ! $this->get( 'complete_timestamp' ) ) {

			return true;
		}

		$current_date   = ( new \DateTime( 'now', wp_timezone() ) );
		$date_completed = ( new \DateTime( 'now', wp_timezone() ) )->setTimestamp( $this->get( 'complete_timestamp' ) );
		// 8 day old expiration
		$expires_in_seconds = 8 * 24 * 60 * 60;

		// If time for our reverse migration has expired
		return ( $current_date->format( 'U' ) - $expires_in_seconds ) < $date_completed->format( 'U' );
	}

	/**
	 * Returns whether there is work being done. Does not only check for an in progress migration.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether some worker actions are in flight.
	 */
	public function is_running() {
		$states = [
			self::PHASE_MIGRATION_IN_PROGRESS,
			self::PHASE_PREVIEW_IN_PROGRESS,
			self::PHASE_CANCEL_IN_PROGRESS,
			self::PHASE_REVERT_IN_PROGRESS,
			self::PHASE_MIGRATION_FAILURE_IN_PROGRESS,
		];

		return in_array( $this->get_phase(), $states, true );
	}

	/**
	 * Checks the phases we want to lock out access to certain features.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether we should lock the site for maintenance mode.
	 */
	public function should_lock_for_maintenance() {
		$states = [
			self::PHASE_MIGRATION_IN_PROGRESS,
			self::PHASE_CANCEL_IN_PROGRESS,
			self::PHASE_REVERT_IN_PROGRESS,
			self::PHASE_MIGRATION_FAILURE_IN_PROGRESS,
		];

		return in_array( $this->get_phase(), $states, true );
	}

	/**
	 * Returns whether the migration is required or not.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the migration is required or not.
	 */
	public function is_required() {
		$phase = $this->get_phase();

		if ( in_array( $phase, [ self::PHASE_MIGRATION_NOT_REQUIRED, self::PHASE_MIGRATION_COMPLETE ], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the current migration phase the site is in.
	 *
	 * @since 6.0.0
	 *
	 * @return string The current migration phase the site is in.
	 */
	public function get_phase() {
		return $this->data['phase'];
	}

	/**
	 * Returns a value for a specific data key or nested data key.
	 *
	 * @since 6.0.0
	 *
	 * @param string ...$keys A set of one or more indexes to get the
	 *                        value of.
	 *
	 * @return mixed|null The value of the requested index, or nested indexed, or `null`
	 *                    if not defined.
	 */
	public function get( ...$keys ) {
		return Arr::get( $this->data, $keys, null );
	}

	/**
	 * Set a value for the migration state.
	 *
	 * @since 6.0.0
	 *
	 * @param ...$keys string The key(s) of the value to store.
	 * @param $value   mixed The value to store.
	 */
	public function set( ...$keys ) {
		$value      = array_pop( $keys );
		$this->data = Arr::set( $this->data, $keys, $value );
	}

	/**
	 * Save our current state.
	 *
	 * @since 6.0.0
	 */
	public function save() {
		// This will return `false` on failure to save and if the value is the same, not indicative of a failure.
		update_option( self::STATE_OPTION_KEY, $this->data );
	}

	/**
	 * Returns whether the current phase is a migration dry-run or not.
	 *
	 * @since 6.0.2
	 *
	 * @return bool Whether the current phase is a migration dry-run or not.
	 */
	public function is_dry_run(): bool {
		$phase = $this->get_phase();
		switch($phase) {
			case State::PHASE_REVERT_COMPLETE:
			case State::PHASE_CANCEL_COMPLETE:
			case State::PHASE_PREVIEW_PROMPT:
			case State::PHASE_PREVIEW_IN_PROGRESS:
			case State::PHASE_MIGRATION_FAILURE_COMPLETE:
				return true;
			default:
				return false;
		}
	}
}