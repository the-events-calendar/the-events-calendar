<?php
/**
 * Keeps the dates and recurrence data of a rule-based Event immutable.
 *
 * A rule-based Event (Events Calendar Pro recurrence rules, no rule engine active)
 * keeps its Occurrences frozen. Its own Start/End dates and recurrence meta must freeze
 * with them: a date edit would move the Event row while the frozen Occurrences stayed
 * put, and a recurrence meta write would rewrite the rules. Every date write, from the
 * Classic Editor, the Block Editor (REST), the ORM or an import, goes through the post
 * meta filters, so the guard sits there and swallows the frozen writes.
 *
 * Swallowed writes report success: a `false` return from the meta filters turns a REST
 * save into a 500 that loses the title and content edits with it.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Authoring_Guard;
use TEC\Events\Recurrence\Settings;
use Tribe__Events__Main as TEC;

/**
 * Class Freeze_Guard.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */
class Freeze_Guard {
	/**
	 * The meta keys frozen on a rule-based Event.
	 *
	 * @since TBD
	 *
	 * @var array<int,string>
	 */
	public const FROZEN_META_KEYS = [
		'_EventStartDate',
		'_EventEndDate',
		'_EventStartDateUTC',
		'_EventEndDateUTC',
		'_EventAllDay',
		'_EventTimezone',
		'_EventDuration',
		'_EventRecurrence',
	];

	/**
	 * The depth of the `allow()` calls suspending the guard.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	private int $suspended = 0;

	/**
	 * The meta keys refused per Event post ID during the request.
	 *
	 * @since TBD
	 *
	 * @var array<int,array<int,string>>
	 */
	private array $refused = [];

	/**
	 * Registers the meta filters.
	 *
	 * The guard runs at -5: after the single Occurrence update buffered (at -10) the
	 * date writes posted against a provisional ID, before the provisional meta filters
	 * (at 0) retarget the remaining ones to the real Event.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! has_filter( 'update_post_metadata', [ $this, 'refuse_update' ] ) ) {
			add_filter( 'update_post_metadata', [ $this, 'refuse_update' ], -5, 4 );
		}

		if ( ! has_filter( 'add_post_metadata', [ $this, 'refuse_add' ] ) ) {
			add_filter( 'add_post_metadata', [ $this, 'refuse_add' ], -5, 4 );
		}

		if ( ! has_filter( 'delete_post_metadata', [ $this, 'refuse_delete' ] ) ) {
			add_filter( 'delete_post_metadata', [ $this, 'refuse_delete' ], -5, 3 );
		}

		if ( ! has_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ] ) ) {
			// After the Event API and the Event Dates section (20) wrote their meta.
			add_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ], 30 );
		}
	}

	/**
	 * Removes the hooks added by the guard.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'update_post_metadata', [ $this, 'refuse_update' ], -5 );
		remove_filter( 'add_post_metadata', [ $this, 'refuse_add' ], -5 );
		remove_filter( 'delete_post_metadata', [ $this, 'refuse_delete' ], -5 );
		remove_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ], 30 );
		$this->refused   = [];
		$this->suspended = 0;
	}

	/**
	 * Refuses a frozen meta update.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check      Whether to short-circuit the update.
	 * @param int|mixed       $object_id  The post ID.
	 * @param string|mixed    $meta_key   The meta key.
	 * @param mixed           $meta_value The meta value.
	 *
	 * @return null|bool|mixed `true` to swallow the write, the input value otherwise.
	 */
	public function refuse_update( $check, $object_id, $meta_key, $meta_value ) {
		return $this->refuse( $check, $object_id, $meta_key, $meta_value, false );
	}

	/**
	 * Refuses a frozen meta addition.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check      Whether to short-circuit the addition.
	 * @param int|mixed       $object_id  The post ID.
	 * @param string|mixed    $meta_key   The meta key.
	 * @param mixed           $meta_value The meta value.
	 *
	 * @return null|bool|mixed `true` to swallow the write, the input value otherwise.
	 */
	public function refuse_add( $check, $object_id, $meta_key, $meta_value ) {
		return $this->refuse( $check, $object_id, $meta_key, $meta_value, false );
	}

	/**
	 * Refuses a frozen meta deletion.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check     Whether to short-circuit the deletion.
	 * @param int|mixed       $object_id The post ID.
	 * @param string|mixed    $meta_key  The meta key.
	 *
	 * @return null|bool|mixed `true` to swallow the deletion, the input value otherwise.
	 */
	public function refuse_delete( $check, $object_id, $meta_key ) {
		return $this->refuse( $check, $object_id, $meta_key, null, true );
	}

	/**
	 * Runs a callback with the guard suspended.
	 *
	 * The conversion of a rule-based Event, and the single Occurrence move of one, write
	 * the frozen meta on purpose.
	 *
	 * @since TBD
	 *
	 * @param callable $callback The callback to run.
	 *
	 * @return mixed The callback return value.
	 */
	public function allow( callable $callback ) {
		++$this->suspended;

		try {
			return $callback();
		} finally {
			--$this->suspended;
		}
	}

	/**
	 * Returns whether the dates and recurrence data of an Event are frozen.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return bool Whether the Event is frozen.
	 */
	public function is_frozen( int $post_id ): bool {
		$post_id = Occurrence::normalize_id( $post_id );

		if ( $post_id <= 0 || TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return false;
		}

		return tribe( Authoring_Guard::class )->is_rule_locked( $post_id );
	}

