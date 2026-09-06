<?php
/**
 * Registers the Block Editor authoring surface of the Recurrence feature.
 *
 * The Event Dates panel renders in the datetime block dashboard, through the same
 * `blocks.eventDatetime.dashboardHook` filter Events Calendar Pro mounts its
 * recurrence UI on. The panel rows bind to the `_tec_events_recurrence_dates` post
 * meta (a JSON mirror); on save the mirror is consumed into the canonical
 * `_EventRecurrence` date rules through the Dates_Service, and the mirror is kept
 * in sync with the canonical meta from then on.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Updates\Admin_Notice;
use TEC\Events\Recurrence\Updates\Rules_Conversion_Request;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;

/**
 * Class Blocks_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Blocks_Provider extends Service_Provider {
	/**
	 * The meta key mirroring the authored dates for the Block Editor.
	 *
	 * @since TBD
	 */
	public const META_KEY = '_tec_events_recurrence_dates';

	/**
	 * Marks a submitted mirror awaiting a successful canonical save.
	 *
	 * @since TBD
	 */
	public const PENDING_META_KEY = '_tec_events_recurrence_dates_pending';

	/**
	 * The admin notice pulled in this request, `null` until one was found.
	 *
	 * @since TBD
	 *
	 * @var array{notice: array{type: string, message: string}}|null
	 */
	private ?array $pulled_notice = null;

	/**
	 * Whether additional date persistence failed in this request.
	 *
	 * @since TBD
	 * @var bool
	 */
	private bool $save_failed = false;

	/**
	 * Suppresses mirror refresh while consuming submitted dates.
	 *
	 * @since TBD
	 * @var bool
	 */
	private bool $consuming = false;

	/**
	 * Registers the Block Editor integration hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			// Events Calendar Pro provides the full recurrence UI.
			return;
		}

		if ( did_action( 'init' ) ) {
			$this->register_meta();
		} elseif ( ! has_action( 'init', [ $this, 'register_meta' ] ) ) {
			add_action( 'init', [ $this, 'register_meta' ] );
		}

		if ( ! has_filter( 'tribe_block_block_data_event-datetime', [ $this, 'add_block_attribute' ] ) ) {
			add_filter( 'tribe_block_block_data_event-datetime', [ $this, 'add_block_attribute' ] );
		}

		if ( ! has_filter( 'tribe_editor_config', [ $this, 'add_editor_config' ] ) ) {
			add_filter( 'tribe_editor_config', [ $this, 'add_editor_config' ] );
		}

		add_filter( 'rest_request_after_callbacks', [ $this, 'report_save_failure' ], 100, 3 );

		if ( ! has_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'consume_blocks_dates' ] ) ) {
			// After core wrote the attribute-bound meta, before the Custom Tables commit at 100.
			add_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'consume_blocks_dates' ], 50, 2 );
		}

		if ( ! has_action( 'updated_post_meta', [ $this, 'sync_mirror_meta' ] ) ) {
			add_action( 'updated_post_meta', [ $this, 'sync_mirror_meta' ], 10, 3 );
		}

		if ( ! has_action( 'added_post_meta', [ $this, 'sync_mirror_meta' ] ) ) {
			add_action( 'added_post_meta', [ $this, 'sync_mirror_meta' ], 10, 3 );
		}

		if ( ! has_action( 'deleted_post_meta', [ $this, 'clear_mirror_meta' ] ) ) {
			add_action( 'deleted_post_meta', [ $this, 'clear_mirror_meta' ], 10, 3 );
		}
	}

	/**
	 * Unregisters the hooks managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'rest_request_after_callbacks', [ $this, 'report_save_failure' ], 100 );
		$this->save_failed = false;
		$this->consuming   = false;
		remove_action( 'init', [ $this, 'register_meta' ] );
		remove_filter( 'tribe_block_block_data_event-datetime', [ $this, 'add_block_attribute' ] );
		remove_filter( 'tribe_editor_config', [ $this, 'add_editor_config' ] );
		remove_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'consume_blocks_dates' ], 50 );
		remove_action( 'updated_post_meta', [ $this, 'sync_mirror_meta' ] );
		remove_action( 'added_post_meta', [ $this, 'sync_mirror_meta' ] );
		remove_action( 'deleted_post_meta', [ $this, 'clear_mirror_meta' ] );
	}

	/**
	 * Registers the mirror meta the datetime block attribute binds to.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_meta(): void {
		register_meta(
			'post',
			self::META_KEY,
			[
				'object_subtype'    => TEC::POSTTYPE,
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
				'sanitize_callback' => [ $this, 'sanitize_rows_json' ],
			]
		);
	}

	/**
	 * Sanitizes the mirror meta value to a JSON array of valid date rows.
	 *
	 * @since TBD
	 *
	 * @param mixed $value The raw meta value.
	 *
	 * @return string The sanitized JSON value.
	 */
	public function sanitize_rows_json( $value ): string {
		$value   = (string) $value;
		$decoded = json_decode( $value, true );
		$rows    = $this->decode_rows( $value );
		// Preserve invalid submissions so the save handler can reject them without deleting dates.
		return is_array( $decoded ) && count( $decoded ) === count( $rows ) ? (string) wp_json_encode( $rows ) : $value;
	}

	/**
	 * Adds the `dates` attribute, bound to the mirror meta, to the datetime block.
	 *
	 * Also rehydrates a stale mirror from the canonical meta before the editor
	 * reads it: the mirror can lag when the feature was inactive (e.g. while
	 * Events Calendar Pro owned the authoring).
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed>|mixed $block_data The datetime block definition data.
	 *
	 * @return array<string,mixed>|mixed The filtered block definition data.
	 */
	public function add_block_attribute( $block_data ) {
		if ( ! is_array( $block_data ) ) {
			return $block_data;
		}

		$block_data['attributes']['dates'] = [
			'type'   => 'string',
			'source' => 'meta',
			'meta'   => self::META_KEY,
		];

		$post_id = absint( tribe_get_request_var( 'post', 0 ) );

		if ( $post_id ) {
			$this->rehydrate_mirror( $post_id );
		}

		return $block_data;
	}

	/**
	 * Adds the Event Dates panel configuration to the editor config.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed>|mixed $editor_config The localized editor configuration.
	 *
	 * @return array<string,mixed>|mixed The filtered editor configuration.
	 */
	public function add_editor_config( $editor_config ) {
		if ( ! is_array( $editor_config ) ) {
			return $editor_config;
		}

		$post_id = absint( tribe_get_request_var( 'post', 0 ) );
		$guard   = $this->container->make( Authoring_Guard::class );

		$is_occurrence = $post_id > 0 && $guard->is_occurrence_edit( $post_id );
		// An Occurrence of a rule-based Event is as locked as the Event: the lock is about the Event.
		$is_locked = $post_id > 0 && $guard->is_rule_locked( $post_id );
		$event_id  = $post_id > 0 ? Occurrence::normalize_id( $post_id ) : 0;
		$summary   = [
			'count' => 0,
			'dates' => [],
		];

		if ( $is_locked ) {
			$list = $this->container->make( Occurrences_List::class );

			foreach ( $list->get_scheduled_dates( $event_id ) as $row ) {
				$chip = $list->format_chip( $row );
				// The editor config uses camelCase keys.
				$chip['editLink'] = $chip['edit_link'];
				unset( $chip['edit_link'] );

				$summary['dates'][] = $chip;
			}

			$summary['count'] = count( $summary['dates'] );
		}

		$lock_enabled = true;
		$can_convert  = false;
		$settings_url = '';

		if ( $is_locked ) {
			$settings     = $this->container->make( Settings::class );
			$lock_enabled = $settings->is_lock_enabled();
			$settings_url = $settings->get_settings_url();
			$can_convert  = $settings->can_convert( $event_id ) && current_user_can( 'edit_post', $event_id );
		}

		$editor_config['events']['recurrenceDates'] = [
			'enabled'        => true,
			'locked'         => $is_locked,
			'isOccurrence'   => $is_occurrence,
			'allDay'         => $event_id > 0 && tribe_event_is_all_day( $event_id ),
			// Built directly: link filters would rewrite the parent Event link back to the Occurrence.
			'parentEditLink' => $is_occurrence ? admin_url( 'post.php?post=' . $event_id . '&action=edit' ) : '',
			'summary'        => $summary,
			'lockEnabled'    => $lock_enabled,
			'settingsUrl'    => $settings_url,
			'canConvert'     => $can_convert,
			'convertUrl'     => Rules_Conversion_Request::get_action_url(),
			'convertAction'  => Rules_Conversion_Request::ACTION,
			'ackField'       => Rules_Conversion_Request::ACK_FIELD,
			// The nonce is minted for a convertible Event only, and for the Event: the request normalizes an Occurrence ID.
			'convertNonce'   => $can_convert ? wp_create_nonce( Rules_Conversion_Request::NONCE_ACTION . $event_id ) : '',
			// The real Event post ID, an Occurrence one normalized: the conversion form posts it.
			'postId'         => $event_id,
			// The Block Editor screen renders no admin notices: the pending one travels with the config.
			'notice'         => $this->pull_notice(),
		];

		return $editor_config;
	}

	/**
	 * Pulls, once per request, the pending admin notice of the current user.
	 *
	 * @since TBD
	 *
	 * @return array{type: string, message: string}|null The notice, or `null` when none is pending.
	 */
	private function pull_notice(): ?array {
		if ( null !== $this->pulled_notice ) {
			return $this->pulled_notice['notice'];
		}

		$notice = $this->container->make( Admin_Notice::class )->pull();

		if ( null !== $notice ) {
			// Pulling deletes the transient: a second config build in the request must see the same notice.
			$this->pulled_notice = [ 'notice' => $notice ];
		}

		return $notice;
	}

	/**
	 * Consumes the mirror meta into the canonical dates on a Block Editor save.
	 *
	 * @since TBD
	 *
	 * @param WP_Post|mixed              $post    The saved Event post.
	 * @param WP_REST_Request|mixed|null $request The REST request that saved it.
	 *
	 * @return void
	 */
	public function consume_blocks_dates( $post, $request = null ): void {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$post_id = (int) $post->ID;
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! metadata_exists( 'post', $post_id, self::META_KEY ) ) {
			// The panel never touched this Event: do not clear dates authored elsewhere.
			return;
		}

		$guard = $this->container->make( Authoring_Guard::class );

		if ( $guard->is_occurrence_edit( $post_id ) || $guard->is_rule_locked( $post_id ) ) {
			// A single Occurrence, or Pro rule data: not authored here.
			return;
		}

		$value = (string) get_post_meta( $post_id, self::META_KEY, true );
		$raw   = json_decode( $value, true );
		$rows  = $this->decode_rows( $value );
		if ( ! is_array( $raw ) || count( $raw ) !== count( $rows ) ) {
			$this->save_failed = true;
			update_post_meta( $post_id, self::PENDING_META_KEY, true );
			return;
		}
		$dates = [];

		foreach ( $rows as $row ) {
			$end_date = $row['end_date'] ?? $row['date'];
			$dates[] = [
				'start' => "{$row['date']} {$row['start']}",
				'end'   => "{$end_date} {$row['end']}",
			];
		}

		$service = $this->container->make( Dates_Service::class );

		update_post_meta( $post_id, self::PENDING_META_KEY, true );
		$this->consuming = true;
		try {
			$saved = count( $dates ) ? $service->set_dates( $post_id, $dates ) : $service->remove_dates( $post_id );
		} finally {
			$this->consuming = false;
		}
		$this->save_failed = ! $saved;
		if ( $saved ) {
			delete_post_meta( $post_id, self::PENDING_META_KEY );
			$this->rehydrate_mirror( $post_id );
		}
	}

	/**
	 * Keeps a failed schedule save visible to the Block Editor instead of returning success.
	 *
	 * @since TBD
	 * @param mixed $response The normal REST response.
	 * @return mixed The normal response or the schedule error.
	 */
	public function report_save_failure( $response ) {
		if ( ! $this->save_failed ) {
			return $response;
		}
		$this->save_failed = false;
		return new \WP_Error( 'tec_recurrence_dates_save_failed', __( 'The additional dates could not be saved. Review the dates and try again. Other event changes may already have been saved.', 'the-events-calendar' ), [ 'status' => 500 ] );
	}

	/**
	 * Keeps the mirror meta in sync when the canonical meta is written.
	 *
	 * @since TBD
	 *
	 * @param int|mixed    $meta_id  The updated meta row ID.
	 * @param int|mixed    $post_id  The post the meta belongs to.
	 * @param string|mixed $meta_key The updated meta key.
	 *
	 * @return void
	 */
	public function sync_mirror_meta( $meta_id, $post_id = 0, $meta_key = '' ): void {
		if ( '_EventRecurrence' !== $meta_key || TEC::POSTTYPE !== get_post_type( (int) $post_id ) ) {
			return;
		}

		if ( $this->consuming ) {
			return;
		}
		delete_post_meta( (int) $post_id, self::PENDING_META_KEY );
		$this->rehydrate_mirror( (int) $post_id );
	}

	/**
	 * Clears the mirror meta when the canonical meta is deleted.
	 *
	 * @since TBD
	 *
	 * @param int[]|mixed  $meta_ids The deleted meta row IDs.
	 * @param int|mixed    $post_id  The post the meta belonged to.
	 * @param string|mixed $meta_key The deleted meta key.
	 *
	 * @return void
	 */
	public function clear_mirror_meta( $meta_ids, $post_id = 0, $meta_key = '' ): void {
		if ( '_EventRecurrence' !== $meta_key || TEC::POSTTYPE !== get_post_type( (int) $post_id ) ) {
			return;
		}

		delete_post_meta( (int) $post_id, self::META_KEY );
	}

	/**
	 * Rewrites the mirror meta from the canonical authored dates, when different.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return void
	 */
	private function rehydrate_mirror( int $post_id ): void {
		if ( get_post_meta( $post_id, self::PENDING_META_KEY, true ) ) {
			// A failed save must not replace submitted dates with the previous schedule.
			return;
		}

		// Preserve submitted mirror data when first-save persistence did not create the Event row.
		if ( ! \TEC\Events\Custom_Tables\V1\Models\Event::find( $post_id, 'post_id' ) && metadata_exists( 'post', $post_id, self::META_KEY ) ) {
			return;
		}

		$guard = $this->container->make( Authoring_Guard::class );

		if ( $guard->is_rule_locked( $post_id ) ) {
			// Rule-based data has no dates mirror; the panel renders a locked notice.
			return;
		}

		$rows = array_map(
			static function ( array $period ): array {
				return [
					'date'  => $period['start']->format( 'Y-m-d' ),
					'start' => $period['start']->format( 'H:i:s' ),
					'end'   => $period['end']->format( 'H:i:s' ),
				] + ( $period['start']->format( 'Y-m-d' ) !== $period['end']->format( 'Y-m-d' ) ? [ 'end_date' => $period['end']->format( 'Y-m-d' ) ] : [] );
			},
			$guard->get_authored_periods( $post_id )
		);

		$mirror = (string) wp_json_encode( $rows );

		if ( $mirror === (string) get_post_meta( $post_id, self::META_KEY, true ) ) {
			return;
		}

		update_post_meta( $post_id, self::META_KEY, $mirror );
	}

	/**
	 * Decodes and validates a JSON mirror value into date rows.
	 *
	 * @since TBD
	 *
	 * @param string $value The JSON mirror value.
	 *
	 * @return array<int,array{date: string, start: string, end: string}> The valid rows.
	 */
	private function decode_rows( string $value ): array {
		$decoded = json_decode( $value, true );

		if ( ! is_array( $decoded ) ) {
			return [];
		}

		$rows = [];

		foreach ( $decoded as $row ) {
			if ( ! is_array( $row ) || empty( $row['date'] ) || empty( $row['start'] ) || empty( $row['end'] ) ) {
				continue;
			}

			$date     = (string) $row['date'];
			$end_date = (string) ( $row['end_date'] ?? $date );
			$start = $this->normalize_time( (string) $row['start'] );
			$end   = $this->normalize_time( (string) $row['end'] );

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || null === $start || null === $end ) {
				continue;
			}

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) || $end_date . ' ' . $end <= $date . ' ' . $start || gmdate( 'Y-m-d', strtotime( $date ) ?: 0 ) !== $date || gmdate( 'Y-m-d', strtotime( $end_date ) ?: 0 ) !== $end_date ) {
				// Keep complete periods and reject invalid calendar dates.
				continue;
			}

			$rows[] = [
				'date'  => $date,
				'start' => $start,
				'end'   => $end,
			] + ( $end_date !== $date ? [ 'end_date' => $end_date ] : [] );
		}

		return $rows;
	}

	/**
	 * Normalizes a time string to the `H:i:s` format.
	 *
	 * @since TBD
	 *
	 * @param string $time The time string, in the `H:i` or `H:i:s` format.
	 *
	 * @return string|null The normalized time, or `null` when invalid.
	 */
	private function normalize_time( string $time ): ?string {
		if ( preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ) {
			return "{$time}:00";
		}

		if ( preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time ) ) {
			return $time;
		}

		return null;
	}
}
