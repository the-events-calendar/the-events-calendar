<?php

namespace Tribe\Events\Editor;

use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;

/**
 * Events block editor hooks.
 *
 * @since 5.12.0
 */
class Hooks extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.12.0
	 */
	public function register() {
		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( static::class, $this );

		$this->add_actions();
	}

	/**
	 * Adds the actions required by each Views v2 component.
	 *
	 * @since 5.12.0
	 */
	protected function add_actions() {
		add_action( 'current_screen', [ $this, 'add_widget_resources' ] );
		add_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'calculate_duration' ], 20 );
		add_action( 'plugins_loaded', [ $this, 'update_old_durations' ] );
	}

	/**
	 * Calculate the event duration based on the saved start and end dates.
	 * This occurs during post save, and checks the saved value against the calculated value on updates.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The event we are setting a duration for.
	 */
	public function calculate_duration( $post_id ) {
		// Sanity check.
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$saved_duration = get_post_meta( $post_id, '_EventDuration', true  );
		$start_date_utc = get_post_meta( $post_id, '_EventStartDateUTC', true );
		$end_date_utc   = get_post_meta( $post_id, '_EventEndDateUTC', true  );

		// Don't try to calculate if a date is missing.
		if ( empty( $start_date_utc ) || empty( $end_date_utc ) ) {
			return;
		}

		$utc_timezone          = new \DateTimezone( 'UTC' );
		$start_date_utc_object = Dates::immutable( $start_date_utc, $utc_timezone );
		$end_date_utc_object   = Dates::immutable( $end_date_utc, $utc_timezone );
		$calculated_duration   = $end_date_utc_object->getTimestamp() - $start_date_utc_object->getTimestamp();

		if ( empty( $saved_duration ) || (int) $saved_duration !== (int) $calculated_duration ) {
			update_post_meta( $post_id, '_EventDuration', $calculated_duration );
		}
	}

	/**
	 * A run-once function to update any existing events that are missing
	 * the `_EventDuration meta.
	 *
	 * @since TBD
	 */
	public function update_old_durations() {
		if ( ! empty( tribe_get_option( 'fix_duration' ) ) ) {
			return;
		}

		global $wpdb;
			$query = $wpdb->prepare( "insert into $wpdb->postmeta (post_id, meta_key, meta_value)
						select p.ID, %s, TIME_TO_SEC(TIMEDIFF(end.meta_value, start.meta_value))
						from $wpdb->posts p
						join $wpdb->postmeta start on start.post_id = p.ID
						and start.meta_key = %s join $wpdb->postmeta end
						on end.post_id = p.ID and end.meta_key = %s
						left join $wpdb->postmeta as duration
						on p.ID = duration.post_id and  duration.meta_key = %s
						where p.post_type = %s and duration.meta_value is null",
						'_EventDuration',
						'_EventStartDateUTC',
						'_EventEndDateUTC',
						'_EventDuration',
						TEC::POSTTYPE
					);

			$fixed = $wpdb->query( $query );

			if( $fixed ) {
				tribe_update_option( 'fix_duration', 1 );
			}
	}

	/**
	 * Adds the editor styles required by the block editor.
	 *
	 * We trigger this action so that we only need to do the is_block_editor() check once.
	 *
	 * @since 5.12.0
	 */
	public function add_widget_resources() {
		if ( ! get_current_screen()->is_block_editor() ) {
			return;
		}

		add_action( 'admin_print_styles', [ $this, 'admin_print_widget_styles' ] );
		add_action( 'admin_print_scripts', [ $this, 'admin_print_widget_scripts' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'admin_print_footer_widget_scripts' ] );
		add_action( 'admin_footer', [ $this, 'admin_footer_widgets' ] );
	}

	/**
	 * Triggers the print styles action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_widget_styles() {
		if ( did_action( 'admin_print_styles-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_print_styles-widgets.php' );
	}

	/**
	 * Triggers the print scripts action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_widget_scripts() {
		if ( ! did_action( 'load-widgets.php' ) ) {
			do_action( 'load-widgets.php' );
		}

		if ( ! did_action( 'widgets.php' ) ) {
			do_action( 'widgets.php' );
		}

		if ( ! did_action( 'sidebar_admin_setup' ) ) {
			do_action( 'sidebar_admin_setup' );
		}

		if ( ! did_action( 'admin_print_scripts-widgets.php' ) ) {
			do_action( 'admin_print_scripts-widgets.php' );
		}
	}

	/**
	 * Triggers the print footer scripts action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_footer_widget_scripts() {
		if ( did_action( 'admin_print_footer_scripts-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_print_footer_scripts-widgets.php' );
	}

	/**
	 * Triggers the admin footer widgets action in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_footer_widgets() {
		if ( did_action( 'admin_footer-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_footer-widgets.php' );
	}
}
