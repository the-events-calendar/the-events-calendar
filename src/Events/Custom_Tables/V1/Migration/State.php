<?php
/**
 * Provides an API to read and write the Migration state.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class State.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class State {

	/**
	 * Returns whether the migration is completed or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration is completed or not.
	 */
	public function is_completed() {
		return false;
	}

	/**
	 * Returns whether the migration process can be undone or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration process can be undone or not.
	 */
	public function can_be_undone(  ) {
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
		return false;
	}
}