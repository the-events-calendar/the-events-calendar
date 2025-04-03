<?php
/**
 * Class Event_Cleaner
 *
 * @since 4.6.13
 */
class Tribe__Events__Event_Cleaner {

	/**
	 * @var $scheduler
	 */
	private $scheduler;

	/**
	 * The option name to move old events to trash.
	 *
	 * @var $key_trash_events
	 *
	 * @since 4.6.13
	 */
	public $key_trash_events = 'trash-past-events';

	/**
	 * The option name to permanently delete old events.
	 *
	 * @var $key_delete_events
	 *
	 * @since 4.6.13
	 */
	public $key_delete_events = 'delete-past-events';

	public function __construct( Tribe__Events__Event_Cleaner_Scheduler $scheduler = null ) {
		$this->scheduler = $scheduler ? $scheduler : new Tribe__Events__Event_Cleaner_Scheduler();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the $key_trash_events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array<string,mixed>|null $old_value The old value of the `tribe_events_calendar_options` option.
	 * @param array<string,mixed>|null $new_value The old value of the `tribe_events_calendar_options` option.
	 *
	 * @since 4.6.13
	 * @since 5.3.0 Loosen the type-checking to avoid errors during option updates.
	 */
	public function move_old_events_to_trash( $old_value = [], $new_value = [] ) {
		$old_value         = (array) $old_value;
		$new_value         = (array) $new_value;
		$old_trash_setting = empty( $old_value[ $this->key_trash_events ] ) ? null : $old_value[ $this->key_trash_events ];
		$new_trash_setting = empty( $new_value[ $this->key_trash_events ] ) ? null : $new_value[ $this->key_trash_events ];

		if ( $new_trash_setting == $old_trash_setting ) {
			return;
		}

		if ( null === $new_trash_setting ) {
			$this->scheduler->trash_clear_scheduled_task();

			return;
		}

		$this->scheduler->set_trash_new_date( $new_trash_setting );
		$this->scheduler->move_old_events_to_trash();
	}

	/**
	 * Receives the existing value and the new value (modified by user) for the $key_delete_events option,
	 * compares them and runs the scheduler if the conditions are satisfied.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 *
	 * @since 4.6.13
	 */
	public function permanently_delete_old_events( array $old_value, array $new_value ) {
		$old_value = empty( $old_value[ $this->key_delete_events ] ) ? null : $old_value[ $this->key_delete_events ];
		$new_value = empty( $new_value[ $this->key_delete_events ] ) ? null : $new_value[ $this->key_delete_events ];

		if ( $new_value == $old_value ) {
			return;
		}

		if ( null === $new_value ) {
			$this->scheduler->delete_clear_scheduled_task();

			return;
		}

		$this->scheduler->set_delete_new_date( $new_value );
		$this->scheduler->permanently_delete_old_events();
	}
}
