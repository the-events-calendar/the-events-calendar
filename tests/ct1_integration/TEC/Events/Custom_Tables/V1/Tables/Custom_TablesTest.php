<?php

namespace TEC\Events\Custom_Tables\V1\Tables;

use WP_Post;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Tables\Events;

class Custom_TablesTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * Should successfully drop custom tables.
	 *
	 * @test
	 */
	public function should_drop_custom_tables() {
		global $wpdb;
		// Should have our custom tables.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertContains( Occurrences::table_name( true ), $tables );
		$this->assertContains( Events::table_name( true ), $tables );

		// Should drop successfully.
		$occurrence_table = tribe( Occurrences::class );
		$event_table      = tribe( Events::class );
		$this->assertTrue( $occurrence_table->drop_table() );
		$this->assertTrue( $event_table->drop_table() );

		// Tables should be gone.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( Occurrences::table_name( true ), $tables );
		$this->assertNotContains( Events::table_name( true ), $tables );
	}

}
