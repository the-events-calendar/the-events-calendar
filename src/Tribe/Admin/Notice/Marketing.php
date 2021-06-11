<?php
/**
 * @internal This class may be removed or changed without notice
 */
class Tribe__Events__Admin__Notice__Marketing {
	/**
	 * Register marketing notices.
	 *
	 * @since 4.6.17
	 * @since 5.1.5 - add Virtual Events Notice.
	 */
	public function hook() {
		tribe_notice(
			'tribe-events-upcoming-survey',
			[ $this, 'notice' ],
			[
				'dismiss' => 1,
				'type'    => 'info',
				'wrap'    => 'p',
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * @since 4.6.17
	 *
	 * @return bool
	 */
	public function should_display() {
		/** @var Tribe__Admin__Helpers $admin_helpers */
		$admin_helpers = tribe( 'admin.helpers' );

		return ( $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen() )
			&& date_create()->format( 'Y-m-d' ) < '2018-06-08';
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since 4.6.17
	 *
	 * @return string
	 */
	public function notice() {
		$link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( 'https://evnt.is/1a3l' ),
			esc_html_x( 'take the survey now', '2018 user survey', 'the-events-calendar' )
		);

		return sprintf(
			_x( '<strong>The Events Calendar Annual Survey:</strong> share your feedback with our team—%1$s!', '2018 user survey', 'the-events-calendar' ),
			$link
		);
	}
}
