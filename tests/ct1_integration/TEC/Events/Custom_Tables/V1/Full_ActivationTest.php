<?php
namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;

class Full_ActivationTest extends \Codeception\TestCase\WPTestCase
{
	/**
	 * It should add custom tables names as properties on wpdb object
	 *
	 * @test
	 */
	public function should_add_custom_tables_names_as_properties_on_wpdb_object() {
		$schema = tribe( Schema_Builder::class );
		$table_schemas = $schema->get_registered_table_schemas();
		$this->assertNotEmpty( $table_schemas );
		global $wpdb;

		foreach ( $table_schemas as $table_schema ) {
			$class = get_class( $table_schema );
			$table_name = $class::base_table_name();
			$this->assertEquals( $wpdb->prefix . $table_name, $wpdb->{$table_name} );
		}
	}
}
