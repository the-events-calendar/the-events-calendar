<?php
/**
 * @internal This class may be removed or changed without notice
 */
class Tribe__Events__Admin__Notice__Marketing {
	/**
	 * Register marketing notices.
	 *
	 * @since 4.6.17
	 */
	public function hook() {
		tribe_notice(
			'tribe-events-upcoming-survey',
			array( $this, 'notice' ),
			array(
				'dismiss' => 1,
				'type'    => 'info',
				'wrap'    => 'p',
			),
			array( $this, 'should_display' )
		);

		tribe_notice(
			'tribe-events-editor',
			array( $this, 'notice_gutenberg' ),
			array(
				'dismiss' => 1,
				'type'    => 'warning',
				'wrap'    => 'p',
			),
			array( $this, 'should_display_gutenberg' )
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
	 * Check if we should display the Gutenberg notice
	 * @since 4.6.25
	 *
	 * @return bool
	 */
	public function should_display_gutenberg() {
		$today         = date_create()->format( 'Y-m-d' );
		$start         = '2018-10-23';
		$end           = '2018-10-30';

		return $today >= $start && $today <= $end;
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
			esc_url( 'https://m.tri.be/1a3l' ),
			esc_html_x( 'take the survey now', '2018 user survey', 'the-events-calendar' )
		);

		return sprintf(
			_x( '<strong>The Events Calendar Annual Survey:</strong> share your feedback with our team—%1$s!', '2018 user survey', 'the-events-calendar' ),
			$link
		);
	}

	/**
	 * HTML for the Gutenberg Notice
	 *
	 * @since 4.6.25
	 *
	 * @return string
	 */
	public function notice_gutenberg() {
		$notice  = __( '<strong>The Events Calendar & Gutenberg</strong>', 'the-events-calendar' );

		$notice .= sprintf(
			'<p>%1$s</p>',
			esc_html__( 'WordPress 5.0 is coming soon, and with it, the arrival of the new block editor interface.', 'the-events-calendar' )
		);

		$notice .= sprintf(
			'<p>%1$s</p>',
			esc_html__( 'Get up to speed with our comprehensive Guide to Gutenberg ebook, then see how events and tickets will behave in the block editor by installing our free Events Gutenberg extension.', 'the-events-calendar' )
		);

		$notice .= sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( 'http://m.tri.be/1a82' ),
			esc_html__( 'Download the eBook', 'the-events-calendar' )
		);

		$notice .= ' &mdash; ';

		$notice .= sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( 'http://m.tri.be/1a83' ),
			esc_html__( 'Try Events Gutenberg', 'the-events-calendar' )
		);

		return $notice;
	}

}
