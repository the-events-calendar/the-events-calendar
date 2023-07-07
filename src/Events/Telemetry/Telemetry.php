<?php
/**
 * Class that handles interfacing with TEC\Common\Telemetry.
 *
 * @since   6.1.0
 *
 * @package TEC\Events\Telemetry
 */

namespace TEC\Events\Telemetry;

use TEC\Common\StellarWP\Telemetry\Config;
use TEC\Common\StellarWP\Telemetry\Opt_In\Status;
use TEC\Common\StellarWP\Telemetry\Opt_In\Opt_In_Template;

use TEC\Common\Telemetry\Telemetry as Common_Telemetry;
use Tribe__Events__Main as TEC;

/**
 * Class Telemetry
 *
 * @since   6.1.0
 * @package TEC\Events\Telemetry
 */
class Telemetry {

	/**
	 * The Telemetry plugin slug for The Events Calendar.
	 *
	 * @since 6.1.1
	 *
	 * @var string
	 */
	protected static $plugin_slug = 'the-events-calendar';

	/**
	 * The "plugin path" for The Events Calendar main file.
	 *
	 * @since 6.1.1
	 *
	 * @var string
	 */
	protected static $plugin_path = 'the-events-calendar.php';

	/**
	 * Filters the modal optin args to be specific to TEC
	 *
	 * @since 6.1.1
	 *
	 * @param array<string|mixed> $original_optin_args The original args, provided by Common.
	 *
	 * @return array<string|mixed> The filtered args.
	 */
	public function filter_tec_common_telemetry_optin_args( $original_optin_args ): array {
		if ( ! static::is_tec_admin_page() ) {
			return $original_optin_args;
		}

		$intro_message = sprintf(
			/* Translators: %1$s - the user name. */
			__( 'Hi, %1$s! This is an invitation to help our StellarWP community.', 'the-events-calendar' ),
			wp_get_current_user()->display_name // escaped after string is assembled, below.
		);

		$intro_message .= ' ' . __( 'If you opt-in, some data about your usage of The Events Calendar and future StellarWP Products will be shared with our teams (so they can work their butts off to improve).' , 'the-events-calendar');
		$intro_message .= ' ' . __( 'We will also share some helpful info on WordPress, and our products from time to time.' , 'the-events-calendar');
		$intro_message .= ' ' . __( 'And if you skip this, that’s okay! Our products still work just fine.', 'the-events-calendar' );

		$tec_optin_args = [
			'plugin_logo_alt' => 'The Events Calendar Logo',
			'plugin_name'     => 'The Events Calendar',
			'heading'         => __( 'We hope you love The Events Calendar!', 'the-events-calendar' ),
			'intro'           => esc_html( $intro_message )
		];

		return array_merge( $original_optin_args, $tec_optin_args );
	}

