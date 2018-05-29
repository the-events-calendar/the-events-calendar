<?php
/**
 * Shows an admin notice for Timezones
 * (When using UTC and on TEC Pages or WordPress > General Settings)
 */
class Tribe__Events__Admin__Notice__Timezones {

	public function hook() {

		tribe_notice(
			'tribe-events-utc-timezone',
			array( $this, 'notice' ),
			array(
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => 'p',
			),
			array( $this, 'should_display' )
		);

	}

	/**
	 * Checks if we are in an TEC page or over
	 * the WordPress > Settings > General
	 *
	 * @since  4.6.17
	 *
	 * @return boolean
	 */
	public function should_display() {
		global $pagenow;

		// Bail if the site isn't using UTC
		if ( ! $this->is_utc_timezone() ) {
			return false;
		}

		// It should display if we're on a TEC page or
		// over Settings > General
		return tribe( 'admin.helpers' )->is_screen() || 'options-general.php' === $pagenow;
	}

	/**
	 * Checks if the site is using UTC Timezone Options
	 *
	 * @since  4.6.17
	 *
	 * @return boolean
	 */
	public function is_utc_timezone() {

		// If the site is using UTC or UTC manual offset
		return strpos( Tribe__Timezones::wp_timezone_string(), 'UTC' ) !== false;
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since  4.6.17
	 *
	 * @return string
	 */
	public function notice() {
		// Bail if the user is not admin or can manage plugins
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		$url = 'https://theeventscalendar.com/knowledgebase/time-zones/';
		$link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Read more', 'the-events-calendar' )
		);
		$text = __( 'When using The Events Calendar, we recommend that you choose a city in your timezone and avoid using a UTC timezone offset. Choosing a UTC timezone may cause problems when importing events or with Day Light Savings time. %1$s', 'the-events-calendar' );

		return sprintf( $text, $link );

	}


}
