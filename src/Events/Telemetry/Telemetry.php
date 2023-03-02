<?php
/**
 * Class that handles interfacing with tec-common Telemetry.
 *
 * @since   TBD
 *
 * @package TEC\Events\Telemetry
 */

namespace TEC\Events\Telemetry;

use TEC\Common\StellarWP\Telemetry\Config;
use TEC\Common\StellarWP\Telemetry\Opt_In\Status;
use TEC\Common\Telemetry\Telemetry as Common_Telemetry;

/**
 * Class Telemetry
 *
 * @since   TBD

 * @package TEC\Events\Telemetry
 */
class Telemetry {
	/**
	 * Filters the modal optin args to be specific to TEC
	 *
	 * @since TBD
	 *
	 * @param array<string|mixed> $original_optin_args The original args, provided by Common.
	 *
	 * @return array<string|mixed> The filtered args.
	 */
	public function filter_tec_common_telemetry_optin_args( $original_optin_args ): array {
		$user_name   = esc_html( wp_get_current_user()->display_name );

		$tec_optin_args = [
			'plugin_logo_alt'       => 'The Events Calendar Logo',
			'plugin_name'           => 'The Events Calendar',
			'heading'               => __( 'We hope you love The Events Calendar!', 'the-events-calendar' ),
			'intro'                 => __( "Hi, {$user_name}! This is an invitation to help our StellarWP community. If you opt-in, some data about your usage of The Events Calendar and future StellarWP Products will be shared with our teams (so they can work their butts off to improve). We will also share some helpful info on WordPress, and our products from time to time. And if you skip this, that’s okay! Our products still work just fine.", 'the-events-calendar' ),
		];

		return array_merge( $original_optin_args, $tec_optin_args );
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
		$status = Config::get_container()->get( Status::class );
		$opted = $status->get();

		switch( $opted ) {
			case $status::STATUS_ACTIVE :
				$label = esc_html_x( 'Opt out of Telemetry', 'Settings label for opting out of Telemetry.', 'the-events-calendar' );
			default :
				$label = esc_html_x( 'Opt in to Telemetry', 'the-events-calendar' );
		}


		$fields['opt-in-status'] = [
			'type'            => 'checkbox_bool',
			'label'           => $label,
			'tooltip'         => sprintf(
				/* Translators: Description of the Telemetry optin setting.
				%1$s: opening anchor tag for permissions link.
				%2$s: opening anchor tag for terms of service link.
				%3$s: opening anchor tag for privacy policy link.
				%4$s: closing anchor tags.
				*/
				_x( 'Enable this option to share usage data with The Events Calendar and StellarWP. %1$sWhat permissions are being granted?%4$s %2$sRead our Terms of Service%4$s %3$sRead our Privacy Policy%4$s', 'Description of optin setting.', 'the-events-calendar' ),
				'<a href=" ' . Common_Telemetry::get_permissions_url() . ' ">',
				'<a href=" ' . Common_Telemetry::get_terms_url() . ' ">',
				'<a href=" ' . Common_Telemetry::get_privacy_url() . ' ">',
				'</a>'
			),
			'default'         => false,
			'validation_type' => 'boolean',
		];

		return $fields;
	}
}
