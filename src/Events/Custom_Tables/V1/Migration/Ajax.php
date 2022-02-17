<?php
/**
 * Handles the migration UI Ajax requests.
 *
 * While, technically, Action Scheduler code will work using AJAX, this
 * handler will concentrate on AJAX requests from the migraiton UI, not
 * from Action Scheduler.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Ajax.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Ajax {

	/**
	 * The full name of the action that will be fired following a migration UI
	 * request for a report.
	 *
	 * @since TBD
	 */
	const ACTION_REPORT = 'wp_ajax_tec_events_custom_tables_v1_migration_report';

	/**
	 * The full name of the action that will be fired following a request from
	 * the migration UI to start the migration.
	 *
	 * @since TBD
	 */
	const ACTION_START = 'wp_ajax_tec_events_custom_tables_v1_migration_start';

	/**
	 * The full name of the action that will be fired following a request from
	 * the migration UI to cancel the migration.
	 */
	const ACTION_CANCEL = 'wp_ajax_tec_events_custom_tables_v1_migration_cancel';

	/**
	 * The full name of the action that will be fired following a request from
	 * the migration UI to undo the migration.
	 */
	const ACTION_UNDO = 'wp_ajax_tec_events_custom_tables_v1_migration_undo';

	/**
	 * A reference to the current reports repository implementation.
	 *
	 * @since TBD
	 *
	 * @var Reports
	 */
	private $reports;

	/**
	 * A reference to the current background processing handler.
	 *
	 * @since TBD
	 *
	 * @var Process
	 */
	private $process;

	/**
	 * Ajax constructor.
	 *
	 * since TBD
	 *
	 * @param Reports $reports A reference to the current reports repository implementation.
	 * @param Process $process A reference to the current background processing handler.
	 */
	public function __construct( Reports $reports, Process $process ) {
		$this->reports = $reports;
		$this->process = $process;
	}

	/**
	 * Builds and sends the report in the format expected by the Migration UI JS
	 * component.
	 *
	 * @since TBD
	 *
	 * @return Site_Report The generated report.
	 */
	public function get_report( ) {
		$report = $this->reports->build();

		return $report;
	}

	/**
	 * Sends the report as JSON data.
	 *
	 * @since TBD
	 */
	public function send_report() {
		wp_send_json( $this->get_report() );
	}

	/**
	 * Handles the request from the Admin UI to start the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A report about the migration start process.
	 */
	public function start_migration( $echo = true ) {
		$report = $this->reports->build();
		$this->process->start();

		if ( $echo ) {
			wp_send_json( $report );
		}

		return $report;
	}

	/**
	 * Handles the request from the Admin UI to cancel the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A report about the migration cancel process.
	 */
	public function cancel_migration( $echo = true ) {
		$report = $this->reports->build();
		$this->process->cancel();

		if ( $echo ) {
			wp_send_json( $report );
		}

		return $report;
	}

	/**
	 * Handles the request from the Admin UI to undo the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A report about the migration undo process.
	 */
	public function undo_migration( $echo = true ) {
		$report = $this->reports->build();
		$this->process->undo();

		if ( $echo ) {
			wp_send_json( $report );
		}

		return $report;
	}
}