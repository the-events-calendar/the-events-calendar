<?php


/**
 * Class Tribe__Events__Integrations__WPML__Defaults
 *
 * Handles sensible defaults for to The Events Calendar in WPML.
 */
class Tribe__Events__Integrations__WPML__Defaults {

	/**
	 * @var Tribe__Events__Integrations__WPML__Defaults
	 */
	protected static $instance;

	/**
	 * @var string The name of the sub-option that will store the first run flag.
	 */
	public $defaults_option_name = 'wpml_tec_did_set_defaults';

	/**
	 * @var Tribe__Settings_Manager
	 */
	protected $settings_manager;

	/**
	 * Tribe__Events__Integrations__WPML__Defaults constructor.
	 *
	 * @param SitePress|null               $sitepress
	 * @param Tribe__Settings_Manager|null $settings_manager
	 */
	public function __construct( Tribe__Settings_Manager $settings_manager = null ) {
		$this->settings_manager = $settings_manager ? $settings_manager : Tribe__Settings_Manager::instance();
	}

	/**
	 * The class singleton constructor
	 *
	 * @return Tribe__Events__Integrations__WPML__Defaults
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks whether default custom field translation option values have been for the current installation.
	 *
	 * @return bool Whether defaaults have been set already or not.
	 */
	public function has_set_defaults() {
		return false !== Tribe__Settings_Manager::get_option( $this->defaults_option_name, false );
	}

	/**
	 * Dumps the contents of the default WPML config file for the plugin to the root plugin folder.
	 *
	 * @return bool `true` if the file was correctly written or exists already, `false` if the file does not exist
	 *              and the plugin could not write it; in the latter case the plugin will show a notice.
	 */
	public function setup_config_file() {
		// run just once in this request...
		remove_action( current_action(), [ $this, 'on_parse_config_file' ] );
		// ...and never again
		Tribe__Settings_Manager::set_option( $this->defaults_option_name, true );

		$destination = $this->get_config_file_path();

		// if the user already put one in place skip
		if ( file_exists( $destination ) ) {
			return true;
		}

		$written = file_put_contents( $destination, $this->get_config_file_contents() );

		if ( ! $written ) {
			$message = $this->get_config_file_fail_message();
			$html = sprintf( '<p class="error">%s</p>', esc_html( $message ) );
			tribe_notice( 'tec-wpml-config-file-not-written', $html, [ 'type' => 'error' ] );

			return false;
		}

		return true;
	}

	/**
	 * Returns the path to the WPML config file for the plugin.
	 *
	 * @return string
	 */
	protected function get_config_file_path() {
		return Tribe__Events__Main::instance()->pluginPath . 'wpml-config.xml';
	}

	/**
	 * Returns the default content of WPML config file for the plugin.
	 *
	 * @return string
	 */
	protected function get_config_file_contents() {
		return '<wpml-config>
			<custom-fields>
			</custom-fields>
		</wpml-config>';
	}

	/**
	 * Returns the notice that will be shown to the user if the WPML config file could not be written.
	 *
	 * @return string
	 */
	protected function get_config_file_fail_message() {
		$message = __( 'The Events Calendar could not write WPML default config file: please create the file manually.', 'the-events-calendar' );

		return $message;
	}

}
