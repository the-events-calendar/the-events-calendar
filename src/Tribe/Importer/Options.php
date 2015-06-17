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
				$core->setOption( $_option, $_POST[ $_option ] );
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
}
