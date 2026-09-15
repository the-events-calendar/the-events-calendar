<?php
/**
 * Detects whether the site has ever used Events Calendar Pro.
 *
 * Sites that once ran Events Calendar Pro can carry rule-based recurring Events and
 * Series links only Pro knows how to author. The detection reads the durable traces
 * Pro leaves behind (schema version options, its settings, its license, its tables and
 * posts) so the safeguards protecting that data can be offered while Pro is inactive.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Events\Custom_Tables\V1\Tables\Events;

/**
 * Class Pro_History.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Pro_History {
	/**
	 * The option memoizing the first signal that detected Events Calendar Pro.
	 *
	 * @since TBD
	 */
	public const MEMO_OPTION = 'tec_events_recurrence_pro_history';

	/**
	 * The transient memoizing, for a day, that no signal detected Events Calendar Pro.
	 *
	 * @since TBD
	 */
	public const NEGATIVE_TRANSIENT = 'tec_events_recurrence_pro_history_none';

	/**
	 * The Series post type Events Calendar Pro registers.
	 *
	 * @since TBD
	 */
	public const SERIES_POST_TYPE = 'tribe_event_series';

	/**
	 * The unprefixed name of the Series relationships table Events Calendar Pro creates.
	 *
	 * @since TBD
	 */
	public const SERIES_RELATIONSHIPS_TABLE = 'tec_series_relationships';

	/**
	 * The option Events Calendar Pro's Series relationships table schema versions itself with.
	 *
	 * @since TBD
	 */
	public const SERIES_SCHEMA_OPTION = 'tec_ct1_series_relationship_table_schema_version';

	/**
	 * The result of the detection in this request, `null` before it ran.
	 *
	 * @since TBD
	 *
	 * @var bool|null
	 */
	private ?bool $detected = null;

	/**
	 * Whether the Series relationships table exists, `null` before it was checked.
	 *
	 * @since TBD
	 *
	 * @var bool|null
	 */
	private static ?bool $table_exists = null;

	/**
	 * Returns whether the site has ever used Events Calendar Pro.
	 *
	 * The signals are read cheapest first and the first positive one is memoized in an
	 * option: having used Pro is a fact that does not change back.
	 *
	 * @since TBD
	 *
	 * @return bool Whether Events Calendar Pro was ever used on the site.
	 */
	public function has_pro_history(): bool {
		if ( null === $this->detected ) {
			$signal         = $this->detect();
			$this->detected = '' !== $signal;

			if ( $this->detected ) {
				$this->mark_detected( $signal );
			} else {
				// A clean site stays clean until Pro shows up (checked first): spare the queries meanwhile.
				set_transient( self::NEGATIVE_TRANSIENT, 1, DAY_IN_SECONDS );
			}
		}

		/**
		 * Filters whether the site is detected as having used Events Calendar Pro.
		 *
		 * @since TBD
		 *
		 * @param bool   $detected Whether Events Calendar Pro was detected.
		 * @param string $signal   The signal that detected it, empty when none did.
		 */
		return tribe_is_truthy( apply_filters( 'tec_events_recurrence_pro_history_detected', $this->detected, $this->detected ? (string) get_option( self::MEMO_OPTION, '' ) : '' ) );
	}

	/**
	 * Memoizes a positive detection, so the detection queries run once per site.
	 *
	 * @since TBD
	 *
	 * @param string|mixed $signal The signal that detected Events Calendar Pro.
	 *
	 * @return void
	 */
	public function mark_detected( $signal ): void {
		$this->detected = true;

		if ( ! is_string( $signal ) || '' === $signal ) {
			$signal = 'unknown';
		}

		if ( '' === (string) get_option( self::MEMO_OPTION, '' ) ) {
			add_option( self::MEMO_OPTION, $signal, '', 'yes' );
		}

		delete_transient( self::NEGATIVE_TRANSIENT );
	}

	/**
	 * Forgets the memoized detection, in the request and in the database.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->detected     = null;
		self::$table_exists = null;
		delete_option( self::MEMO_OPTION );
		delete_transient( self::NEGATIVE_TRANSIENT );
	}

	/**
	 * Returns the prefixed name of the Series relationships table.
	 *
	 * @since TBD
	 *
	 * @return string The prefixed table name.
	 */
	public static function series_relationships_table(): string {
		global $wpdb;

		return $wpdb->prefix . self::SERIES_RELATIONSHIPS_TABLE;
	}

	/**
	 * Returns whether the Series relationships table exists in the database.
	 *
	 * Events Calendar Pro creates the table and nothing removes it on deactivation.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the table exists.
	 */
	public static function series_relationships_table_exists(): bool {
		if ( null !== self::$table_exists ) {
			return self::$table_exists;
		}

		global $wpdb;

		$table = self::series_relationships_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );

		self::$table_exists = $found === $table;

		return self::$table_exists;
	}

	/**
	 * Forgets whether the Series relationships table exists, so the next read checks again.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public static function reset_table_check(): void {
		self::$table_exists = null;
	}

	/**
	 * Runs the detection signals, cheapest first.
	 *
	 * @since TBD
	 *
	 * @return string The slug of the first positive signal, empty when none is.
	 */
	private function detect(): string {
		global $wpdb;

		if ( '' !== (string) get_option( self::MEMO_OPTION, '' ) ) {
			return (string) get_option( self::MEMO_OPTION );
		}

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			return 'pro_active';
		}

		if ( get_transient( self::NEGATIVE_TRANSIENT ) ) {
			return '';
		}

		if ( ! empty( get_option( self::SERIES_SCHEMA_OPTION ) ) ) {
			return 'series_schema_option';
		}

		if ( ! empty( tribe_get_option( 'pro-schema-version' ) ) ) {
			return 'pro_schema_version';
		}

		if ( ! empty( get_option( 'pue_install_key_events_calendar_pro' ) ) ) {
			return 'pro_license';
		}

		if ( self::series_relationships_table_exists() ) {
			return 'series_relationships_table';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$series_post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1", self::SERIES_POST_TYPE ) );

		if ( ! empty( $series_post ) ) {
			return 'series_posts';
		}

		$events_table = Events::table_name( true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rule_event = $wpdb->get_var( "SELECT event_id FROM {$events_table} WHERE rset LIKE '%RRULE%' LIMIT 1" );

		if ( ! empty( $rule_event ) ) {
			return 'rule_based_events';
		}

		return '';
	}
}
