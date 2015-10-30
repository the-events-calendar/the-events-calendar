<?php

/**
 * Shows a welcome or update message after the plugin is installed/updated
 */
class Tribe__Events__Activation_Page {
	/** @var self */
	private static $instance = null;

	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_redirect' ), 10, 0 );
		add_action( 'admin_menu', array( $this, 'register_page' ), 100, 0 ); // come in after the default page is registered

		add_action( 'update_plugin_complete_actions', array( $this, 'update_complete_actions' ), 15, 2 );
		add_action( 'update_bulk_plugins_complete_actions', array( $this, 'update_complete_actions' ), 15, 2 );
	}

	/**
	 * Filter the Default WordPress actions when updating the plugin to prevent users to be redirected if they have an
	 * specific intention of going back to the plugins page.
	 *
	 * @param  array $actions The Array of links (html)
	 * @param  string $plugin Which plugins are been updated
	 * @return array          The filtered Links
	 */
	public function update_complete_actions( $actions, $plugin ) {
		$plugins = array();

		if ( ! empty( $_GET['plugins'] ) ) {
			$plugins = explode( ',', esc_attr( $_GET['plugins'] ) );
		}

		if ( ! in_array( Tribe__Events__Main::instance()->pluginDir . 'the-events-calendar.php', $plugins ) ){
			return $actions;
		}

		if ( isset( $actions['plugins_page'] ) ) {
			$actions['plugins_page'] = '<a href="' . esc_url( self_admin_url( 'plugins.php?tec-skip-welcome' ) ) . '" title="' . esc_attr__( 'Go to plugins page' ) . '" target="_parent">' . esc_html__( 'Return to Plugins page' ) . '</a>';

			if ( ! current_user_can( 'activate_plugins' ) ){
				unset( $actions['plugins_page'] );
			}
		}

		if ( isset( $actions['updates_page'] ) ) {
			$actions['updates_page'] = '<a href="' . esc_url( self_admin_url( 'update-core.php?tec-skip-welcome' ) ) . '" title="' . esc_attr__( 'Go to WordPress Updates page' ) . '" target="_parent">' . esc_html__( 'Return to WordPress Updates' ) . '</a>';
		}

		return $actions;
	}

	public function maybe_redirect() {
		if ( ! empty( $_POST ) ) {
			return; // don't interrupt anything the user's trying to do
		}

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) {
			return; // probably the plugin update/install iframe
		}

		if ( isset( $_GET['tec-welcome-message'] ) || isset( $_GET['tec-update-message'] ) ) {
			return; // no infinite redirects
		}

		if ( isset( $_GET['tec-skip-welcome'] ) ) {
			return; // a way to skip these checks and
		}

		// bail if we aren't activating a plugin
		if ( ! get_transient( '_tribe_events_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_tribe_events_activation_redirect' );

		if ( ! current_user_can( Tribe__Settings::instance()->requiredCap ) ){
			return;
		}

		if ( $this->showed_update_message_for_current_version() ) {
			return;
		}

		// the redirect might be intercepted by another plugin, but
		// we'll go ahead and mark it as viewed right now, just in case
		// we end up in a redirect loop
		// see #31088
		$this->log_display_of_message_page();

		if ( $this->is_new_install() ) {
			$this->redirect_to_welcome_page();
		}

		/*
		 * TODO: determine if we wish to keep the update splash screen in the future
		else {
			$this->redirect_to_update_page();
		}
		*/
	}

	/**
	 * Have we shown the welcome/update message for the current version?
	 *
	 * @return bool
	 */
	protected function showed_update_message_for_current_version() {
		$message_version_displayed = Tribe__Settings_Manager::get_option( 'last-update-message' );
		if ( empty( $message_version_displayed ) ) {
			return false;
		}
		if ( version_compare( $message_version_displayed, Tribe__Events__Main::VERSION, '<' ) ) {
			return false;
		}
		return true;
	}

	protected function log_display_of_message_page() {
		Tribe__Settings_Manager::set_option( 'last-update-message', Tribe__Events__Main::VERSION );
	}

	/**
	 * The previous_ecp_versions option will be empty or set to 0
	 * if the current version is the first version to be installed.
	 *
	 * @return bool
	 * @see Tribe__Events__Main::maybeSetTECVersion()
	 */
	protected function is_new_install() {
		$previous_versions = Tribe__Settings_Manager::get_option( 'previous_ecp_versions' );
		return empty( $previous_versions ) || ( end( $previous_versions ) == '0' );
	}

	protected function redirect_to_welcome_page() {
		$url = $this->get_message_page_url( 'tec-welcome-message' );
		wp_safe_redirect( $url );
		exit();
	}

	protected function redirect_to_update_page() {
		$url = $this->get_message_page_url( 'tec-update-message' );
		wp_safe_redirect( $url );
		exit();
	}

	protected function get_message_page_url( $slug ) {
		$settings = Tribe__Settings::instance();
		// get the base settings page url
		$url  = apply_filters(
			'tribe_settings_url', add_query_arg(
				array(
					'post_type' => Tribe__Events__Main::POSTTYPE,
					'page'      => $settings->adminSlug,
				), admin_url( 'edit.php' )
			)
		);
		$url = esc_url_raw( add_query_arg( $slug, 1, $url ) );
		return $url;
	}

	public function register_page() {
		if ( isset( $_GET['tec-welcome-message'] ) ) {
			$this->disable_default_settings_page();
			add_action( 'tribe_events_page_' . Tribe__Settings::$parent_slug, array( $this, 'display_welcome_page' ) );
		} elseif ( isset( $_GET['tec-update-message'] ) ) {
			$this->disable_default_settings_page();
			add_action( 'tribe_events_page_' . Tribe__Settings::$parent_slug, array( $this, 'display_update_page' ) );
		}
	}

	protected function disable_default_settings_page() {
		remove_action( 'tribe_events_page_' . Tribe__Settings::$parent_slug, array( Tribe__Settings::instance(), 'generatePage' ) );
	}

	public function display_welcome_page() {
		do_action( 'tribe_settings_top' );
		echo '<div class="tribe_settings tribe_welcome_page wrap">';
		echo '<h1>';
		echo $this->welcome_page_title();
		echo '</h1>';
		echo $this->welcome_page_content();
		echo '</div>';
		do_action( 'tribe_settings_bottom' );
		$this->log_display_of_message_page();
	}

	protected function welcome_page_title() {
		return __( 'Welcome to The Events Calendar', 'the-events-calendar' );
	}

	protected function welcome_page_content() {
		return $this->load_template( 'admin-welcome-message' );
	}

	public function display_update_page() {
		do_action( 'tribe_settings_top' );
		echo '<div class="tribe_settings tribe_update_page wrap">';
		echo '<h1>';
		echo $this->update_page_title();
		echo '</h1>';
		echo $this->update_page_content();
		echo '</div>';
		do_action( 'tribe_settings_bottom' );
		$this->log_display_of_message_page();
	}

	protected function update_page_title() {
		return __( 'Thanks for Updating The Events Calendar', 'the-events-calendar' );
	}

	protected function update_page_content() {
		return $this->load_template( 'admin-update-message' );
	}

	protected function load_template( $name ) {
		ob_start();
		include trailingslashit( Tribe__Events__Main::instance()->pluginPath ) . 'src/admin-views/' . $name . '.php';
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
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}
