<?php
/**
 * Handles registering the Table and Field handlers.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Abstract class Schema_Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
abstract class Abstract_Schema_Provider extends Service_Provider implements Schema_Provider_Interface {
	/**
	 * @inheritDoc
	 */
	public function register() {
		add_filter( 'tec_events_custom_tables_v1_table_schemas', [ $this, 'filter_table_schemas' ] );
		add_filter( 'tec_events_custom_tables_v1_field_schemas', [ $this, 'filter_field_schemas' ] );
		$this->container->singleton( static::class, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function filter_table_schemas( $schemas ) {
		return array_merge( static::get_table_schemas(), $schemas );
	}

	/**
	 * @inheritDoc
	 */
	public function filter_field_schemas( $schemas ) {
		return array_merge( static::get_field_schemas(), $schemas );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_table_schemas() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_field_schemas() {
		return [];
	}
}
