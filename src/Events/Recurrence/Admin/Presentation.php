<?php
/**
 * Shared, read-only event identity and schedule presentation for the admin.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Recurrence\Authoring_Guard;
use TEC\Events\Recurrence\Date_Rules;
use TEC\Events\Recurrence\Dates;
use Tribe__Date_Utils as Date_Utils;
use WP_Post;

/** Shared presentation data. @since TBD */
class Presentation {
	/** @var array<int,Event|null> Event models, loaded per page. @since TBD */
	private array $events = [];
	/** @var array<int,int> Materialized date counts, including past dates. @since TBD */
	private array $counts = [];

	/**
	 * Primes parent records and counts without a query per rendered occurrence.
	 *
	 * @since TBD
	 * @param array<WP_Post> $posts List results.
	 * @return void
	 */
	public function prime( array $posts ): void {
		$ids = [];
		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$id = Occurrence::normalize_id( (int) $post->ID );
				if ( ! array_key_exists( $id, $this->events ) ) {
					$ids[] = $id;
				}
			}
		}
		$ids = array_values( array_unique( $ids ) );
		if ( ! $ids ) {
			return;
		}
		_prime_post_caches( $ids, true, true );
		foreach ( $ids as $id ) {
			$this->events[ $id ] = null;
			$this->counts[ $id ] = 0;
		}
		foreach ( Event::where_in( 'post_id', $ids )->all() as $event ) {
			$this->events[ (int) $event->post_id ] = $event;
		}
		global $wpdb;
		$table = Occurrences::table_name( true );
		$in    = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.DirectDatabaseQuery -- Table name and placeholder list are internal; the request-local cache is primed here.
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, COUNT(*) AS total FROM $table WHERE post_id IN ($in) GROUP BY post_id", $ids ) );
		foreach ( $rows as $row ) {
			$this->counts[ (int) $row->post_id ] = (int) $row->total;
		}
	}

	/** Clears the request cache after writes or provider teardown. @since TBD @return void */
	public function reset(): void {
		$this->events = [];
		$this->counts = [];
	}

	/**
	 * Describes an event or occurrence, using the authored schedule rather than its count.
	 *
	 * @since TBD
	 * @param int $post_id Editor/list identity.
	 * @return array<string,mixed> Shared UI data, with plain text and explicit URLs.
	 */
	public function get( int $post_id ): array {
		$post = get_post( $post_id );
		$this->prime( $post instanceof WP_Post ? [ $post ] : [] );
		$parent     = Occurrence::normalize_id( $post_id );
		$event      = $this->events[ $parent ] ?? null;
		$meta       = get_post_meta( $parent, '_EventRecurrence', true );
		$rset       = $event instanceof Event ? trim( (string) $event->rset ) : '';
		$rules      = $meta ? ! Date_Rules::is_dates_only_meta( $meta ) : ( '' !== $rset && ! Dates::is_dates_only( $rset ) );
		$multiple   = $rules || '' !== $rset || ! empty( $meta['rules'] );
		$kind       = $rules ? 'rules' : ( $multiple ? 'dates' : 'single' );
		$labels     = [
			'single' => __( 'Single event', 'the-events-calendar' ),
			'dates'  => __( 'Multiple dates', 'the-events-calendar' ),
			'rules'  => __( 'Recurring event', 'the-events-calendar' ),
		];
		$occurrence = tribe( Provisional_Post::class )->is_provisional_post_id( $post_id );
		$external   = tribe( Authoring_Guard::class )->has_external_updates();
		return [
			'postId'         => $post_id,
			'eventId'        => $parent,
			'isOccurrence'   => $occurrence,
			'schedule'       => $kind,
			'scheduleLabel'  => $labels[ $kind ],
			'locked'         => $rules && ! $external,
			'externalScope'  => $external,
			'count'          => $this->counts[ $parent ] ?? 0,
			'eventTitle'     => get_the_title( $parent ),
			'parentEditLink' => current_user_can( 'edit_post', $parent ) ? admin_url( 'post.php?post=' . $parent . '&action=edit' ) : '',
			'datesLink'      => add_query_arg(
				[
					'post_type'       => 'tribe_events',
					'tec_events_view' => 'occurrences',
					'tec_dates'       => 'all',
					'tec_event'       => $parent,
				],
				admin_url( 'edit.php' ) 
			),
			'start'          => $this->date_label( $post_id, false ),
			'end'            => $this->date_label( $post_id, true ),
		];
	}

	/**
	 * Formats the selected identity's dates, including year and timezone.
	 *
	 * @since TBD
	 * @param int  $id  Event or occurrence post ID.
	 * @param bool $end Whether to format its end.
	 * @return string Localized date and time.
	 */
	public function date_label( int $id, bool $end ): string {
		$date = get_post_meta( $id, $end ? '_EventEndDate' : '_EventStartDate', true );
		if ( ! $date ) {
			return __( 'Not scheduled', 'the-events-calendar' );
		}
		$timezone = get_post_meta( $id, '_EventTimezone', true ) ?: wp_timezone_string();
		try {
			$value = Date_Utils::immutable( $date, $timezone );
			$label = $value->format_i18n( tribe_get_date_format( true ) );
			if ( tribe_event_is_all_day( $id ) ) {
				return $label . ' · ' . __( 'All Day', 'the-events-calendar' ) . ' · ' . $timezone;
			}
			return $label . ' · ' . $value->format_i18n( tribe_get_time_format() ) . ' · ' . $timezone;
		} catch ( \Exception $e ) {
			return __( 'Date unavailable', 'the-events-calendar' );
		}
	}
}
