<?php
/**
 * Service Provider for interfacing with TEC\Common\Telemetry.
 *
 * @since   6.1.0
 *
 * @package TEC\Events\Telemetry
 */

namespace TEC\Events\Telemetry;

use TEC\Common\Contracts\Service_Provider;

 /**
  * Class Provider
  *
  * @since   6.1.0
  * @package TEC\Events\Telemetry
  */
class Provider extends Service_Provider {
	/**
	 * Handles the registering of the provider.
	 *
	 * @since 6.1.0
	 */
	public function register() {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Handles the inclusion of the Filters for this module.
	 *
	 * @since 6.1.0
	 */
	public function add_filters() {
		add_filter( 'tec_common_telemetry_optin_args', [ $this, 'filter_tec_common_telemetry_optin_args' ] );
		add_filter( 'tribe_general_settings_debugging_section',[ $this, 'filter_tribe_general_settings_debugging_section' ] );
		add_filter( 'tribe_field_value', [ $this, 'filter_tribe_field_opt_in_status' ], 10, 2 );
		add_filter( 'tec_telemetry_slugs', [ $this, 'filter_tec_telemetry_slugs' ] );
	}

	/**
	 * Handles the action hooks for this module.
	 *
	 * @since 6.1.0
	 */
	public function add_actions() {
		add_action( 'admin_footer', [ $this, 'action_inject_modal_link' ] );
		add_action( 'tribe_settings_save_field_opt-in-status', [ $this, 'action_save_opt_in_setting_field' ] );
	}

	/**
	 * Filter the telemetry opt-in arguments.
	 *
	 * @since 6.1.0
	 *
	 * @param array $optin_args Previous set of args we are changing.
	 *
	 * @return array
	 */
	public function filter_tec_common_telemetry_optin_args( $optin_args ) {
		return $this->container->get( Telemetry::class )->filter_tec_common_telemetry_optin_args( $optin_args );
	}

	/**
	 * Adds the opt in/out control to the general tab debug section.
	 *
	 * @since 6.1.0
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
	 * @since 6.1.0
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
	 * @since 6.1.0
	 *
	 * @param array<string,string> $slugs The existing array of slugs.
	 *
	 * @return array<string,string> $slugs The modified array of slugs.
	 */
	public function filter_tec_telemetry_slugs( $slugs ) {
		return $this->container->get( Telemetry::class )->filter_tec_telemetry_slugs( $slugs );
	}

	/**
	 * Conditionally injects the hook to trigger the Telemetry modal.
	 *
	 * @since 6.1.0
	 */
	public function action_inject_modal_link() {
		return $this->container->get( Telemetry::class )->inject_modal_link();
	}

	/**
	 * Update our option and the stellar option when the user opts in/out via the TEC admin.
	 *
	 * @since 6.1.0
	 *
	 * @param bool $value The optin value.
	 */
	public function action_save_opt_in_setting_field( $value ) {
		return $this->container->get( Telemetry::class )->save_opt_in_setting_field( $value );
	}
}