	/**
	 * Adds the opt in/out control to the general tab debug section.
	 *
	 * @since 6.1.1
	 *
	 * @param array<string|mixed> $fields The fields for the general tab Debugging section.
	 *
	 * @return array<string|mixed> The fields, with the optin control appended.
	 */
	public function filter_tribe_general_settings_debugging_section( $fields ): array {
		$telemetry = tribe( Common_Telemetry::class );
		$telemetry->init();
		$status = $telemetry::get_status_object();
		$opted = $status->get( self::$plugin_slug );

		switch( $opted ) {
			case Status::STATUS_ACTIVE :
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
				_x(
					'Enable this option to share usage data with The Events Calendar and StellarWP. %1$sWhat permissions are being granted?%4$s %2$sRead our terms of service%4$s. %3$sRead our privacy policy%4$s.',
					'Description of optin setting.',
					'the-events-calendar'
				),
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

	/**
	 * Reconcile our option and the Telemetry option to a single value.
	 *
	 * @since 6.1.1
	 */
	public function get_reconciled_telemetry_opt_in(): bool {
		$status         = Config::get_container()->get( Status::class );
		$stellar_option = $status->get_option();
		$optin          = $stellar_option[ static::$plugin_slug ]['optin'] ?? false;
		$tec_optin      = tribe_get_option( 'opt_in_status', null );

		if ( is_null( $tec_optin ) ) {
			// If the option is null, we haven't saved it yet, so use the stellar option.
			return $optin;
		}

		return $tec_optin;
	}

	/**
	 * Ensures the admin control reflects the actual opt-in status.
	 * We save this value in tribe_options but since that could get out of sync,
	 * we always display the status from TEC\Common\StellarWP\Telemetry\Opt_In\Status directly.
	 *
	 * @since 6.1.0
	 *
	 * @param mixed  $value  The value of the attribute.
	 * @param string $field  The field object id.
	 *
	 * @return mixed $value
	 */
	public function filter_tribe_field_opt_in_status( $value, $id ) {
		if ( 'opt-in-status' !== $id ) {
			return $value;
		}

		// We don't care what the value stored in tribe_options is - give us Telemetry's Opt_In\Status value.
		$status = Config::get_container()->get( Status::class );
		$value  = $status->get() === $status::STATUS_ACTIVE;

		return $value;
	}

	/**
	 * Adds The Events Calendar to the list of plugins
	 * to be opted in/out alongside tribe-common.
	 *
	 * @since 6.1.0
	 *
	 * @param array<string,string> $slugs The default array of slugs in the format  [ 'plugin_slug' => 'plugin_path' ]
	 *
	 * @see \TEC\Common\Telemetry\Telemetry::get_tec_telemetry_slugs()
	 *
	 * @return array<string,string> $slugs The same array with The Events Calendar added to it.
	 */
	public function filter_tec_telemetry_slugs( $slugs ) {
		$dir = TEC::instance()->plugin_dir;
		$slugs[ static::$plugin_slug ] =  $dir . static::$plugin_path;
		return array_unique( $slugs, SORT_STRING );
	}

	/**
	 * Determines if we are on a TEC admin page except the post edit page.
	 *
	 * @since 6.1.0
	 *
	 * @return boolean
	 */
	public static function is_tec_admin_page(): bool {
		$current_screen = get_current_screen();
		$helper = \Tribe__Admin__Helpers::instance();

		// Are we on a tec post-type admin screen?
		if ( ! $helper->is_post_type_screen( TEC::POSTTYPE ) ) {
			return false;
		}

		// Are we on a post edit screen?
		if ( $current_screen instanceof \WP_Screen && tribe_get_request_var( 'action' ) === 'edit' ) {
			return false;
		}

		// Are we on a new post screen?
		if ( $current_screen instanceof \WP_Screen && $current_screen->action === 'add' ) {
			return false;
		}

		return true;
	}

	/**
	 * Outputs the hook that renders the Telemetry action on all TEC admin pages.
	 *
	 * @since 6.1.0
	 */
	public function inject_modal_link() {
		if ( ! static::is_tec_admin_page() ) {
			return;
		}

		// Don't double-dip on the action.
		if ( did_action( 'tec_telemetry_modal' ) ) {
			return;
		}

		// 'the-events-calendar'
		$telemetry_slug = \TEC\Common\Telemetry\Telemetry::get_plugin_slug();

		$show = get_option( Config::get_container()->get( Opt_In_Template::class )->get_option_name( $telemetry_slug ) );

		if ( ! $show ) {
			return;
		}

		/**
		 * Fires to trigger the modal content on admin pages.
		 *
		 *
		 * @since 6.1.0
		 */
		do_action( 'tec_telemetry_modal', $telemetry_slug );
	}

	/**
	 * Update our option and the stellar option when the user opts in/out via the TEC admin.
	 *
	 *
	 * @since 6.1.0
	 *
	 * @param bool $value The option value
	 */
	public function save_opt_in_setting_field( $value ): void {

		// Get the value submitted on the settings page as a boolean.
		$value = tribe_is_truthy( tribe_get_request_var( 'opt-in-status' ) );

		// Gotta catch them all..
		tribe( Common_Telemetry::class )->register_tec_telemetry_plugins( $value );

		if ( $value ) {
			// If opting in, blow away the expiration datetime so we send updates on next shutdown.
			delete_option( 'stellarwp_telemetry_last_send' );
		}
	}
}
