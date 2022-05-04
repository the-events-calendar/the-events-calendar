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

use TEC\Events\Custom_Tables\V1\Migration\Admin\Phase_View_Renderer;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Progress_Modal;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;

/**
 * Class Ajax.
 *
 * @since   TBD
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
	const ACTION_REVERT = 'wp_ajax_tec_events_custom_tables_v1_migration_undo';

	/**
	 * The name of the action that will be used to create the nonce used by
	 * all requests that will start, cancel, undo or get a report about
	 * the migration process.
	 */
	const NONCE_ACTION = 'tec-ct1-upgrade';

	/**
	 * A reference to the current background processing handler.
	 *
	 * @since TBD
	 *
	 * @var Process
	 */
	private $process;
	/**
	 * A reference to the current progress modal handler.
	 *
	 * @since TBD
	 *
	 * @var Progress_Modal
	 */
	private $progress_modal;

	/**
	 * Ajax constructor.
	 *
	 * since TBD
	 *
	 * @param Process $process A reference to the current background processing handler.
	 */
	public function __construct( Process $process, Progress_Modal $progress_modal ) {
		$this->process        = $process;
		$this->progress_modal = $progress_modal;
	}

	/**
	 * Builds and sends the report in the format expected by the Migration UI JS
	 * component.
	 *
	 * @since TBD
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 *
	 */
	public function send_report( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}

	/**
	 * Builds the structured report HTML.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed>
	 */
	protected function get_report() {

		// What phase are we in?
		$state = tribe( State::class );
		$phase = $state->get_phase();

		/**
		 * Filters the Phase_View_Renderer being constructed for this phase.
		 *
		 * @since TBD
		 *
		 * @param Phase_View_Renderer A reference to the Phase_View_Renderer that should be used.
		 *                           Initially `null`.
		 * @param string $phase      The current phase we are in.
		 */
		$renderer = apply_filters( "tec_events_custom_tables_v1_migration_ajax_ui_renderer", null, $phase );
		if ( ! $renderer instanceof Phase_View_Renderer ) {

			$renderer = $this->get_renderer_for_phase( $phase );
		}

		return $renderer->compile();
	}

	/**
	 * Will construct the appropriate templates and nodes to be compiled, for this phase in the migration.
	 *
	 * @since TBD
	 *
	 * @param string $phase The current phase of the migration.
	 *
	 * @return Phase_View_Renderer The configured Phase_View_Renderer for this particular phase.
	 */
	protected function get_renderer_for_phase( $phase ) {
		// @todo flesh out pagination more
		$page        = - 1;
		$count       = 1000;
		$site_report = Site_Report::build();

		// Is the Maintenance Mode view requesting the report? This changes how we handle the views.
		$is_maintenance_mode = ! empty( $_GET["is_maintenance_mode"] );

		// Determine base directory for templates.
		$base_dir = $is_maintenance_mode ? "/maintenance-mode/phase" : "/phase";

		// Base template is phase name. Some phases might change.
		$template = $phase;

		// Then build the renderer.
		switch ( $phase ) {
			case State::PHASE_MIGRATION_COMPLETE:
			case State::PHASE_MIGRATION_PROMPT:
				$renderer = new Phase_View_Renderer( $phase,
					"$base_dir/$phase.php",
					[
						'state'         => tribe( State::class ),
						'report'        => $site_report,
						'event_reports' => $site_report->get_event_reports( $page, $count ),
						'text'          => tribe( String_Dictionary::class )
					]
				);
				$renderer->should_poll( false );
				break;
			case State::PHASE_CANCEL_IN_PROGRESS:
			case State::PHASE_REVERT_IN_PROGRESS:
				$renderer = new Phase_View_Renderer( $phase,
					"$base_dir/$phase.php",
					[
						'state'  => tribe( State::class ),
						'report' => $site_report,
						'text'   => tribe( String_Dictionary::class )
					]
				);
				$renderer->should_poll( true );
				break;
			case State::PHASE_MIGRATION_FAILURE_IN_PROGRESS:
				$template = State::PHASE_MIGRATION_IN_PROGRESS;
			case State::PHASE_PREVIEW_IN_PROGRESS:
			case State::PHASE_MIGRATION_IN_PROGRESS:
				$renderer = new Phase_View_Renderer( $phase,
					"$base_dir/$template.php"
				);
				$renderer->register_node( 'progress-bar',
					'.tec-ct1-upgrade-update-bar-container',
					'/partials/progress-bar.php',
					[
						'phase'  => $phase,
						'report' => $site_report,
						'text'   => tribe( String_Dictionary::class )
					]
				);
				$renderer->should_poll( true );
				break;
			default:
			case State::PHASE_CANCEL_COMPLETE:
			case State::PHASE_REVERT_COMPLETE:
			case State::PHASE_PREVIEW_PROMPT:
			case State::PHASE_MIGRATION_FAILURE_COMPLETE:
				// Maintenance mode and migration failure has templates for each phase.
				$specific_template = ( State::PHASE_MIGRATION_FAILURE_COMPLETE === $phase || $is_maintenance_mode );
				if ( $specific_template ) {
					$template = $phase;
				} else {
					// Other phases / views have this specific template.
					$template = State::PHASE_PREVIEW_PROMPT;
				}
				$renderer = new Phase_View_Renderer( $phase,
					"$base_dir/$template.php",
					[
						'state'         => tribe( State::class ),
						'report'        => $site_report,
						'text'          => tribe( String_Dictionary::class ),
						'event_reports' => $site_report->get_event_reports( $page, $count, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE )
					]
				);
				$renderer->should_poll( false );
				break;
		}

		// Log our poll status
		do_action( 'tribe_log', 'debug', 'Ajax: Migration report poll renderer', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'report' => $site_report,
		] );

		return $renderer;
	}

	/**
	 * Handles the request from the Admin UI to start the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function start_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		$dry_run = ! empty( $_REQUEST['tec_events_custom_tables_v1_migration_dry_run'] );
		// Log our start
		do_action( 'tribe_log', 'debug', 'Ajax: Start migration', [
			'source'  => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'dry_run' => $dry_run,
		] );
		$this->process->start( $dry_run );

		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Handles the request from the Admin UI to cancel the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 *
	 */
	public function cancel_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );
		// Log our start
		do_action( 'tribe_log', 'debug', 'Ajax: Cancel migration', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
		] );
		// A cancel action is identical to an undo.
		$this->process->cancel();
		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}

	/**
	 * Handles the request from the Admin UI to undo the migration and returns
	 * a first report about its progress.
	 *
	 * @since TBD
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function revert_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );
		// Log our start
		do_action( 'tribe_log', 'debug', 'Ajax: Undo migration', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
		] );
		$this->process->revert();
		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}
}