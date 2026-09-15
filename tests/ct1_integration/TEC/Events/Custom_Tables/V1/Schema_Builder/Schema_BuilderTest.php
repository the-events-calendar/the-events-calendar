<?php

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Fields\Events as Events_Fields;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;

class Schema_BuilderTest extends WPTestCase {
	/**
	 * It should dedupe duplicate table schema registrations
	 *
	 * @test
	 */
	public function should_dedupe_duplicate_table_schema_registrations(): void {
		$builder = tribe( Schema_Builder::class );

		$before = $builder->get_registered_table_schemas();

		$add_duplicates = static function ( array $schemas ): array {
			$schemas[] = tribe( Events::class );
			$schemas[] = tribe( Occurrences::class );

			return $schemas;
		};

		add_filter( 'tec_events_custom_tables_v1_table_schemas', $add_duplicates, 100 );

		try {
			$after = $builder->get_registered_table_schemas();
		} finally {
			remove_filter( 'tec_events_custom_tables_v1_table_schemas', $add_duplicates, 100 );
		}

		$this->assertCount( count( $before ), $after, 'Duplicate table schema registrations must collapse.' );
	}

	/**
	 * It should dedupe duplicate field schema registrations by version option
	 *
	 * @test
	 */
	public function should_dedupe_duplicate_field_schema_registrations_by_version_option(): void {
		$builder = tribe( Schema_Builder::class );

		$before = $builder->get_registered_field_schemas();

		/*
		 * A second plugin registering a field schema managing the same schema version
		 * option (e.g. an Events Calendar Pro version registering the recurrence
		 * columns): only the first registration should survive.
		 */
		$twin = new class extends Abstract_Custom_Field {
			const SCHEMA_VERSION_OPTION = Events_Fields::SCHEMA_VERSION_OPTION;
			const SCHEMA_VERSION        = Events_Fields::SCHEMA_VERSION;

			public function fields() {
				return [ 'rset' ];
			}

			protected function get_update_sql() {
				return '';
			}

			public function table_schema() {
				return tribe( Events::class );
			}
		};

		$add_twin = static function ( array $schemas ) use ( $twin ): array {
			$schemas[] = $twin;

			return $schemas;
		};

		add_filter( 'tec_events_custom_tables_v1_field_schemas', $add_twin, 100 );

		try {
			$after = $builder->get_registered_field_schemas();
		} finally {
			remove_filter( 'tec_events_custom_tables_v1_field_schemas', $add_twin, 100 );
		}

		$this->assertCount( count( $before ), $after, 'Twin field schema registrations must collapse on the version option.' );
		$this->assertNotContains( $twin, $after, 'The first registration wins on equal versions.' );
	}

	/**
	 * It should register the recurrence field schemas unconditionally
	 *
	 * @test
	 */
	public function should_register_the_recurrence_field_schemas_unconditionally(): void {
		$field_schemas = tribe( Schema_Builder::class )->get_registered_field_schemas();

		$options = array_map(
			static function ( $schema ): string {
				return (string) constant( get_class( $schema ) . '::SCHEMA_VERSION_OPTION' );
			},
			$field_schemas
		);

		$this->assertContains( 'tec_ct1_events_field_schema_version', $options );
		$this->assertContains( 'tec_ct1_occurrences_field_schema_version', $options );
	}

	/**
	 * It should keep the field schema declaring the highest version
	 *
	 * During a transition window two plugins can declare different versions of the same
	 * fields: the newer definition owns the `dbDelta` run, whatever the registration order.
	 *
	 * @test
	 */
	public function should_keep_the_field_schema_declaring_the_highest_version(): void {
		$builder = tribe( Schema_Builder::class );

		$newer = new class extends Abstract_Custom_Field {
			const SCHEMA_VERSION_OPTION = Events_Fields::SCHEMA_VERSION_OPTION;
			const SCHEMA_VERSION        = '99.0.0';

			public function fields() {
				return [ 'rset' ];
			}

			protected function get_update_sql() {
				return '';
			}

			public function table_schema() {
				return tribe( Events::class );
			}
		};

		$finder = static function ( array $schemas ) {
			foreach ( $schemas as $schema ) {
				if ( Events_Fields::SCHEMA_VERSION_OPTION === constant( get_class( $schema ) . '::SCHEMA_VERSION_OPTION' ) ) {
					return $schema;
				}
			}

			return null;
		};

		// Registered after the built-in one.
		$append = static function ( array $schemas ) use ( $newer ): array {
			$schemas[] = $newer;

			return $schemas;
		};
		add_filter( 'tec_events_custom_tables_v1_field_schemas', $append, 100 );
		try {
			$this->assertSame( $newer, $finder( $builder->get_registered_field_schemas() ), 'A later, newer registration must win.' );
		} finally {
			remove_filter( 'tec_events_custom_tables_v1_field_schemas', $append, 100 );
		}

		// Registered before the built-in one.
		$prepend = static function ( array $schemas ) use ( $newer ): array {
			array_unshift( $schemas, $newer );

			return $schemas;
		};
		add_filter( 'tec_events_custom_tables_v1_field_schemas', $prepend, 1 );
		try {
			$this->assertSame( $newer, $finder( $builder->get_registered_field_schemas() ), 'An earlier, newer registration must win.' );
		} finally {
			remove_filter( 'tec_events_custom_tables_v1_field_schemas', $prepend, 1 );
		}

		// Same version: the first registration wins, the built-in one here.
		$this->assertInstanceOf( Events_Fields::class, $finder( $builder->get_registered_field_schemas() ) );
	}
}
