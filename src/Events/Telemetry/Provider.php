<?php
/**
 * Service Provider for interfacing with TEC\Common\Telemetry.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Telemetry;

use TEC\Common\lucatume\DI52\ServiceProvider as ServiceProvider;
use Tribe\Events\Admin\Settings as Plugin_Settings;

 /**
  * Class Provider
  *
  * @since   TBD

  * @package TEC\Events\Telemetry
  */
class Provider extends ServiceProvider {
	/**
	 * Slug for the section.
	 *
	 * @since TBD
	 *
	 * @var string $slug
	 */
	protected static string $slug = 'the-events-calendar';


	public function register() {
		// wp-admin/admin.php?page=tec-event-settings
		if ( ! tribe( Plugin_Settings::class )->is_tec_events_settings() ) {
			return;
		}
		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {
		add_action( 'tribe_common_loaded', [ $this, 'hook_into_common_telemetry' ], 10 , );
	}

	public function add_filters() {
		add_filter( 'tec_common_telemetry_optin_args', [ $this, 'filter_tec_common_telemetry_optin_args' ] );
		add_filter( 'tribe_general_settings_debugging_section',[ $this, 'filter_tribe_general_settings_debugging_section' ] );
		add_filter( 'tribe_field_value', [ $this, 'filter_tribe_field_opt_in_status' ], 10, 2 );
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

	public function hook_into_common_telemetry() {
		$this->container->get( Telemetry::class )->hook_into_common_telemetry();
	}
}
