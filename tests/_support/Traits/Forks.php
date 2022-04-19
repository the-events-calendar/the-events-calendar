<?php
/**
 * Provides methods to test functionality in forks.
 *
 * Forks are only NOT available on Windows.
 * Forks are powerful and tricky, use them wisely.
 *
 * @package Tribe\Events\Test\Traits;
 */

namespace Tribe\Events\Test\Traits;

/**
 * Trait Forks.
 *
 * @package Tribe\Events\Test\Traits;
 */
trait Forks {

	/**
	 * Do something in a fork. No bells and whistles.
	 *
	 * Codeception does not like `exit` at all, but it's the correct flow control
	 * instruction to use in a fork, as such this method will use a trick to avoid
	 * Codeception from complaining.
	 *
	 * @param callable $do The thing to do in the fork. All side effects will happen
	 *                     in the fork variable scope, the main process variable scope
	 *                     will not be affected.
	 *
	 * @return int In the main process, this method will return the child process PID
	 *             (Process ID); in the child process this method will `exit`;
	 */
	private function in_fork_do( callable $do ) {
		$pid = pcntl_fork();

		if ( $pid < 0 ) {
			$reason = pcntl_strerror( pcntl_get_last_error() );
			throw new \RuntimeException( "Failed to spawn process fork: $reason" );
		}

		if ( $pid === 0 ) {
			// Child process.

			// Buffer everything and avoid Codeception 'COMMAND DID NOT FINISH PROPERLY' message.
			ob_start( static function () {
				global $wpdb;
				$wpdb->close();
			} );

			// Close and re-open the db connection as resources are not shared in forks.
			global $wpdb;
			$wpdb->close();
			$wpdb->check_connection( false );

			$do();

			exit;
		}

		// Main process.
		return $pid;
	}

	/**
	 * Loop until completion over a set of forks with a maximum parallelism set.
	 *
	 * @param \Generator<callable> $forks_generator A generator that will return the callable to call
	 *                                              inside each fork.
	 * @param int                  $parallelism     The amount of parallel workers to try and set up.
	 *
	 * @return array<int> The pcntl_status of each completed fork, to be used as input
	 *                    to `pcntl_wifexited` and similar functions.
	 */
	private function fork_loop_wait( \Generator $forks_generator, $parallelism = 1 ) {
		$forks_generator->rewind();
		$pids      = [];
		$completed = [];

		// Start an initial set of workers up to the parallelism value.
		while ( count( $pids ) < $parallelism ) {
			$op     = $forks_generator->current();
			$pids[] = $this->in_fork_do( $op );
			$forks_generator->next();
		}

		$this->assertLessThanOrEqual(
			$parallelism,
			count( $pids ),
			"It should have initially spawned up to $parallelism workers."
		);

		// As soon as a worker is done, enqueue a new worker.
		do {
			// Blocking, wait for a worker to terminate.
			pcntl_wait( $status, WUNTRACED );
			$completed[] = $status;
			$op     = $forks_generator->current();
			$pids[] = $this->in_fork_do( $op );
			$forks_generator->next();
		} while ( $forks_generator->valid() );


		// Wait for all processes to have completed.
		while ( count( $completed ) !== count( $pids ) ) {
			pcntl_wait( $status, WUNTRACED );
			$completed[] = $status;
		}

		$this->assertCount(
			count( $pids ), $completed, 'All processes should have completed.'
		);

		return $completed;
	}
}