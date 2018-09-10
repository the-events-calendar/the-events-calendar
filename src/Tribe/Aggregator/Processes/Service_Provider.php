<?php

/**
 * Class Tribe__Events__Aggregator__Processes__Service_Provider
 *
 * @since 4.6.16
 */
class Tribe__Events__Aggregator__Processes__Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.6.16
	 */
	public function register() {
		tribe_register( 'events-aggregator.record-items', 'Tribe__Events__Aggregator__Record__Items' );
		tribe_register( 'events-aggregator.processes.import-events', 'Tribe__Events__Aggregator__Processes__Import_Events' );
		tribe_singleton( 'events-aggregator.queue-control', 'Tribe__Events__Aggregator__Processes__Queue_Control' );

		add_filter( 'tribe_process_queues', array( $this, 'filter_tribe_process_queues' ) );
		add_filter( 'tribe_settings_save_field_value', array(
			$this,
			'filter_tribe_settings_save_field_value',
		), 10, 2 );

		$this->handle_clear_request();
		$this->handle_clear_result();
	}

	/**
	 * Registers the event import background process.
	 *
	 * @since 4.6.16
	 *
	 * @param array $queues
	 *
	 * @return array
	 */
	public function filter_tribe_process_queues( array $queues = array() ) {
		$queues[] = 'Tribe__Events__Aggregator__Processes__Import_Events';

		return $queues;
	}

	/**
	 * Handles requests to clear queue processes.
	 *
	 * @since 4.6.23
	 */
	protected function handle_clear_request() {
		if (
			tribe_get_request_var( Tribe__Events__Aggregator__Processes__Queue_Control::CLEAR_PROCESSES, false )
			&& is_admin()
			&& current_user_can( 'manage_options' )
		) {
			$clear_queues = tribe_callback( 'events-aggregator.queue-control', 'clear_queues_and_redirect' );
			add_action( 'admin_init', $clear_queues, 9, 0 );
		}
	}

	/**
	 * Handles requests to show the queue processes clearing results.
	 *
	 * @since 4.6.23
	 */
	protected function handle_clear_result() {
		// `0` removed queue processes is still something we would want to notify users about
		$clear_result = tribe_get_request_var( Tribe__Events__Aggregator__Processes__Queue_Control::CLEAR_RESULT, false );

		if ( false !== $clear_result ) {
			$message = 0 === (int) $clear_result
				? sprintf( esc_html__( 'No asynchronous queue processes to clear.', 'the-events-calendar' ), $clear_result )
				: sprintf( esc_html(
					_n(
						'Successfully stopped and cleared 1 asynchronous queue process.',
						'Successfully stopped and cleared %d asynchronous queue processes.',
						(int) $clear_result,
						'the-events-calendar'
					)
				), $clear_result );

			tribe_notice(
				'ea-clear-queues-result',
				'<p>' . $message . '</p>',
				array( 'type' => 'success' )
			);
		}
	}

	/**
	 * Filters the save operation of the process system to watch for system switches while there are
	 * running asynchronous queues.
	 *
	 * While going from cron-based to async will work, due to underlying system, the reverse will not.
	 * To prevent this from creating issues all asynchronous queue processes will be cleared before
	 * the switch.
	 *
	 * @since 4.6.23
	 *
	 * @param string $value    The new setting value.
	 * @param string $field_id The setting field id.
	 *
	 * @return string The new setting value, unmodified.
	 */
	public function filter_tribe_settings_save_field_value( $value, $field_id ) {
		$option = 'tribe_aggregator_import_process_system';
		if ( $field_id !== $option ) {
			return $value;
		}

		/** @var Tribe__Events__Aggregator__Processes__Queue_Control $control */
		$control = tribe( 'events-aggregator.queue-control' );

		$old_value = tribe_get_option( $option, false );

		if ( $old_value === 'async' && $value !== 'async' ) {
			$control->clear_queues();
		}

		return $value;
	}
}
