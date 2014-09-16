<?php

/**
 * Shows a welcome or update message after the plugin is installed/updated
 */
class Tribe__Events__Activation_Page {
	/** @var self */
	private static $instance = NULL;

	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_redirect' ), 10, 0 );
		add_action( 'admin_menu', array( $this, 'register_page' ), 100, 0 ); // come in after the default page is registered
	}

	public function maybe_redirect() {
		if ( !empty($_POST) ) {
			return; // don't interrupt anything the user's trying to do
		}
		if ( !is_admin() || defined('DOING_AJAX') ) {
			return;
		}
		if ( isset($_GET['tec-welcome-message']) || isset($_GET['tec-update-message']) ) {
			return; // no infinite redirects
		}
		if ( $this->showed_update_message_for_current_version() ) {
			return;
		}
		if ( $this->is_new_install() ) {
			$this->redirect_to_welcome_page();
		} else {
			$this->redirect_to_update_page();
		}
	}

	/**
	 * Have we shown the welcome/update message for the current version?
	 *
	 * @return bool
	 */
	protected function showed_update_message_for_current_version() {
		$tec = TribeEvents::instance();
		$message_version_displayed = $tec->getOption('last-update-message');
		if ( empty($message_version_displayed) ) {
			return FALSE;
		}
		if ( version_compare( $message_version_displayed, TribeEvents::VERSION, '<' ) ) {
			return FALSE;
		}
		return TRUE;
	}

	protected function log_display_of_message_page() {
		$tec = TribeEvents::instance();
		$tec->setOption('last-update-message', TribeEvents::VERSION);
	}

	/**
	 * The previous_ecp_versions option will be empty or set to 0
	 * if the current version is the first version to be installed.
	 *
	 * @return bool
	 * @see TribeEvents::maybeSetTECVersion()
	 */
	protected function is_new_install() {
		$tec = TribeEvents::instance();
		$previous_versions = $tec->getOption('previous_ecp_versions');
		return empty($previous_versions) || ( end($previous_versions) == '0' );
	}

	protected function redirect_to_welcome_page() {
		$url = $this->get_message_page_url( 'tec-welcome-message' );
		wp_safe_redirect($url);
		exit();
	}

	protected function redirect_to_update_page() {
		$url = $this->get_message_page_url( 'tec-update-message' );
		wp_safe_redirect($url);
		exit();
	}

	protected function get_message_page_url( $slug ) {
		$settings = TribeSettings::instance();
		// get the base settings page url
		$url  = apply_filters(
			'tribe_settings_url', add_query_arg(
				array(
					'post_type' => TribeEvents::POSTTYPE,
					'page'      => $settings->adminSlug
				), admin_url( 'edit.php' )
			)
		);
		$url = add_query_arg( $slug, 1, $url );
		return $url;
	}

	public function register_page() {
		// tribe_events_page_tribe-events-calendar
		if ( isset($_GET['tec-welcome-message']) ) {
			$this->disable_default_settings_page();
			add_action( 'tribe_events_page_tribe-events-calendar', array( $this, 'display_welcome_page' ) );
		} elseif ( isset( $_GET['tec-update-message'] ) ) {
			$this->disable_default_settings_page();
			add_action( 'tribe_events_page_tribe-events-calendar', array( $this, 'display_update_page' ) );
		}
	}

	protected function disable_default_settings_page() {
		remove_action( 'tribe_events_page_tribe-events-calendar', array( TribeSettings::instance(), 'generatePage' ) );
	}

	public function display_welcome_page() {
		do_action( 'tribe_settings_top' );
		echo '<div class="tribe_settings tribe_welcome_page wrap">';
		echo '<h2>';
		echo $this->welcome_page_title();
		echo '</h2>';
		echo $this->welcome_page_content();
		echo '</div>';
		do_action( 'tribe_settings_bottom' );
		$this->log_display_of_message_page();
	}

	protected function welcome_page_title() {
		return __('Welcome to The Events Calendar', 'tribe-events-calendar');
	}

	protected function welcome_page_content() {
		return $this->load_template('admin-welcome-message');
	}

	public function display_update_page() {
		do_action( 'tribe_settings_top' );
		echo '<div class="tribe_settings tribe_update_page wrap">';
		echo '<h2>';
		echo $this->update_page_title();
		echo '</h2>';
		echo $this->update_page_content();
		echo '</div>';
		do_action( 'tribe_settings_bottom' );
		$this->log_display_of_message_page();
	}

	protected function update_page_title() {
		return __('Thanks for Updating The Events Calendar', 'tribe-events-calendar');
	}

	protected function update_page_content() {
		require_once("Changelog_Reader.php");
		return $this->load_template('admin-update-message');
	}

	protected function load_template( $name ) {
		ob_start();
		include(trailingslashit(TribeEvents::instance()->pluginPath).'admin-views/'.$name.'.php');
		return ob_get_clean();
	}

	/**
	 * Initialize the global instance of the class.
	 */
	public static function init() {
		self::instance()->add_hooks();
	}

	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


} 