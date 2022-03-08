<?php

namespace TEC\Events\Custom_Tables\V1\Traits;

class With_Database_TransactionsTest extends \CT1_Migration_Test_Case {
	use With_Database_Transactions;

	private $test_tables = [
		'__test_innodb_posts',
		'__test_innodb_postmeta',
		'__test_myisam_posts',
		'__test_myisam_postmeta',
	];

	/**
	 * @before
	 * @after
	 */
	public function drop_test_tables() {
		global $wpdb;
		foreach ( $this->test_tables as $table ) {
			if ( false === $wpdb->query( "DROP TABLE IF EXISTS `$table`" ) ) {
				throw new \RuntimeException( "Failed to DROP test table $table" );
			}
		}
	}

	/**
	 * It should correctly spot table engines
	 *
	 * @test
	 */
	public function should_correctly_spot_table_engines() {
		global $wpdb;
		$innodb_posts_table = $wpdb->query( "CREATE TABLE __test_innodb_posts  (id INTEGER, value INTEGER) ENGINE=INNODB" );
		if ( false === $innodb_posts_table ) {
			throw new \RuntimeException( 'Failed to create test INNODB posts table: ' . $wpdb->last_error );
		}
		$innodb_postmeta_table = $wpdb->query( 'CREATE TABLE __test_innodb_postmeta (id INTEGER, value INTEGER) ENGINE=INNODB' );
		if ( false === $innodb_postmeta_table ) {
			throw new \RuntimeException( 'Failed to create test INNODB postmeta table: ' . $wpdb->last_error );
		}
		$myisam_posts_table = $wpdb->query( 'CREATE TABLE __test_myisam_posts (id INTEGER, value INTEGER) ENGINE=MYISAM' );
		if ( false === $myisam_posts_table ) {
			throw new \RuntimeException( 'Failed to create test MYISAM posts table: ' . $wpdb->last_error );
		}
		$myisam_postmeta_table = $wpdb->query( 'CREATE TABLE __test_myisam_postmeta (id INTEGER, value INTEGER) ENGINE=MYISAM' );
		if ( false === $myisam_postmeta_table ) {
			throw new \RuntimeException( 'Failed to create test MYISAM postmeta table: ' . $wpdb->last_error );
		}

		$this->assertTrue( $this->tables_use_innodb( [ '__test_innodb_posts', '__test_innodb_postmeta' ] ) );
		$this->assertFalse( $this->tables_use_innodb( [ '__test_myisam_posts', '__test_myisam_postmeta' ] ) );
	}
}