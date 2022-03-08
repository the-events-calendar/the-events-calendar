<?php
/**
 * Provides methods to manage database transactions.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Database_Transactions.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */
trait With_Database_Transactions {
	/**
	 * Returns whether all tables in a set use the InnoDB engine or not.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return bool Whether database transactions are supported or not.
	 */
	private function supports_transactions() {
		global $wpdb;
		$check = $this->tables_use_innodb( [ $wpdb->posts, $wpdb->postmeta ] );

		/**
		 * Filters whether database transactions are supported or not.
		 *
		 * The initial value is set checking if the ENGINE of the `posts`
		 * and `postmeta` table is the INNODB one or not.
		 *
		 * @since TBD
		 *
		 * @param bool $check Whether transactions are supported or not.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_db_transactions_supported', $check );
	}

	private function rollback_transaction() {
		// @todo
	}
}