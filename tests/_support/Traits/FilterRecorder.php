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

			$current_filter      = $wp_filter[ $tag ];
			$classes_and_methods = array_reduce( $current_filter->callbacks,
				static function ( array $buffer, array $filter_callbacks ) use ( $debug_backtrace_limit ) {
					foreach ( $filter_callbacks as $priority => $callbacks ) {
						foreach ( $callbacks as $the_function ) {
							if ( is_int( $the_function ) ) {
								continue;
							}

							$class  = '';
							$method = '';
							if ( is_string( $the_function ) ) {
								if ( ! function_exists( $the_function ) ) {
									continue;
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
						}
					}

					return $buffer;
				}, [] );

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

		return array_filter( $this->recorded_callbacks,
			static function ( array $callbacks ) use ( $string, $is_regex ) {
				return count( array_filter( $callbacks, function ( array $callback ) use ( $string, $is_regex ) {
					$search = $callback['class'] ?? $callback['function'];

					if ( isset( $callback['class'] ) && __CLASS__ === $callback['class'] ) {
						// Let's exclude the class that is using the trait from the results to reduce noise.
						return false;
					}

					return $is_regex
						? preg_match( $string, $search )
						: false !== stripos( $search, $string );
				} ) );
			} );
	}
}
