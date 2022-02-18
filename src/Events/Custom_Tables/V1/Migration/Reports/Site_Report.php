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
use Tribe__Events__Main as TEC;

/**
 * Class Site_Report.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Site_Report implements JsonSerializable {

	/**
	 * @var array The report data.
	 */
	protected $data = [
		'estimated_time_in_hours' => 0,
		'date_completed' => null,
		'total_events' => null,
		'has_changes' => false,
	'event_reports' => [],
	];

	/**
	 * Site_Report constructor.
	 * since TBD
	 *
	 * @param array <string,mixed> $data The report data in array format.
	 */
	public function __construct( array $data ) {
		$this->data['estimated_time_in_hours'] = $data['estimated_time_in_hours'];
		$this->data['total_events']            = $data['total_events'];
		$this->data['has_changes']             = ! empty( $data['event_reports'] );
		$this->data['event_reports']           = $data['event_reports'];
	}

	/**
	 * @return null|string
	 */
	public function get_date_completed() {
		return $this->data['date_completed'];
	}

	/**
	 * @return int
	 */
	public function get_estimated_time_in_hours() {
		return $this->data['estimated_time_in_hours'];
	}

	/**
	 * @return array
	 */
	public function get_event_reports() {
		return $this->data['event_reports'];
	}

	/**
	 * @return int|null
	 */
	public function get_total_events() {
		return $this->data['total_events'];
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->data ;
	}

	/**
	 * Factory that builds and returns the site migration report
	 *
	 * @since TBD
	 *
	 * @param int $page
	 * @param int $count
	 *
	 * @return Site_Report A reference the site migration report instance.
	 */
	public static function build( $page = - 1, $count = 20 ) {

		global $wpdb;
		$cnt_query     = sprintf( "SELECT COUNT(*) FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
    WHERE p.post_type = '%s'",
			Event_Report::META_KEY_COMPLETE,
			Event_Report::META_KEY_IN_PROGRESS,
			TEC::POSTTYPE );
		$total_flagged = $wpdb->get_var( $cnt_query );

		// Get in progress / complete events
		if ( $page === - 1 || $total_flagged == 0 || $count > $total_flagged) {
			$query = sprintf( "SELECT DISTINCT ID from {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
    WHERE p.post_type = '%s' ",
				Event_Report::META_KEY_COMPLETE,
				Event_Report::META_KEY_IN_PROGRESS,
				TEC::POSTTYPE
			);
		} else {
			$total_pages = $total_flagged / $count;
			if($page > $total_pages) {
				$page = $total_pages;
			}
			$start = ($page - 1) * $count;

			$query = sprintf( "SELECT DISTINCT ID FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( '%s', '%s')
    WHERE p.post_type = '%s' ORDER BY ID ASC LIMIT %d, %d",
				Event_Report::META_KEY_COMPLETE,
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

		// @todo State - what goes in here vs polled realtime? Like total events? Is State going to do realtime data...?
		$state = tribe( State::class );

		$report_meta = [ 'complete_timestamp' => strtotime( 'yesterday 4pm' ) ];


		$data = [
			'estimated_time_in_hours' => $state->get( 'migrate', 'estimated_time_in_seconds' ) * 60 * 60,
			'date_completed'          => ( new \DateTimeImmutable( date( 'Y-m-d H:i:s', $report_meta['complete_timestamp'] ) ) )->format( 'F j, Y, g:i a' ),
			'total_events'             => $total_flagged,
			'has_changes'             => !!count($event_reports),
			'event_reports'           => $event_reports
		];

		return new Site_Report( $data );
	}
}