<?php
/**
 * Handles logging for the category color migration process.
 * Provides structured logging for errors, warnings, and informational messages.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Logger
 * Manages logging throughout the migration process.
 * Supports error, warning, and informational logs to track migration progress.
 * Logs can be retrieved at any stage to assist with debugging.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Logger {
	/**
	 * Stores log messages categorized by type.
	 *
	 * @since TBD
	 * @var array<string, array<string>>
	 */
	protected static array $logs = [
		'error'   => [],
		'warning' => [],
		'info'    => [],
	];

	/**
	 * Adds a message to the log.
	 *
	 * @since TBD
	 *
	 * @param string $level   The log level (error, warning, info).
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	public static function log( string $level, string $message ): void {
		if ( ! isset( self::$logs[ $level ] ) ) {
			self::$logs[ $level ] = [];
		}

		self::$logs[ $level ][] = $message;
	}

	/**
	 * Retrieves logs by level.
	 *
	 * @since TBD
	 *
	 * @param string|null $level The log level (error, warning, info) or null for all.
	 *
	 * @return array<string, array<string>> The log messages.
	 */
	public static function get_logs( ?string $level = null ): array {
		return $level ? ( self::$logs[ $level ] ?? [] ) : self::$logs;
	}

	/**
	 * Checks if there are any logs of a given level.
	 *
	 * @since TBD
	 *
	 * @param string $level The log level to check ('error', 'warning', 'info').
	 *
	 * @return bool True if logs exist for the given level, false otherwise.
	 */
	public static function has_logs( string $level ): bool {
		if ( ! isset( self::$logs[ $level ] ) ) {
			return false;
		}

		return ! empty( self::$logs[ $level ] );
	}

	/**
	 * Clears all logs or logs of a specific type.
	 *
	 * @since TBD
	 *
	 * @param string|null $level The log level to clear or null for all logs.
	 *
	 * @return void
	 */
	public static function clear_logs( ?string $level = null ): void {
		if ( $level ) {
			self::$logs[ $level ] = [];
		} else {
			self::$logs = [
				'error'   => [],
				'warning' => [],
				'info'    => [],
			];
		}
	}
}
