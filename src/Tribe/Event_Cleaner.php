<?php

/**
 * Class Event_Cleaner
 */
class Tribe__Events__Event_Cleaner {

	/**
	 * @var $scheduler
	 */
	private $scheduler;

	/**
	 * The option name to move old events to trash.
	 * @var $trash_events
	 */
	public $trash_events = 'trash-past-events';

	/**
	 * The option name to permanently delete old events.
	 * @var $delete_events
	 */
	public $delete_events = 'delete-past-events';

	public function __construct( Tribe__Events__Event_Scheduler $scheduler = null ) {
		$this->scheduler = $scheduler ? $scheduler : new Tribe__Events__Event_Scheduler();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the $trash_events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function move_old_events_to_trash( array $old_value, array $new_value ) {
		$old_value = empty( $old_value[ $this->trash_events ] ) ? null : $old_value[ $this->trash_events ];
		$new_value = empty( $new_value[ $this->trash_events ] ) ? null : $new_value[ $this->trash_events ];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( null === $new_value ) {
			$this->scheduler->trash_clear_scheduled_task();

			return;
		}

		$this->scheduler->trash_set_new_date( $new_value );
		$this->scheduler->move_old_events_to_trash();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the $delete_events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since TBD
	 */
	public function permanently_delete_old_events( array $old_value, array $new_value ) {
		$old_value = empty( $old_value[ $this->delete_events ] ) ? null : $old_value[ $this->delete_events ];
		$new_value = empty( $new_value[ $this->delete_events ] ) ? null : $new_value[ $this->delete_events ];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( null === $new_value ) {
			$this->scheduler->delete_clear_scheduled_task();

			return;
		}

		$this->scheduler->delete_set_new_date( $new_value );
		$this->scheduler->permanently_delete_old_events();
	}
}