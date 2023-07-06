<?php
/**
 * An immutable value object modeling the migration report for a site.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration\Report;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use JsonSerializable;
use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * Class Site_Report.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 * @property float  estimated_time_in_seconds
 * @property float  estimated_time_in_minutes
 * @property string date_completed
 * @property string completed_timestamp
 * @property int    total_events
 * @property int    total_events_migrated
 * @property int    total_events_in_progress
 * @property int    total_events_remaining
 * @property bool   has_changes
 * @property bool   has_errors
 * @property string migration_phase
 * @property bool   is_completed
 * @property bool   is_running
 * @property int    progress_percent
 * @property int    total_events_failed
 */
class Site_Report implements JsonSerializable {

	/**
	 * Site report data.
	 *
	 * @since 6.0.0
	 *
	 * @var array<mixed> The report data.
	 */
	protected $data = [
		'estimated_time_in_seconds' => 0,
		'estimated_time_in_minutes' => 0,
		'date_completed'            => null,
		'completed_timestamp'       => null,
		'total_events'              => null,
		'total_events_migrated'     => null,
		'total_events_in_progress'  => null,
		'total_events_remaining'    => null,
		'has_changes'               => false,
		'migration_phase'           => null,
		'is_completed'              => false,
		'is_running'                => false,
		'has_errors'                => false,
		'progress_percent'          => 0,
		'total_events_failed'       => null,
	];

	/**
	 * Site_Report constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param array <string,mixed> $data The report data in array format.
	 */
	public function __construct( array $data ) {
		$this->data['estimated_time_in_seconds'] = $data['estimated_time_in_seconds'] ?? 0;
		$this->data['estimated_time_in_minutes'] = $data['estimated_time_in_minutes'] ?? 0;
		$this->data['total_events']              = (int) $data['total_events'] ;
		$this->data['total_events_remaining']    = (int) $data['total_events_remaining'];
		$this->data['total_events_in_progress']  = (int) $data['total_events_in_progress'];
		$this->data['total_events_migrated']     = (int) $data['total_events_migrated'];
		$this->data['has_changes']               = (boolean) $data['has_changes'];
		$this->data['has_errors']                = (boolean) $data['has_errors'];
		$this->data['migration_phase']           = $data['migration_phase'] ?? null;
		$this->data['is_completed']              = $data['is_completed'] ?? false;
		$this->data['is_running']                = $data['is_running'] ?? false;
		$this->data['progress_percent']          = $data['progress_percent'] ?? 0;
		$this->data['date_completed']            = $data['date_completed'] ?? null;
		$this->data['total_events_failed']       = $data['total_events_failed'] ?? null;
		$this->data['completed_timestamp']       = $data['completed_timestamp'] ?? null;
	}

	/**
	 * Factory that builds and returns the site migration report, with pagination for the Event_Reports.
	 *
	 * @since 6.0.0
	 *
	 * @return Site_Report A reference to the site migration report instance.
	 */
	public static function build() {
		$event_repo = tribe( Events::class );
		$state      = tribe( State::class );

		// Total TEC events
		$total_events = $event_repo->get_total_events();

		// Total done with migration
		$total_events_migrated = $event_repo->get_total_events_migrated();

		// Total in progress
		$total_events_in_progress = $event_repo->get_total_events_in_progress();

		// Total migrations that had some error.
		$total_events_with_failure = $event_repo->get_total_events_with_failure();

		// How many events have not been migrated yet
		$total_events_remaining = $event_repo->get_total_events_remaining();

		$progress_percent          = ( $total_events ) ? round( ( $total_events_migrated / $total_events ) * 100 ) : 0;
		$date_completed            = ( new \DateTime( 'now', wp_timezone() ) )->setTimestamp( $state->get( 'complete_timestamp' ) );
		$estimated_time_in_seconds = $state->get( 'migration', 'estimated_time_in_seconds' ) + ( 60 * 5 );

		$data = [
			'estimated_time_in_seconds' => $estimated_time_in_seconds,
			'estimated_time_in_minutes' => round( $estimated_time_in_seconds / 60, 0 ),
			'date_completed'            => $date_completed->format( 'F j, Y, g:i a' ),
			'completed_timestamp'       => $date_completed->getTimestamp(),
			'total_events_in_progress'  => $total_events_in_progress,
			'total_events_migrated'     => $total_events_migrated,
			'total_events'              => $total_events,
			'total_events_remaining'    => $total_events_remaining,
			'total_events_failed'       => $total_events_with_failure,
			'has_changes'               => $total_events_migrated > 0,
			'migration_phase'           => $state->get_phase(),
			'is_completed'              => $state->is_completed(),
			'is_running'                => $state->is_running(),
			'progress_percent'          => $progress_percent,
			'has_errors'                => $total_events_with_failure > 0
		];

		return new Site_Report( $data );
	}

	/**
	 * Retrieves a sorted list of Event_Report objects.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $page                  The page to retrieve in a pagination request. If -1, it will retrieve all
	 *                                     reports in the database.
	 * @param int   $count                 The number of event reports to retrieve. If $page is -1 this will be
	 *                                     ignored.
	 * @param array $filter                An option set of filters to apply to the search.
	 *
	 * @return array<Event_Report> A sorted list of Event_Report objects.
	 */
	public function get_event_reports( $page = - 1, $count = 20, $filter = [] ) {
		$event_repo = tribe( Events::class );
		// Get all the events that have been touched by migration
		$post_ids      = $event_repo->get_events_migrated( $page, $count, $filter );
		$event_reports = [];
		foreach ( $post_ids as $post_id ) {
			$event_reports[] = new Event_Report( get_post( $post_id ) );
		}

		return $event_reports;
	}

	/**
	 * Get all of the site report data.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,mixed>
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Getter for site report data.
	 *
	 * @since 6.0.0
	 *
	 * @param string $prop The key of the data.
	 *
	 * @return mixed|null
	 */
	public function __get( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : null;
	}

	/**
	 * The JSON serializer.
	 *
	 * @since 6.0.0
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->data;
	}
}
