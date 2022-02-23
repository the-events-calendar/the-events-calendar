<?php
/**
 * An immutable value object modeling the migration report for a site.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration\Report;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use JsonSerializable;
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
 */
class Site_Report implements JsonSerializable {

	/**
	 * @since TBD
	 * @var array<mixed> The report data.
	 */
	protected $data = [
		'estimated_time_in_hours'  => 0,
		'date_completed'           => null,
		'total_events'             => null,
		'total_events_migrated'    => null,
		'total_events_in_progress' => null,
		'has_changes'              => false,
		'event_reports'            => [],
		'migration_phase'          => null,
		'is_completed'             => false,
		'is_running'               => false,
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
		$this->data['total_events']             = $data['total_events'];
		$this->data['total_events_remaining']   = $data['total_events_remaining'];
		$this->data['total_events_in_progress'] = $data['total_events_in_progress'];
		$this->data['total_events_migrated']    = $data['total_events_migrated'];
		$this->data['has_changes']              = ! empty( $data['event_reports'] );
		$this->data['event_reports']            = $data['event_reports'];
		$this->data['migration_phase']          = $data['migration_phase'];
		$this->data['is_completed']             = $data['is_completed'];
		$this->data['is_running']               = $data['is_running'];
	}

	/**
	 * Factory that builds and returns the site migration report, with pagination for the Event_Reports.
	 *
	 * @since TBD
	 *
	 * @param int $page
	 * @param int $count
	 *
	 * @return Site_Report A reference to the site migration report instance.
	 */
	public static function build( $page = - 1, $count = 20 ) {
		global $wpdb;
		// Total TEC events
		$total_cnt_query = sprintf(
			"SELECT COUNT(*)
			FROM {$wpdb->posts} p
			WHERE p.post_type = '%s'",
			TEC::POSTTYPE
		);
		$total_events    = $wpdb->get_var( $total_cnt_query );

		// Total done with migration
		$total_migrated_query  = sprintf(
			"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
			WHERE p.post_type = '%s'",
			Event_Report::META_KEY_SUCCESS,
			Event_Report::META_KEY_FAILURE,
			TEC::POSTTYPE
		);
		$total_events_migrated = $wpdb->get_var( $total_migrated_query );

		// Total in progress or done with migration
		$total_in_progress_query  = sprintf(
			"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '%s'
			WHERE p.post_type = '%s'",
			Event_Report::META_KEY_IN_PROGRESS,
			TEC::POSTTYPE
		);
		$total_events_in_progress = $wpdb->get_var( $total_in_progress_query );

		// Get in progress / complete events
		if ( $page === - 1 || $total_events_migrated == 0 || $count > $total_events_migrated ) {
			$query = sprintf(
				"SELECT DISTINCT ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
				WHERE p.post_type = '%s' ",
				Event_Report::META_KEY_REPORT_DATA,
				Event_Report::META_KEY_IN_PROGRESS,
				TEC::POSTTYPE
			);
		} else {
			$total_pages = $total_events_migrated / $count;
			if ( $page > $total_pages ) {
				$page = $total_pages;
			}
			$start = ( $page - 1 ) * $count;

			$query = sprintf(
				"SELECT DISTINCT ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
				WHERE p.post_type = '%s' ORDER BY ID ASC LIMIT %d, %d",
				Event_Report::META_KEY_REPORT_DATA,
				Event_Report::META_KEY_IN_PROGRESS,
				TEC::POSTTYPE,
				$start,
				$count
			);
		}

		$rows          = $wpdb->get_col( $query );
		$event_reports = [];
		foreach ( $rows as $post_id ) {
			$event_reports[] = new Event_Report( get_post( $post_id ) );
		}


		$state = tribe( State::class );

		$report_meta = [ 'complete_timestamp' => strtotime( 'yesterday 4pm' ) ];

		$data = [
			'estimated_time_in_hours'  => $state->get( 'migrate', 'estimated_time_in_seconds' ) * 60 * 60,
			'date_completed'           => ( new \DateTimeImmutable( date( 'Y-m-d H:i:s', $report_meta['complete_timestamp'] ) ) )->format( 'F j, Y, g:i a' ),
			'total_events_in_progress' => $total_events_in_progress,
			'total_events_migrated'    => $total_events_migrated,
			'total_events'             => $total_events,
			'total_events_remaining'   => $total_events - $total_events_migrated,
			'has_changes'              => (bool) count( $event_reports ),
			'event_reports'            => $event_reports,
			'migration_phase'          => $state->get_phase(),
			'is_completed'             => $state->is_completed(),
			'is_running'               => $state->is_running(),
		];

		return new Site_Report( $data );
	}

	/**
	 * @since TBD
	 * @return mixed[]
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * @since TBD
	 *
	 * @param $prop
	 *
	 * @return mixed|null
	 */
	public function __get( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : null;
	}

	/**
	 * @since TBD
	 * @return array<mixed>
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}