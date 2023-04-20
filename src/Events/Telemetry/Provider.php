<?php
/**
 * Service Provider for interfacing with TEC\Common\Telemetry.
 *
 * @since   TBD
 *
 * @package TEC\Events\Telemetry
 */

namespace TEC\Events\Telemetry;

use TEC\Common\lucatume\DI52\ServiceProvider as ServiceProvider;

 /**
  * Class Provider
  *
  * @since   TBD

  * @package TEC\Events\Telemetry
  */
class Provider extends ServiceProvider {
	public function register() {
		$this->add_filters();
	}

	public function add_filters() {
		add_filter( 'tec_common_telemetry_optin_args', [ $this, 'filter_tec_common_telemetry_optin_args' ] );
		add_filter( 'tribe_general_settings_debugging_section',[ $this, 'filter_tribe_general_settings_debugging_section' ] );
		add_filter( 'tribe_field_value', [ $this, 'filter_tribe_field_opt_in_status' ], 10, 2 );
		add_filter( 'tec_telemetry_slugs', [ $this, 'filter_tec_telemetry_slugs' ] );
	}

	public function filter_tec_common_telemetry_optin_args( $optin_args ) {
		return $this->container->get( Telemetry::class )->filter_tec_common_telemetry_optin_args(  $optin_args );
	}

	/**
	 * Adds the opt in/out control to the general tab debug section.
	 *
	 * @since TBD
	 *
	 * @param array<string|mixed> $fields The fields for the general tab Debugging section.
	 *
	 * @return array<string|mixed> The fields, with the optin control appended.
	 */
	public function filter_tribe_general_settings_debugging_section( $fields ): array {
		return $this->container->get( Telemetry::class )->filter_tribe_general_settings_debugging_section( $fields );
	}

	/**
	 * Ensures the admin control reflects the actual opt-in status.
	 * Note this filter is defined twice with different signatures.
	 * We take the "low road" - 2 params and test them in the later function
	 * to ensure we're only changing the thing we expect.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value  The value of the attribute.
	 * @param string $field  The field object id.
	 *
	 * @return mixed $value
	 */
	public function filter_tribe_field_opt_in_status( $value, $id )  {
		return $this->container->get( Telemetry::class )->filter_tribe_field_opt_in_status( $value, $id );
	}

	/**
	 * Let The Events Calendar add itself to the list of registered plugins for Telemetry.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $slugs The existing array of slugs.
	 *
	 * @return array<string,string> $slugs The modified array of slugs.
	 */
	public function filter_tec_telemetry_slugs( $slugs ) {
		return $this->container->get( Telemetry::class )->filter_tec_telemetry_slugs( $slugs );
	}
}
