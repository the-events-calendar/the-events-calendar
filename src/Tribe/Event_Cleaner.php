<?php
/**
 * Class Event_Cleaner
 */
class Tribe__Events__Event_Cleaner {

	/**
	 * @var Tribe__Events__Event_Cleaner
	 */
	protected static $instance;
	private $scheduler;

	/**
	 * @return Tribe__Events__Event_Cleaner
	 */
	public static function instance() {
		if ( self::$instance == false ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct( Tribe__Events__Event_Scheduler $scheduler = null ) {
		$this->scheduler = $scheduler ? $scheduler : new Tribe__Events__Event_Scheduler();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the trashPastEvents option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function move_old_events_to_trash( array $old_value, array $new_value ) {
		$old_value = empty( $old_value['trashPastEvents'] ) ? null : $old_value['trashPastEvents'];
		$new_value = empty( $new_value['trashPastEvents'] ) ? null : $new_value['trashPastEvents'];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( $new_value == null ) {
			$this->scheduler->trash_clear_scheduled_task();
		}

		$this->scheduler->trash_set_new_date( $new_value );
		$this->scheduler->move_old_events_to_trash();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the deletePastEvents option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function permanently_delete_old_events( array $old_value, array $new_value ) {
		$old_value = empty( $old_value['deletePastEvents'] ) ? null : $old_value['deletePastEvents'];
		$new_value = empty( $new_value['deletePastEvents'] ) ? null : $new_value['deletePastEvents'];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( $new_value == null ) {
			$this->scheduler->delete_clear_scheduled_task();
		}

		$this->scheduler->delete_set_new_date( $new_value );
		$this->scheduler->permanently_delete_old_events();
	}
}