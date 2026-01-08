<?php
/**
 * Handles the migration UI Ajax requests.
 *
 * While, technically, Action Scheduler code will work using AJAX, this
 * handler will concentrate on AJAX requests from the migraiton UI, not
 * from Action Scheduler.
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Admin\Phase_View_Renderer;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report_Categories;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;

/**
 * Class Ajax.
 *
 * @since 6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Ajax {

	/**
	 * The full name of the action that will be fired following a migration UI
	 * request for a paginated batch of events.
	 */
	const ACTION_PAGINATE_EVENTS = 'wp_ajax_tec_events_custom_tables_v1_migration_event_pagination';
	/**
	 * The full name of the action that will be fired following a migration UI
	 * request for a report.
	 *
	 * @since 6.0.0
	 */
	const ACTION_REPORT = 'wp_ajax_tec_events_custom_tables_v1_migration_report';

	/**
	 * The full name of the action that will be fired following a request from
	 * the migration UI to start the migration.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @var Process
	 */
	private $process;

	/**
	 * @since 6.0.0
	 *
	 * @var Site_Report
	 */
	private $site_report;

	/**
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events_repository;

	/**
	 * @since 6.0.0
	 *
	 * @var State
	 */
	private $state;

	/**
	 * @since 6.0.0
	 *
	 * @var String_Dictionary
	 */
	private $text;

	/**
	 * Ajax constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Process           $process           The process master.
	 * @param Events            $events_repository The migration events repository.
	 * @param String_Dictionary $text              The string translations object.
	 * @param State             $state             The migration state.
	 */
	public function __construct( Process $process, Events $events_repository, String_Dictionary $text, State $state ) {
		$this->process           = $process;
		$this->site_report       = Site_Report::build();
		$this->events_repository = $events_repository;
		$this->state             = $state;
		$this->text              = $text;
	}

	/**
	 * Builds and sends the report in the format expected by the Migration UI JS
	 * component.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function send_report( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'You do not have permission to view this page.', 'the-events-calendar' ),
				]
			);

			return;
		}

		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}

	/**
	 * Requests a batch of paginated events.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $echo
	 *
	 * @return false|string|void
	 */
	public function paginate_events( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'You do not have permission to view this page.', 'the-events-calendar' ),
				]
			);

			return;
		}

		$response = $this->get_paginated_response( $_GET['page'], 25, ! empty( $_GET['upcoming'] ), $_GET['report_category'] );
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}

	/**
	 * Responds to the paginated requests.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $page     The page of results we are fetching.
	 * @param int    $count    The number of events we are requesting.
	 * @param bool   $upcoming If we want upcoming or past events.
	 * @param string $category The category of event reports we are searching.
	 *
	 * @return mixed[]
	 */
	public function get_paginated_response( $page, $count, $upcoming, $category ) {
		$phase = $this->state->get_phase();

		$filter        = [
			Event_Report::META_KEY_MIGRATION_CATEGORY => $category,
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => $upcoming,
		];
		$event_details = $this->get_events_and_has_more( $page, $count, $filter );
		$renderer_args = [
			'state'         => $this->state,
			'report'        => $this->site_report,
			'text'          => $this->text,
			'event_reports' => $event_details['event_reports'],
		];

		$renderer = new Phase_View_Renderer(
			$phase . '-paginated',
			'/partials/event-items.php',
			$renderer_args,
			[
				'has_more' => $event_details['has_more'],
				'append'   => $upcoming,
				'prepend'  => ! $upcoming,
			]
		);

		return $renderer->compile();
	}


	/**
	 * Builds the structured report HTML.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string, mixed>
	 */
	protected function get_report() {
		// What phase are we in?
		$state = $this->state;
		$phase = $state->get_phase();

		// Short-circuit if migration is not required.
		if ( $phase === State::PHASE_MIGRATION_NOT_REQUIRED ) {
			return [
				'key'   => 'stop',
				'html'  => '',
				'nodes' => [],
				'poll'  => false,
			];
		}

		$renderer = $this->get_renderer_for_phase( $phase );

		return $renderer->compile();
	}


	/**
	 * Will fetch event reports for a particular filter, and check if there are more to request for that filter.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $page  Which page we are on.
	 * @param int    $count How many we want.
	 * @param $filter
	 *
	 * @return array{ has_more:bool, event_reports:array<Event_Report> }
	 */
	protected function get_events_and_has_more( $page, $count, $filter ) {
		$event_reports = $this->site_report->get_event_reports(
			$page,
			$count,
			$filter
		);
		// Did we even have enough to fill our request?
		if ( count( $event_reports ) < $count ) {
			$has_more = false;
		} else {
			// If we did, lets see if there is another page.
			$has_more = ! empty(
				$this->events_repository->get_events_migrated(
					$page + 1,
					$count,
					$filter
				) 
			);
		}

		return [
			'has_more'      => $has_more,
			'event_reports' => $event_reports,
		];
	}

	/**
	 * Construct the query args for the primary renderer template (not used for the node templates).
	 *
	 * @since 6.0.0
	 *
	 * @param string $phase The current phase.
	 *
	 * @return array<string,mixed> The primary renderer template args.
	 */
	protected function get_renderer_args( $phase ) {
		$count         = 25;
		$renderer_args = [
			'state'  => $this->state,
			'report' => $this->site_report,
			'text'   => $this->text,
		];

		switch ( $phase ) {
			case State::PHASE_MIGRATION_COMPLETE:
			case State::PHASE_MIGRATION_PROMPT:
				$renderer_args['preview_unsupported'] = (bool) $this->state->get( 'preview_unsupported' );
				if ( $this->site_report->has_errors ) {
					$filter                         = [
						Event_Report::META_KEY_MIGRATION_PHASE => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE,
					];
					$renderer_args['event_reports'] = $this->site_report->get_event_reports(
						1,
						$count,
						$filter
					);
				} else {
					// This should only handle first render - pagination should be handled elsewhere.
					$event_categories = tribe( Event_Report_Categories::class )->get_categories();
					foreach ( $event_categories as $i => $category ) {
						$upcoming_filter         = [
							Event_Report::META_KEY_MIGRATION_CATEGORY => $category['key'],
							Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
							'upcoming'                                => true,
						];
						$past_filter             = $upcoming_filter;
						$past_filter['upcoming'] = false;
						$upcoming_events         = $this->get_events_and_has_more( 1, $count, $upcoming_filter );
						$past_events             = $this->get_events_and_has_more( 1, $count, $past_filter );
						// Grab upcoming if any, else grab past events.
						$event_categories[ $i ] ['event_reports'] = empty( $upcoming_events['event_reports'] )
							? $past_events['event_reports']
							: $upcoming_events['event_reports'];

						// No reports? Skip this category.
						if ( empty( $event_categories[ $i ] ['event_reports'] ) ) {
							unset( $event_categories[ $i ] );
							continue;
						}

						$event_categories[ $i ]['has_upcoming']        = $upcoming_events['has_more'];
						$event_categories[ $i ]['upcoming_start_page'] = $upcoming_events['has_more'] ? 2 : 1;
						$event_categories[ $i ]['past_start_page']     = $past_events['has_more'] ? 2 : 1;
						// By default we show upcoming, but will fall back to past events if no upcoming.
						// We need to validate when we do this flip and confirm which we are showing and when we should show a "show more" button.
						$event_categories[ $i ]['has_past'] = ! empty( $upcoming_events['event_reports'] ) && ! empty( $past_events['event_reports'] );
					}
					$renderer_args['event_categories'] = $event_categories;
				}
				break;
			case State::PHASE_CANCEL_COMPLETE:
			case State::PHASE_REVERT_COMPLETE:
			case State::PHASE_PREVIEW_PROMPT:
			case State::PHASE_MIGRATION_FAILURE_COMPLETE:
				$renderer_args['event_reports'] = $this->site_report->get_event_reports(
					1,
					$count,
					[ Event_Report::META_KEY_MIGRATION_PHASE => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE ]
				);
				break;
		}

		return $renderer_args;
	}

	/**
	 * Based on the current phase, find the correct template file for the renderer.
	 *
	 * @since 6.0.0
	 *
	 * @param string $phase The current phase.
	 *
	 * @return string|void The primary template file to load for this phase.
	 */
	protected function get_renderer_template( $phase ) {
		$phase ??= State::PHASE_PREVIEW_PROMPT;

		// Is the Maintenance Mode view requesting the report? This changes how we handle the views.
		$is_maintenance_mode = ! empty( $_GET['is_maintenance_mode'] );

		// Determine base directory for templates.
		$base_dir = $is_maintenance_mode ? '/maintenance-mode/phase' : '/phase';

		// Base template is phase name. Some phases might change it with other logic.
		$template = $phase;

		switch ( $phase ) {
			case State::PHASE_MIGRATION_FAILURE_IN_PROGRESS:
				$template = State::PHASE_MIGRATION_IN_PROGRESS;
			case State::PHASE_PREVIEW_IN_PROGRESS:
			case State::PHASE_MIGRATION_IN_PROGRESS:
				return "$base_dir/$template.php";
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

				return "$base_dir/$template.php";
			default:
				return "$base_dir/$template.php";
		}
	}

	/**
	 * Determines if the frontend should poll for updates from the backend.
	 *
	 * @since 6.0.0
	 *
	 * @param string $phase The current phase.
	 *
	 * @return bool Whether the frontend should continue polling.
	 */
	protected function should_renderer_poll( $phase ) {
		switch ( $phase ) {
			case State::PHASE_MIGRATION_COMPLETE:
			case State::PHASE_MIGRATION_PROMPT:
			case State::PHASE_CANCEL_COMPLETE:
			case State::PHASE_REVERT_COMPLETE:
			case State::PHASE_PREVIEW_PROMPT:
			case State::PHASE_MIGRATION_FAILURE_COMPLETE:
				return false;
			default:
				return true;
		}
	}

	/**
	 * Will construct the appropriate templates and nodes to be compiled, for this phase in the migration.
	 *
	 * @since 6.0.0
	 *
	 * @param string $phase The current phase of the migration.
	 *
	 * @return Phase_View_Renderer The configured Phase_View_Renderer for this particular phase.
	 */
	public function get_renderer_for_phase( $phase ) {

		/**
		 * Filters the Phase_View_Renderer being constructed for this phase.
		 *
		 * @since 6.0.0
		 *
		 * @param Phase_View_Renderer A reference to the Phase_View_Renderer that should be used.
		 *                           Initially `null`.
		 * @param string $phase      The current phase we are in.
		 */
		$renderer = apply_filters( 'tec_events_custom_tables_v1_migration_ajax_ui_renderer', null, $phase );
		if ( $renderer instanceof Phase_View_Renderer ) {

			return $renderer;
		}

		$phase ??= State::PHASE_PREVIEW_PROMPT;

		// Get the args.
		$renderer_args = $this->get_renderer_args( $phase );
		$template      = $this->get_renderer_template( $phase );
		$renderer      = new Phase_View_Renderer( $phase, $template, $renderer_args );
		$renderer->should_poll( $this->should_renderer_poll( $phase ) );

		switch ( $phase ) {
			case State::PHASE_MIGRATION_FAILURE_IN_PROGRESS:
			case State::PHASE_PREVIEW_IN_PROGRESS:
			case State::PHASE_MIGRATION_IN_PROGRESS:
				// * Warning, need a new report object here, state will have changed.
				$site_report = Site_Report::build();
				$renderer->register_node(
					'progress-bar',
					'.tec-ct1-upgrade-update-bar-container',
					'/partials/progress-bar.php',
					[
						'phase'  => $phase,
						'report' => $site_report,
						'text'   => $this->text,
					]
				);
				break;
		}

		return $renderer;
	}

	/**
	 * Handles the request from the Admin UI to start the migration and returns
	 * a first report about its progress.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function start_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'You do not have permission to migrate events.', 'the-events-calendar' ),
				]
			);

			return;
		}

		$dry_run = ! empty( $_REQUEST['tec_events_custom_tables_v1_migration_dry_run'] );
		// Log our start
		do_action(
			'tribe_log',
			'debug',
			'Ajax: Start migration',
			[
				'source'  => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
				'dry_run' => $dry_run,
			] 
		);
		$this->process->start( $dry_run );

		$response = $this->get_report();

		// Make sure we flush before we start the migration.
		flush_rewrite_rules();

		if ( $echo ) {
			wp_send_json( $response );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Handles the request from the Admin UI to cancel the migration and returns
	 * a first report about its progress.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function cancel_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'You do not have permission to migrate events.', 'the-events-calendar' ),
				]
			);

			return;
		}

		// Log our start
		do_action(
			'tribe_log',
			'debug',
			'Ajax: Cancel migration',
			[
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			] 
		);
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
	 * @since 6.0.0
	 *
	 * @param bool $echo Flag whether we echo or return json string.
	 *
	 * @return void|string The JSON-encoded data for the front-end.
	 */
	public function revert_migration( $echo = true ) {
		check_ajax_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'You do not have permission to migrate events.', 'the-events-calendar' ),
				]
			);

			return;
		}

		// Log our start
		do_action(
			'tribe_log',
			'debug',
			'Ajax: Undo migration',
			[
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			] 
		);
		$this->process->revert();
		$response = $this->get_report();
		if ( $echo ) {
			wp_send_json( $response );
			die();
		}

		return wp_json_encode( $response );
	}
}
