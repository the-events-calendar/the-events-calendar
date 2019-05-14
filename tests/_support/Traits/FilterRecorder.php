<?php
/**
 * Provides filters assertions
 *
 * @package Tribe\Events\Test\Traits
 * @since TBD
 */

namespace Tribe\Events\Test\Traits;


trait FilterRecorder {

	/**
	 * Stores the callbacks recorded so far.
	 *
	 * @var array
	 */
	private $recorded_callbacks = [];

	/**
	 * Starts recording filters and the callbacks on them.
	 *
	 * Use this method as late as you can: filtering `all` filters is, by no means, efficient.
	 *
	 * @param  int  $debug_backtrace_limit  The debug backtrace limit, a value of `0` will prevent the trace recording.j
	 */
	protected function record_filter_callbacks( $debug_backtrace_limit = 0 ) {
		add_filter( 'all', function () use ( $debug_backtrace_limit ) {
			global $wp_filter;
			$tag = current_filter();

			if ( empty( $wp_filter[ $tag ] ) ) {
				return;
			}

			$current_filter = $wp_filter[ $tag ];

			if ( empty( $current_filter->callbacks ) ) {
				return;
			}

			foreach ( $current_filter->callbacks as $priority => $callbacks ) {
				$classes_and_methods = array_reduce( $callbacks,
					static function ( array $buffer, $callback ) use (
						$debug_backtrace_limit,
						$priority
					) {
						if ( is_int( $callback ) ) {
							return $buffer;
						}

						$the_function = $callback['function'];

						$class  = '';
						$method = '';
						if ( is_string( $the_function ) ) {
							if ( ! function_exists( $the_function ) ) {
								return $buffer;
							}
							$class = $the_function;
						} elseif ( is_array( $the_function ) ) {
							$class  = is_string( $the_function[0] ) ? $the_function[0] : get_class( $the_function[0] );
							$method = $the_function[1];
						} elseif ( $the_function instanceof \Closure ) {
							$class = ( new \ReflectionMethod( $the_function ) )->name;
						}

						$entry = '' !== $method
							? [ 'class' => $class, 'method' => $method ]
							: [ 'function' => $class ];

						$entry['priority']      = $priority;
						$entry['accepted_args'] = $callback['accepted_args'];

						if ( ! empty( $debug_backtrace_limit ) ) {
							$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $debug_backtrace_limit );

							// Clean the trace removing line, type and file.
							$trace          = array_map( static function ( array $trace_entry ) {
								$clean = $trace_entry;
								unset( $clean['file'], $clean['line'], $clean['type'] );

								return $clean;
							}, $trace );
							$entry['trace'] = $trace;
						}

						$buffer[] = $entry;

						return $buffer;
					}, [] );
			}

			if ( ! empty( $classes_and_methods ) ) {
				$this->recorded_callbacks[ $tag ] = $classes_and_methods;
			}
		} );
	}

	/**
	 * Returns a list of recorded callbacks whose class, or function name, contains a string.
	 *
	 * The function filtering is case insensitive.
	 *
	 * @param  string  $string  The string to use for the filtering, it can be a regular expression too.
	 *
	 * @return array An array of classes and methods, or functions, whose name contains the specified string, by filter.
	 */
	protected function get_recorded_filter_callbacks_containing( $string ) {
		$is_regex = tribe_is_regex( $string );

		$matches = [];

		foreach ( $this->recorded_callbacks as $filter_tag => $the_recorded_callbacks ) {
			$filtered = array_filter( $the_recorded_callbacks, function ( $callback ) use ( $is_regex, $string ) {
				$search = $callback['class'] ?? $callback['function'];

				if ( isset( $callback['class'] ) && __CLASS__ === $callback['class'] ) {
					// Let's exclude the class that is using the trait from the results to reduce noise.
					return false;
				}

				return $is_regex
					? preg_match( $string, $search )
					: false !== stripos( $search, $string );
			} );

			if ( count( $filtered ) ) {
				$matches[ $filter_tag ] = $filtered;
			}
		}

		return $matches;
	}
}
