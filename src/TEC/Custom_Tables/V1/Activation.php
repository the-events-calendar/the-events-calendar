<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */

namespace TEC\Custom_Tables\V1;

use TEC\Custom_Tables\V1\Events\Provisional\Provider as Provisional_Post_Provider;
use TEC\Custom_Tables\V1\Tables\Provider as Tables;

/**
 * Class Activation
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the plugin did activate
	 * or not.
	 *
	 * @since TBD
	 */
	const ACTIVATION_TRANSIENT = 'tec_custom_tables_v1_initialized';

	/**
	 * Handles the activation of the feature functions.
	 *
	 * @since TBD
	 */
	public static function activate() {
		// @todo remove this block when merged into TEC.
		if ( ! ( class_exists( 'Tribe__Events__Main' ) ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( TEC_CUSTOM_TABLES_V1_FILE, true );
			$message = __(
				'To use Recurring Events 2.0 Alpha, you must activate the latest versions of The Events ' .
				'Calendar and The Events Calendar Pro.',
				'ical-tec'
			);

			if ( ! defined( 'WP_CLI' ) ) {
				wp_die( $message );
			} else {
				\WP_CLI::error( $message );
			}

			return;
		}

		// Delete the transient to make sure the activation code will run again.
		delete_transient( self::ACTIVATION_TRANSIENT );

		self::init();
	}

	/**
	 * Initializes the custom tables required by the feature to work.
	 *
	 * This method will run once a day (using transients) and is idem-potent
	 * in the context of the same day.
	 *
	 * @since TBD
	 */
	public static function init() {
		$initialized = get_transient( self::ACTIVATION_TRANSIENT );

		if ( $initialized ) {
			return;
		}

		set_transient( self::ACTIVATION_TRANSIENT, 1, DAY_IN_SECONDS );

		$services = tribe();

		$services->register( Tables::class );
		$services->register( Provisional_Post_Provider::class );

		$services->make( Tables::class )->update_tables();
		$services->make( Provisional_Post_Provider::class )->on_activation();
	}

	/**
	 * Handles the feature deactivation.
	 *
	 * @since TBD
	 */
	public static function deactivate() {
		$services = tribe();

		$services->register( Tables::class );

		$services->make( Tables::class )->clean();
		$services->make( Provisional_Post_Provider::class )->on_deactivation();
	}
}
