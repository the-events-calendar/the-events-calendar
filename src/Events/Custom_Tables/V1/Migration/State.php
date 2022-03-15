<?php
/**
 * Provides an API to read and write the Migration state.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use Tribe__Utils__Array as Arr;

/**
 * Class State.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class State {

	/**
	 * @var string First step.
	 */
	const PHASE_PREVIEW_PROMPT = 'preview-prompt';
	/**
	 * @var string Second step.
	 */
	const PHASE_PREVIEW_IN_PROGRESS = 'preview-in-progress';
	/**
	 * @var string Third step.
	 */
	const PHASE_MIGRATION_PROMPT = 'migration-prompt';
	/**
	 * @var string Fourth step.
	 */
	const PHASE_MIGRATION_IN_PROGRESS = 'migration-in-progress';
	/**
	 * @var string Final step.
	 */
	const PHASE_MIGRATION_COMPLETE = 'migration-complete';
	const PHASE_CANCELLATION_IN_PROGRESS = 'cancellation-in-progress';
	const PHASE_UNDO_IN_PROGRESS = 'undo-in-progress';
	const STATE_OPTION_KEY = 'ct1_migration_state';
	/**
	 * An array of default data the migration state will be hydrated with if no
	 * corresponding option is set.
	 *
	 * @since TBD
	 *
	 * @var array<string,mixed>
	 */
	protected $default_data = [
		// @todo set this up.
	];
	/**
	 * An array that will contain the migration state as hydrated from the database values,
	 * or from the default values.
	 *
	 * @since TBD
	 *
	 * @var array<string,mixed>
	 */
	private $data = [];


	public function __construct() {
		// @todo remove this data mock.
		$this->default_data = [
			'complete_timestamp' => strtotime( 'yesterday 4pm' ),
			'phase' => self::PHASE_PREVIEW_PROMPT,
		];

		$this->data = tribe_get_option( self::STATE_OPTION_KEY, $this->default_data );
	}

	/**
	 * Returns whether the migration is completed or not.
	 *
	 * @since TBD
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
	 * Returns whether the migration process can be undone or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration process can be undone or not.
	 */
	public function can_be_undone() {
		return false;
	}

	/**
	 * Returns whether the migration is running or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration is running or not.
	 */
	public function is_running() {
		// @todo This what we want to check here...? Being used in Site_Report
		$in_progress_states = [
			self::PHASE_MIGRATION_IN_PROGRESS,
			self::PHASE_PREVIEW_IN_PROGRESS,
			self::PHASE_UNDO_IN_PROGRESS,
		];

		return in_array( $this->get_phase(), $in_progress_states );
	}

	/**
	 * Returns whether the migration is required or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration is required or not.
	 */
	public function is_required() {
		return true;
	}

	/**
	 * Returns the current migration phase the site is in.
	 *
	 * @since TBD
	 *
	 * @return string The current migration phase the site is in.
	 */
	public function get_phase() {
		return $this->data['phase'];
	}

	/**
	 * Returns a value for a specific data key or nested data key.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 */
	public function save() {
		tribe_update_option( self::STATE_OPTION_KEY, $this->data );
	}
}