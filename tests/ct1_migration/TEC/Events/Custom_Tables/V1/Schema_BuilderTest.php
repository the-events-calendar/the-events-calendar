<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Schema_BuilderTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * @after each test make sure the custom tables will be there for the following ones.
	 */
	public function recreate_custom_tables() {
		$events_updated = ( new EventsSchema )->update();
		if ( ! $events_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
		$occurrences_updated = ( new OccurrencesSchema() )->update();
		if ( ! $occurrences_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
	}

	/**
	 *
	 *
	 * @test
	 */
	public function should_up_schema() {
		global $wpdb;
		$schema_builder = tribe(Schema_Builder::class);

		// Activate.
		$up = $schema_builder->up();

		// Validate expected state.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertContains( EventsSchema::table_name( true ), $tables );
		//$this->assertNotEmpty($up);
	}

	/**
	 * @test
	 */
	public function should_down_schema() {
		global $wpdb;
		$schema_builder = tribe(Schema_Builder::class);

		// Activate.
		$schema_builder->down();

		// Validate expected state.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
	}

}