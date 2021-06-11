<?php

/**
 * Class Tribe__Events__Privacy
 */
class Tribe__Events__Privacy {

	/**
	 * Class initialization
	 *
	 * @since 4.6.20
	 */
	public function hook() {
		add_action( 'admin_init', [ $this, 'privacy_policy_content' ], 20 );
	}

	/**
	 * Add the suggested privacy policy text to the policy postbox.
	 *
	 * @since 4.6.20
	 */
	public function privacy_policy_content() {

		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return false;
		}

		$content = $this->default_privacy_policy_content( true );
		wp_add_privacy_policy_content( __( 'The Events Calendar', 'the-events-calendar' ), $content );
	}

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @param bool $descr Whether to include the descriptions under the section headings. Default false.
	 *
	 * @since 4.6.20
	 *
	 * @return string The default policy content.
	 */
	public function default_privacy_policy_content( $descr = false ) {

		ob_start();
		include_once Tribe__Events__Main::instance()->pluginPath . 'src/admin-views/privacy.php';
		$content = ob_get_clean();

		/**
		 * Filters the default content suggested for inclusion in a privacy policy.
		 *
		 * @since 4.6.20
		 *
		 * @param $content string The default policy content.
		 */
		return apply_filters( 'tribe_events_default_privacy_policy_content', $content );

	}
}
