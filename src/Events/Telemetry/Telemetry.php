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
use TEC\Common\StellarWP\Telemetry\Opt_In\Opt_In_Subscriber;
use TEC\Common\StellarWP\Telemetry\Opt_In\Status;

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

		$fields['opt-in-status'] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html_x( 'Data share consent', 'Title for Data share section.', 'the-events-calendar' ),
			'tooltip'         => sprintf(
			// Translators: 1: opening anchor tag, 2: opening anchor tag, 3: opening anchor tag, 4: closing anchor tags.
				_x(
					'Enable this option to share usage data with The Events Calendar and StellarWP.
        This activates access to TEC AI chatbot and in-app priority support for premium users.
        %1$sWhat permissions are being granted?%4$s
        %2$sRead our terms of service%4$s.
        %3$sRead our privacy policy%4$s.',
					'Description of opt-in setting.',
					'the-events-calendar'
				),
				'<br/><a href="' . Common_Telemetry::get_permissions_url() . '">', // URL is escaped in method.
				'<br/><a href="' . Common_Telemetry::get_terms_url() . '">', // URL is escaped in method.
				'<br/><a href="' . Common_Telemetry::get_privacy_url() . '">', // URL is escaped in method.
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
	 * @param string $id  The field object id.
	 *
	 * @return mixed $value
	 */
	public function filter_tribe_field_opt_in_status( $value, $id ) {
		if ( 'opt-in-status' !== $id ) {
			return $value;
		}

		// Trigger this before we try use the value.
		tribe( Common_Telemetry::class )->normalize_optin_status();

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

		// If we are not on a tec post-type admin screen, bail.
		if ( ! $helper->is_post_type_screen( TEC::POSTTYPE ) ) {
			return false;
		}

		// If we are on a post edit screen, bail.
		if ( $current_screen instanceof \WP_Screen && tribe_get_request_var( 'action' ) === 'edit' ) {
			return false;
		}

		// If we are on a new post screen, bail.
		if ( $current_screen instanceof \WP_Screen && $current_screen->action === 'add' ) {
			return false;
		}

		/**
		 * Filter to determine if we are on a TEC admin page.
		 * Allows other classes to hook in and modify the return value.
		 *
		 * @since 6.13.0
		 *
		 * @param bool $is_tec_admin_page Whether we are on a TEC admin page.
		 */
		return (bool) apply_filters( 'tec_telemetry_is_tec_admin_page', true );
	}

	/**
	 * Outputs the hook that renders the Telemetry action on all TEC admin pages.
	 *
	 * @since 6.1.0
	 * @since 6.8.2 We are bailing on events list page.
	 */
	public function inject_modal_link() {
		if ( ! static::is_tec_admin_page() ) {
			return;
		}

		$current_screen = get_current_screen();

		// The save action would perform a POST request against edit.php. So WP would assume we are editing a post throwing an error!
		if ( isset( $current_screen->id ) && $current_screen->id === 'edit-tribe_events' ) {
			return;
		}

		// Don't double-dip on the action.
		if ( did_action( 'tec_telemetry_modal' ) ) {
			return;
		}

		// 'the-events-calendar'
		$telemetry_slug = substr( basename( TRIBE_EVENTS_FILE ), 0, -4 );

		$show = tribe( Common_Telemetry::class )->calculate_modal_status();

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
	 * @since 6.1.0
	 *
	 * @param bool $saved_value The option value
	 */
	public function save_opt_in_setting_field( $saved_value ): void {
		$saved_value = tribe_is_truthy( $saved_value );

		// Get the currently saved value.
		$option = tribe_get_option( 'opt-in-status', false );

		// Gotta catch them all.
		tribe( Common_Telemetry::class )->register_tec_telemetry_plugins( $saved_value );

		if ( $saved_value && $option !== $saved_value ) {
			// If changing the value, blow away the expiration datetime so we send updates on next shutdown.
			delete_option( 'stellarwp_telemetry_last_send' );

			$telemetry_data = get_option( 'stellarwp_telemetry' );

			if ( empty( $telemetry_data['token'] ) ) {
				// Force and Opt-in to be done, as we don't have a token yet.
				$opt_in_subscriber = Config::get_container()->get( Opt_In_Subscriber::class );
				$opt_in_subscriber->opt_in( static::$plugin_slug );
			}
		}
	}
}
