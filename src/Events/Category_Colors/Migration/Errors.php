<?php

namespace TEC\Events\Category_Colors\Migration;

class Errors {

	/**
	 * Stores recorded errors across all instances.
	 *
	 * @var array<string> List of error messages.
	 */
	protected static array $errors = [];

	/**
	 * Records an error message for later retrieval.
	 *
	 * This allows checking for errors even if logging is disabled.
	 *
	 * @since TBD
	 *
	 * @param string $message The error message to store.
	 *
	 * @return void
	 */
	public static function add_error( string $message ): void {
		static::$errors[] = $message;
	}

	/**
	 * Checks if any errors have been recorded.
	 *
	 * @since TBD
	 *
	 * @return bool True if errors exist, false otherwise.
	 */
	public static function has_errors(): bool {
		return ! empty( static::$errors );
	}

	/**
	 * Retrieves all recorded errors.
	 *
	 * @since TBD
	 *
	 * @return array<string> List of error messages.
	 */
	public static function get_errors(): array {
		return static::$errors;
	}

	/**
	 * Clears all recorded errors.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public static function clear_errors(): void {
		static::$errors = [];
	}
}
