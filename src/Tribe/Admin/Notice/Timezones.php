<?php
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;
use Tribe__Events__Timezones as Event_Timezones;

/**
 * Shows an admin notice for Timezones
 * (When using UTC and on TEC Pages or WordPress > General Settings)
 */
class Tribe__Events__Admin__Notice__Timezones {

	/**
	 * Notice Slug on the user options
	 *
	 * @since  4.8.2
	 * @var string
	 */
	private $slug = 'events-utc-timezone';

	public function hook() {
		$date = $this->get_current_reset_date();
		$slug = $this->slug;
		/**
		 * Allows users to completely deactivate the resetting of the Day Light savings notice
		 *
		 * @since  4.8.2
		 *
		 * @param  bool
		 */
		$should_reset = apply_filters( 'tribe_events_admin_notice_daylight_savings_reset_notice', true );

		// If we have a date append to the Slug
		if ( $should_reset && $date ) {
			$slug .= '-' . $date;
		}

		tribe_notice(
			$slug,
			[ $this, 'notice' ],
			[
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => 'p',
			],
			[ $this, 'should_display' ]
		);

	}

	/**
	 * Fetches the date in which the Notice had it's reset
	 *
	 * @since  4.8.2
	 *
	 * @return string|null
	 */
	public function get_current_reset_date() {
		$dates = $this->get_reset_dates();
		$today = date( Dates::DBDATEFORMAT );

		foreach ( $dates as $key => $date ) {
			if ( $date <= $today ) {
				return $date;
			}
		}

		return null;
	}

	/**
	 * Which dates this Notice gets reset
	 *
	 * @since  4.8.2
	 *
	 * @return array
	 */
	public function get_reset_dates() {
		$dates[] = date( Dates::DBDATEFORMAT, strtotime( 'last sunday of february' ) );
		$dates[] = date( Dates::DBDATEFORMAT, strtotime( 'third sunday of october' ) );
		return $dates;
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

		// Display when dealing with UTC but the negative still needs to test the global
		if (
			'post.php' === $pagenow
			&& $this->is_utc_timezone( (int) tribe_get_request_var( 'post' ) )
		) {
			return true;
		}

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
	public function is_utc_timezone( $event = 0 ) {
		$timezone = Timezones::wp_timezone_string();
		if ( $event ) {
			$timezone = Event_Timezones::get_event_timezone_string( $event );
		}

		// If the site is using UTC or UTC manual offset
		return strpos( $timezone, 'UTC' ) !== false;
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

		$text = [];
		$current_utc = Timezones::wp_timezone_string();

		$url = 'http://evnt.is/1ad3';
		$link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Read more', 'the-events-calendar' )
		);

		$text[] = __( 'When using The Events Calendar, we recommend that you use a geographic timezone such as "America/Los_Angeles" and avoid using a UTC timezone offset such as “%2$s”.', 'the-events-calendar' );
		$text[] = __( 'Choosing a UTC timezone for your site or individual events may cause problems when importing events or with Daylight Saving Time. %1$s', 'the-events-calendar' );

		return sprintf( implode( '<br />', $text ), $link, $current_utc );

	}
}
