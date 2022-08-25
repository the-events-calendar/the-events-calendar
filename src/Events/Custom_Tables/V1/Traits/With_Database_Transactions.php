<?php
/**
 * Provides methods to manage database transactions.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Database_Transactions.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */
trait With_Database_Transactions {
	/**
	 * A flag property that will be set by the transaction methods
	 * to keep track whether the current queries are running in a transaction
	 * or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $transaction_started = false;

	/**
	 * Returns whether all tables in a set use the InnoDB engine or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $tables The list of tables to check.
	 *
	 * @return bool Whether all tables in a set use the InnoDB engine or not.
	 */
	private function tables_use_innodb( array $tables = [] ) {
		global $wpdb;
		$tables_list = $wpdb->prepare(
			implode( ', ', array_fill( 0, count( $tables ), '%s' ) ),
			...$tables
		);
		$table_stati = $wpdb->get_results(
			"SHOW TABLE STATUS WHERE `Name` IN ($tables_list)"
		);

		$innodb_tables = array_filter( $table_stati, static function ( $table_status ) {
			return isset( $table_status->Engine ) && 'INNODB' === strtoupper( $table_status->Engine );
		} );

		return count( $innodb_tables ) === count( $tables );
	}

	/**
	 * Returns whether database transactions are supported or not.
	 *
	 * The method will look up the `posts` and `postmeta` table and
	 * check if the ENGINE is INNODB to assume transactions are supported.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null The table prefix to check for, or `null` to use
	 *                    the current one.
	 *
	 * @return bool Whether database transactions are supported or not.
	 */
	private function transactions_supported( $table_prefix = null ) {
		global $wpdb;
		$table_prefix = null === $table_prefix ? $wpdb->prefix : $table_prefix;
		$cache        = tribe( 'cache' );
		$check        = $cache[ __METHOD__ . '_' . $table_prefix ];

		if ( false === $check ) {
			$posts_table                               = $table_prefix . 'posts';
			$postmeta_table                            = $table_prefix . 'postmeta';
			$check                                     = (int) $this->tables_use_innodb( [
				$posts_table,
				$postmeta_table
			] );
			$cache[ __METHOD__ . '_' . $table_prefix ] = $check;
		}

		/**
		 * Filters whether database transactions are supported or not.
		 *
		 * The initial value is set checking if the ENGINE of the `posts`
		 * and `postmeta` table is the INNODB one or not.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $check Whether transactions are supported or not.
		 */
		$check = apply_filters( 'tec_events_custom_tables_v1_db_transactions_supported', (bool) $check );
		if ( ! $check ) {
			do_action( 'tribe_log', 'debug', 'Check transaction support.', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
				'check'  => $check
			] );
		}

		return $check;
	}

	/**
	 * Starts a transaction if supported and possible.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the transaction started or not.
	 */
	private function transaction_start() {
		global $wpdb;

		if ( ! $this->transactions_supported( $wpdb->prefix ) ) {
			return false;
		}

		$wpdb->suppress_errors     = true;
		$started                   = $wpdb->query( 'START TRANSACTION' );
		$this->transaction_started = true;

		return false !== $started;
	}

	/**
	 * Rollback the current transaction if supported and possible.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the transaction was rolled back or not.
	 */
	private function transaction_rollback() {
		if ( ! $this->transaction_started ) {
			return false;
		}

		global $wpdb;

		if ( ! $this->transactions_supported( $wpdb->prefix ) ) {
			return false;
		}

		$wpdb->suppress_errors     = true;
		$rolled_back               = $wpdb->query( 'ROLLBACK' );
		$this->transaction_started = false;

		return false !== $rolled_back;
	}

	/**
	 * Commits the current transaction if supported and possible.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the transaction was committed or not.
	 */
	private function transaction_commit() {
		if ( ! $this->transaction_started ) {
			return false;
		}

		global $wpdb;

		if ( ! $this->transactions_supported( $wpdb->prefix ) ) {
			return false;
		}

		$wpdb->suppress_errors     = true;
		$committed                 = $wpdb->query( 'COMMIT' );
		$this->transaction_started = false;

		return false !== $committed;
	}
}