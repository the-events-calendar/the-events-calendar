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
	 * @before
	 * @after
	 */
	public function rollback_transactions() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
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
		$this->assertTrue( $this->transactions_supported( '__test_innodb_' ) );
		$this->assertFalse( $this->transactions_supported( '__test_myisam_' ) );
	}

	/**
	 * It should allow filtering transaction support
	 *
	 * @test
	 */
	public function should_allow_filtering_transaction_support() {
		global $wpdb;
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_false' );

		$this->assertFalse( $this->transactions_supported( $wpdb->prefix ) );

		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_true' );

		$this->assertTrue( $this->transactions_supported( $wpdb->prefix ) );
	}

	/**
	 * It should start transaction when supported
	 *
	 * @test
	 */
	public function should_start_transaction_when_supported() {
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_true' );

		$started = $this->transaction_start();

		$this->assertTrue($this->transaction_started);
	}

	/**
	 * It should not start transaction when not supported
	 *
	 * @test
	 */
	public function should_not_start_transaction_when_not_supported() {
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_false' );

		$started = $this->transaction_start();

		$this->assertFalse( $started );
	}

	/**
	 * It should rollback started transactions correctly
	 *
	 * @test
	 */
	public function should_rollback_started_transactions_correctly() {
		global $wpdb;
		$post = static::factory()->post->create_and_get();
		$this->assertInstanceOf( \WP_Post::class, $post );

		$started = $this->transaction_start();
		$this->assertTrue( $started );

		add_post_meta( $post->ID, '__test_key', '__test_value' );

		// Direct query to skip cache.
		$this->assertEquals(
			'__test_value',
			$wpdb->get_var( "select meta_value from $wpdb->postmeta where meta_key = '__test_key' and post_id = $post->ID" )
		);

		$rolled_back = $this->transaction_rollback();

		$this->assertTrue( $rolled_back );

		// Direct query to skip cache.
		$this->assertEmpty(
			$wpdb->get_var( "select meta_value from $wpdb->postmeta where meta_key = '__test_key' and post_id = $post->ID" )
		);
	}

	/**
	 * It should commit started transactions correctly
	 *
	 * @test
	 */
	public function should_commit_started_transactions_correctly() {
		global $wpdb;
		$post = static::factory()->post->create_and_get();
		$this->assertInstanceOf( \WP_Post::class, $post );

		$started = $this->transaction_start();
		$this->assertTrue( $started );

		add_post_meta( $post->ID, '__test_key', '__test_value' );

		// Direct query to skip cache.
		$this->assertEquals(
			'__test_value',
			$wpdb->get_var( "select meta_value from $wpdb->postmeta where meta_key = '__test_key' and post_id = $post->ID" )
		);

		$rolled_back = $this->transaction_commit();

		$this->assertTrue( $rolled_back );

		// Direct query to skip cache.
		$this->assertEquals(
			'__test_value',
			$wpdb->get_var( "select meta_value from $wpdb->postmeta where meta_key = '__test_key' and post_id = $post->ID" )
		);
	}
}