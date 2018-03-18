<?php
/**
 * Class Event_Cleaner
 */
class Tribe__Events__Event_Cleaner {

	/**
	 * @var $scheduler
	 */
	private $scheduler;

	public function __construct( Tribe__Events__Event_Scheduler $scheduler = null ) {
		$this->scheduler = $scheduler ? $scheduler : new Tribe__Events__Event_Scheduler();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the trash-past-events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function move_old_events_to_trash( array $old_value, array $new_value ) {
		$old_value = empty( $old_value['trash-past-events'] ) ? null : $old_value['trash-past-events'];
		$new_value = empty( $new_value['trash-past-events'] ) ? null : $new_value['trash-past-events'];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( $new_value === null ) {
			$this->scheduler->trash_clear_scheduled_task();

			return;
		}

		$this->scheduler->trash_set_new_date( $new_value );
		$this->scheduler->move_old_events_to_trash();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the delete-past-events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function permanently_delete_old_events( array $old_value, array $new_value ) {
		$old_value = empty( $old_value['delete-past-events'] ) ? null : $old_value['delete-past-events'];
		$new_value = empty( $new_value['delete-past-events'] ) ? null : $new_value['delete-past-events'];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( $new_value === null ) {
			$this->scheduler->delete_clear_scheduled_task();

			return;
		}

		$this->scheduler->delete_set_new_date( $new_value );
		$this->scheduler->permanently_delete_old_events();
	}
}