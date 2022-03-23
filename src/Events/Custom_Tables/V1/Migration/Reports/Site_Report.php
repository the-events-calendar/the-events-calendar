<?php
/**
 * An immutable value object modeling the migration report for a site.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration\Report;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use JsonSerializable;
use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events_Pro\Custom_Tables\V1\EventRecurrence_Factory;
use Tribe__Events__Main as TEC;

/**
 * Class Site_Report.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 * @property int                 estimated_time_in_hours
 * @property string              date_completed
 * @property int                 total_events
 * @property int                 total_events_migrated
 * @property int                 total_events_in_progress
 * @property int                 total_events_remaining
 * @property bool                has_changes
 * @property array<Event_Report> event_reports
 * @property string              migration_phase
 * @property bool                is_completed
 * @property bool                is_running
 * @property int                 progress_percent
 */
class Site_Report implements JsonSerializable {

	/**
	 * Site report data.
	 *
	 * @since TBD
	 *
	 * @var array<mixed> The report data.
	 */
	protected $data = [
		'estimated_time_in_hours'  => 0,
		'date_completed'           => null,
		'total_events'             => null,
		'total_events_migrated'    => null,
		'total_events_in_progress' => null,
		'has_changes'              => false,
		'migration_phase'          => null,
		'is_completed'             => false,
		'is_running'               => false,
		'progress_percent'         => 0,
	];

	/**
	 * Site_Report constructor.
	 *
	 * @since TBD
	 *
	 * @param array <string,mixed> $data The report data in array format.
	 */
	public function __construct( array $data ) {
		$this->data['estimated_time_in_hours']  = $data['estimated_time_in_hours'];
		$this->data['total_events']             = (int) $data['total_events'];
		$this->data['total_events_remaining']   = (int) $data['total_events_remaining'];
		$this->data['total_events_in_progress'] = (int) $data['total_events_in_progress'];
		$this->data['total_events_migrated']    = (int) $data['total_events_migrated'];
		$this->data['has_changes']              = (boolean) $data['has_changes'];
		$this->data['migration_phase']          = $data['migration_phase'];
		$this->data['is_completed']             = $data['is_completed'];
		$this->data['is_running']               = $data['is_running'];
		$this->data['progress_percent']         = $data['progress_percent'];
		$this->data['date_completed']           = $data['date_completed'];
	}

	/**
	 * Factory that builds and returns the site migration report, with pagination for the Event_Reports.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A reference to the site migration report instance.
	 */
	public static function build() {
		$event_repo = tribe(Events::class);
		$state = tribe( State::class );

		// Total TEC events
		$total_events = $event_repo->get_total_events();

		// Total done with migration
		$total_events_migrated = $event_repo->get_total_events_migrated();

		// Total in progress
		$total_events_in_progress = $event_repo->get_total_events_in_progress();

		// How many events have not been migrated yet
		$total_events_remaining = $event_repo->get_total_events_remaining();

		$progress_percent = ( $total_events ) ? round( ( $total_events_migrated / $total_events ) * 100 ) : 0;
		$date_completed   = ( new \DateTime( 'now', wp_timezone() ) )->setTimestamp( $state->get( 'complete_timestamp' ) );

		$data = [
			'estimated_time_in_hours'  => round( $state->get( 'migration', 'estimated_time_in_seconds' ) / 60 / 60, 2 ),
			'date_completed'           => $date_completed->format( 'F j, Y, g:i a' ),
			'total_events_in_progress' => $total_events_in_progress,
			'total_events_migrated'    => $total_events_migrated,
			'total_events'             => $total_events,
			'total_events_remaining'   => $total_events_remaining,
			'has_changes'              => $total_events_migrated > 0,
			'migration_phase'          => $state->get_phase(),
			'is_completed'             => $state->is_completed(),
			'is_running'               => $state->is_running(),
			'progress_percent'         => $progress_percent,
		];

		return new Site_Report( $data );
	}

	/**
	 * Retrieves a sorted list of Event_Report objects.
	 *
	 * @since TBD
	 *
	 * @param int $page  The page to retrieve in a pagination request. If -1, it will retrieve all reports in the
	 *                   database.
	 * @param int $count The number of event reports to retrieve. If $page is -1 this will be ignored.
	 *
	 * @return array<Event_Report> A sorted list of Event_Report objects.
	 */
	public function get_event_reports( $page = - 1, $count = 20 ) {
		$event_repo = tribe( Events::class );
		// Get all the events that have been touched by migration
		$post_ids      = $event_repo->get_events_migrated( $page, $count );
		$event_reports = [];
		foreach ( $post_ids as $post_id ) {
			$event_reports[] = new Event_Report( get_post( $post_id ) );
		}

		return $event_reports;
	}

	/**
	 * Get all of the site report data.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed>
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Getter for site report data.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return array<string,mixed>
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}