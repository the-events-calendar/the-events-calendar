<?php
/**
 * Tribe__Events__Importer__Options
 *
 */
class Tribe__Events__Importer__Options {
	public static $options = array(
		'imported_post_status'
	);

	public static function process_general_form_submission() {
		if ( ! empty( $_POST[ 'tribe-import-general-settings' ] ) && wp_verify_nonce( $_POST[ 'tribe-import-general-settings' ], 'tribe-import-general-settings' ) ) {

			$options = apply_filters( 'tribe-import-available-options', self::$options );
			$core = Tribe__Events__Main::instance();

			foreach ( $options as $_option ) {
				$value = isset( $_POST[ $_option ] ) ? $_POST[ $_option ] : null;
				$core->setOption( $_option, $value );
			}

			add_action( 'tribe-import-under-heading', array( __CLASS__, 'settings_saved_message' ) );
		}
	}

	public static function settings_saved_message() {
		?>
		<div id="message" class="updated below-h2">
			<p>
				<strong><?php esc_html_e( 'Settings saved.' ); ?></strong>
			</p>
		</div>
		<?php
	}


	/**
	 * Get value for a specific option
	 *
	 * @param string $optionName name of option
	 * @param string $default    default value
	 *
	 * @return mixed results of option query
	 */
	public static function getOption( $optionName, $default = '' ) {
		if ( ! $optionName ) {
			return null;
		}

		$value = Tribe__Events__Main::getOption( $optionName, $default );

		return apply_filters( 'tribe-import-setting-' . $optionName, $value, $default );
	}

	public static function get_default_post_status( $type = 'csv' ) {
		$options = self::getOption( 'imported_post_status', array( $type => 'publish' ) );

		// Legacy for Facebook Status
		if ( 'facebook' === $type && empty( $options['facebook'] ) ){
			$options['facebook'] = Tribe__Events__Main::getOption( 'fb_default_status', 'publish' );
		}

		// A way to handle the legacy `imported_post_status`
		if ( is_string( $options ) ) {
			$options = array( $type => $options );
		}

		if ( ! isset( $options[ $type ] ) ) {
			$options[ $type ] = apply_filters( 'tribe-import-default-post-status-non-saved', 'publish', $type );
		}

		/**
		 * Allows users to filter
		 */
		return apply_filters( 'tribe-import-default-post-status', $options[ $type ], $type );
	}

	public static function get_possible_stati() {
		$stati = array(
			'publish' => __( 'Published', 'the-events-calendar' ),
			'pending' => __( 'Pending', 'the-events-calendar' ),
			'draft'   => __( 'Draft', 'the-events-calendar' ),
		);

		return apply_filters( 'tribe-import-possible-stati', $stati );
	}
}