	/**
	 * Returns the meta keys refused for an Event during the request.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array<int,string> The refused meta keys.
	 */
	public function get_refused( int $post_id ): array {
		return $this->refused[ Occurrence::normalize_id( $post_id ) ] ?? [];
	}

	/**
	 * Records a refused write to the frozen dates or recurrence data of an Event.
	 *
	 * The guard records its own refusals; the single Occurrence update records the
	 * Occurrence moves it refuses on a rule-based Event, so the user gets the same notice.
	 *
	 * @since TBD
	 *
	 * @param int    $post_id  The Event post ID (a provisional ID is accepted).
	 * @param string $meta_key The meta key the write targeted.
	 *
	 * @return void
	 */
	public function record_refusal( int $post_id, string $meta_key ): void {
		$post_id = Occurrence::normalize_id( $post_id );

		$this->refused[ $post_id ] ??= [];
		$this->refused[ $post_id ][] = $meta_key;

		/**
		 * Fires when a write to the frozen dates or recurrence data of a rule-based Event is refused.
		 *
		 * @since TBD
		 *
		 * @param int    $post_id  The Event post ID.
		 * @param string $meta_key The meta key.
		 */
		do_action( 'tec_events_recurrence_frozen_write_refused', $post_id, $meta_key );
	}

	/**
	 * Leaves the user a notice when a Classic Editor save tried to change frozen dates.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The Event post ID.
	 *
	 * @return void
	 */
	public function on_classic_save( $event_id ): void {
		$event_id = (int) $event_id;

		if ( ! count( $this->get_refused( $event_id ) ) ) {
			return;
		}

		$message = esc_html__( 'The dates of this event follow recurrence rules created with Events Calendar Pro and were left unchanged.', 'the-events-calendar' );

		if ( tribe( Settings::class )->is_lock_enabled() ) {
			$message .= ' ' . sprintf(
				/* translators: %1$s: opening link tag to the Events settings, %2$s: closing link tag. */
				esc_html__( 'Reactivate Events Calendar Pro to edit them, or %1$sallow converting the event to individual dates%2$s in the settings.', 'the-events-calendar' ),
				'<a href="' . esc_url( tribe( Settings::class )->get_settings_url() ) . '">',
				'</a>'
			);
		} else {
			$message .= ' ' . esc_html__( 'Reactivate Events Calendar Pro to edit them, or convert the event to individual dates from the Event Dates section.', 'the-events-calendar' );
		}

		tribe( Admin_Notice::class )->set( 'warning', $message );
	}

	/**
	 * Decides whether a meta write is refused.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check      The short-circuit value provided by earlier filters.
	 * @param int|mixed       $object_id  The post ID.
	 * @param string|mixed    $meta_key   The meta key.
	 * @param mixed           $meta_value The meta value, `null` for a deletion.
	 * @param bool            $is_delete  Whether the write is a deletion.
	 *
	 * @return null|bool|mixed `true` to swallow the write, the input value otherwise.
	 */
	private function refuse( $check, $object_id, $meta_key, $meta_value, bool $is_delete ) {
		if ( null !== $check || $this->suspended > 0 ) {
			return $check;
		}

		if ( ! is_string( $meta_key ) || ! is_numeric( $object_id ) || (int) $object_id <= 0 ) {
			return $check;
		}

		$post_id = Occurrence::normalize_id( (int) $object_id );

		/**
		 * Filters the meta keys frozen on a rule-based Event.
		 *
		 * @since TBD
		 *
		 * @param array<int,string> $keys    The frozen meta keys.
		 * @param int               $post_id The Event post ID.
		 */
		$keys = (array) apply_filters( 'tec_events_recurrence_frozen_meta_keys', self::FROZEN_META_KEYS, $post_id );

		if ( ! in_array( $meta_key, $keys, true ) ) {
			return $check;
		}

		if ( ! $this->is_frozen( $post_id ) ) {
			return $check;
		}

		/**
		 * Filters whether a meta write on a rule-based Event is frozen.
		 *
		 * @since TBD
		 *
		 * @param bool   $frozen   Whether the write is frozen; `false` lets it through.
		 * @param int    $post_id  The Event post ID.
		 * @param string $meta_key The meta key.
		 */
		if ( ! tribe_is_truthy( apply_filters( 'tec_events_recurrence_freeze_meta_write', true, $post_id, $meta_key ) ) ) {
			return $check;
		}

		if ( $this->is_change( $post_id, $meta_key, $meta_value, $is_delete ) ) {
			$this->record_refusal( $post_id, $meta_key );
		}

		return true;
	}

	/**
	 * Returns whether a write would change the stored value.
	 *
	 * A plain save re-posts the current dates: those writes are swallowed silently.
	 *
	 * @since TBD
	 *
	 * @param int    $post_id    The Event post ID.
	 * @param string $meta_key   The meta key.
	 * @param mixed  $meta_value The meta value, `null` for a deletion.
	 * @param bool   $is_delete  Whether the write is a deletion.
	 *
	 * @return bool Whether the write is a change.
	 */
	private function is_change( int $post_id, string $meta_key, $meta_value, bool $is_delete ): bool {
		if ( $is_delete ) {
			return metadata_exists( 'post', $post_id, $meta_key );
		}

		$current = get_post_meta( $post_id, $meta_key, true );

		// The meta API stores scalars as strings: compare the stored shapes.
		return maybe_serialize( $current ) !== maybe_serialize( is_scalar( $meta_value ) ? (string) $meta_value : $meta_value );
	}
}
