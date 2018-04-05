<?php
class Tribe__Events__Admin__Front_Page_View {

	private $HOME_VIRTUAL_ID = -1;

	public function hook() {
		add_action( 'sanitize_option_page_on_front', array( $this, 'save_page_on_front' ), 10, 3 );

		tribe_asset(
			Tribe__Events__Main::instance(),
			'tribe-events-front-page-setting',
			'front-page-setting.js',
			array( 'jquery' ),
			'admin_enqueue_scripts',
			array(
				'localize' => array(
					'name' => 'tribe_events_front_page_setting',
					'data' => array(
						'enabled'           => (bool) tribe_get_option( 'front_page_event_archive', false ),
						'check'             => wp_create_nonce( 'events_front_page_setting' ),
						'events_page_label' => sprintf(
							esc_html_x( 'Main %s Page', 'Static front page setting', 'the-events-calendar' ),
							tribe_get_event_label_plural()
						),
					),
				),
			)
		);
	}

	/**
	 *
	 *
	 * @since TBD
	 *
	 * @param $value
	 * @param $option
	 * @param $original_value
	 *
	 * @return mixed
	 */
	public function save_page_on_front( $value, $option, $original_value ) {

		$is_front_page_event_archive = $this->is_virtual_page_id( $original_value );

		tribe_update_option( 'front_page_event_archive', $is_front_page_event_archive );

		return $is_front_page_event_archive ? $original_value : $value;
	}

	public function get_virtual_id() {
		return $this->HOME_VIRTUAL_ID;
	}

	public function is_virtual_page_id( $compare ) {
		return $this->get_virtual_id() === (int) $compare;
	}
}