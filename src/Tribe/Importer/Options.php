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
			$options = self::$options;

			if ( has_filter( 'tribe-import-available-options' ) ) {
				/**
				 * Remove this Filter on 4.3
				 * @deprecated
				 */
				_doing_it_wrong(
					'tribe-import-available-options',
					sprintf(
						esc_html__( 'This Filter has been deprecated, to comply with WordPress Standards we are now using Underscores (_) instead of Dashes (-). From: "%s" To: "%s"', 'the-events-calendar' ),
						'tribe-import-available-options',
						'tribe_import_available_options'
					),
					'4.0'
				);
				$options = apply_filters( 'tribe-import-available-options', $options );
			}
			$options = apply_filters( 'tribe_import_available_options', $options );

			foreach ( $options as $_option ) {
				$value = isset( $_POST[ $_option ] ) ? $_POST[ $_option ] : null;
				Tribe__Settings_Manager::set_option( $_option, $value );
			}

			/**
			 * Fires once import options have been saved/updated.
			 *
			 * @var array $options
			 */
			do_action( 'tribe_import_options_updated', $options );

			add_action( 'tribe_import_under_heading', array( __CLASS__, 'settings_saved_message' ) );
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

		$value = Tribe__Settings_Manager::get_option( $optionName, $default );

		if ( has_filter( 'tribe-import-setting-' . $optionName ) ) {
			/**
			 * Remove this Filter on 4.3
			 * @deprecated
			 */
			_doing_it_wrong(
				'tribe-import-setting-' . $optionName,
				sprintf(
					esc_html__( 'This Filter has been deprecated, to comply with WordPress Standards we are now using Underscores (_) instead of Dashes (-). From: "%s" To: "%s"', 'the-events-calendar' ),
					'tribe-import-setting-' . $optionName,
					'tribe_import_setting_' . $optionName
				),
				'4.0'
			);
			$value = apply_filters( 'tribe-import-setting-' . $optionName, $value, $default );
		}

		return apply_filters( 'tribe_import_setting_' . $optionName, $value, $default );
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
			$options[ $type ] = apply_filters( 'tribe_import_default_post_status_non_saved', 'publish', $type );
		}

		/**
		 * Allows users to filter
		 */
		return apply_filters( 'tribe_import_default_post_status', $options[ $type ], $type );
	}

	public static function get_possible_stati() {
		$stati = array(
			'publish' => __( 'Published', 'the-events-calendar' ),
			'pending' => __( 'Pending', 'the-events-calendar' ),
			'draft'   => __( 'Draft', 'the-events-calendar' ),
		);

		return apply_filters( 'tribe_import_possible_stati', $stati );
	}
}
