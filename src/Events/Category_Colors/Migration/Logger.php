<?php

namespace TEC\Events\Category_Colors\Migration;

class Logger {
	/**
	 * Stores log messages categorized by type.
	 *
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
	 * @param string|null $level The log level (error, warning, info) or null for all.
	 *
	 * @return array<string, array<string>> The log messages.
	 */
	public static function get_logs( ?string $level = null ): array {
		return $level ? ( self::$logs[ $level ] ?? [] ) : self::$logs;
	}

	/**
	 * Clears all logs or logs of a specific type.
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
