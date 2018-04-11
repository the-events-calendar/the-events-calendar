<?php
_deprecated_file( __FILE__, 'TBD', 'Tribe__Events__Front_Page_View' );

class Tribe__Events__Admin__Front_Page_View {
	public function hook() {
		add_action( 'load-options.php', array( $this, 'save_setting' ) );
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
	public function save_setting() {
		if ( ! current_user_can( 'customize' ) || empty( $_POST ) ) {
			return;
		}
		$fields = array_merge(
			array(
				'show_on_front'        => '',
				'page_on_front'        => '',
				'set_main_events_page' => '',
			),
			$_POST
		);
		if ( ! wp_verify_nonce( $fields['set_main_events_page'], 'events_front_page_setting' ) ) {
			return;
		}
		if ( 'page' === $fields['show_on_front'] && 'main_events_page' === $fields['page_on_front'] ) {
			tribe_update_option( 'front_page_event_archive', true );
		} else {
			tribe_update_option( 'front_page_event_archive', false );
		}
	}
}
