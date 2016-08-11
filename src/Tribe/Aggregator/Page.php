<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Page {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * The page slug
	 * @var string
	 */
	public static $slug = 'aggregator';

	/**
	 * Stores the Registred ID from `add_submenu_page`
	 *
	 * @var string
	 */
	public $ID;


	/**
	 * Stores the Tabs Manager class
	 *
	 * @var null|Tribe__Events__Aggregator__Tabs
	 */
	public $tabs;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependecies
	 */
	private function __construct() {
		$plugin = Tribe__Events__Main::instance();

		add_action( 'admin_menu', array( $this, 'register_menu_item' ) );
		add_action( 'current_screen', array( $this, 'action_request' ) );

		// Setup Tabs Instance
		$this->tabs = Tribe__Events__Aggregator__Tabs::instance();

		// Load these on all the pages
		tribe_assets( $plugin,
			array(
				array(
					'tribe-ea-fields',
					'aggregator-fields.js',
					array(
						'jquery',
						'tribe-datatables',
						'underscore',
						'tribe-bumpdown',
						'tribe-dependency',
						'tribe-events-select2',
					),
				),
				array( 'tribe-ea-page', 'aggregator-page.css', array( 'datatables-css' ) ),
			),
			'admin_enqueue_scripts',
			array(
				'conditionals' => array(
					array( $this, 'is_screen' ),
				),
				'localize' => (object) array(
					'name' => 'tribe_aggregator',
					'data' => array(
						'csv_column_mapping' => array(
							'events' => get_option( 'tribe_events_import_column_mapping_events', array() ),
							'organizers' => get_option( 'tribe_events_import_column_mapping_organizers', array() ),
							'venues' => get_option( 'tribe_events_import_column_mapping_venues', array() ),
						),
						'l10n' => array(
							'preview_fetch_error_prefix' => __( 'There was an error fetching the results from your import:', 'the-events-calendar' ),
							'import_all' => __( 'Import All (%d)', 'the-events-calendar' ),
							'import_checked' => __( 'Import Checked (%d)', 'the-events-calendar' ),
							'create_schedule' => __( 'Save Scheduled Import', 'the-events-calendar' ),
							'edit_save' => __( 'Save Changes', 'the-events-calendar' ),
							'events_required_for_manual_submit' => __( 'Your import must include at least one event', 'the-events-calendar' ),
							'debug' => defined( 'WP_DEBUG' ) && true === WP_DEBUG,
						),
						'default_settings' => Tribe__Events__Aggregator__Settings::instance()->get_all_default_settings(),
					),
				),
			)
		);
	}

	/**
	 * Hooked to `current_screen` allow tabs and other parts of the plugin to hook to aggregator before rendering any headers
	 *
	 * @param  WP_Screen $screen Variable from `current_screen`
	 *
	 * @return bool
	 */
	public function action_request( $screen ) {
		if ( ! $this->is_screen() ) {
			return false;
		}

		/**
		 * Fires an Action to allow Form actions to be hooked to
		 */
		return do_action( 'tribe_aggregator_page_request' );
	}

	/**
	 * Checks if we are in the correct screen
	 *
	 * @return boolean
	 */
	public function is_screen() {
		return Tribe__Admin__Helpers::instance()->is_screen( $this->ID );
	}

	/**
	 * Returns the main admin settings URL.
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args = array(), $relative = false ) {
		$defaults = array(
			'page' => self::$slug,
			'post_type' => Tribe__Events__Main::POSTTYPE,
		);

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Base relative URL
		$url = 'edit.php';

		// Keep the URL as a Relative one
		if ( ! $relative ) {
			$url = admin_url( $url );
		}

		// Add the Arguments
		$url = add_query_arg( $args, $url );

		/**
		 * Allow users to filter the Admin Page URL
		 *
		 * @param string $url
		 * @param array  $args
		 */
		$url = apply_filters( 'tribe_aggregator_admin_page', $url, $args );

		return $url;
	}

	/**
	 * Gets the Menu label for the Aggregator
	 *
	 * @return string
	 */
	public function get_menu_label() {
		return __( 'Import', 'the-events-calendar' );
	}

	/**
	 * Gets the Page title for the Aggegator
	 *
	 * @return string
	 */
	public function get_page_title() {
		return __( 'Events Import', 'the-events-calendar' );
	}

	/**
	 * Register the Sub Menu item for this page
	 *
	 * @return string Page ID on WordPress
	 */
	public function register_menu_item() {
		$cpt = get_post_type_object( Tribe__Events__Main::POSTTYPE );
		$this->ID = add_submenu_page(
			$this->get_url( array( 'page' => null ), true ),
			esc_html( $this->get_page_title() ),
			esc_html( $this->get_menu_label() ),
			$cpt->cap->publish_posts,
			self::$slug,
			array( $this, 'render' )
		);

		return $this->ID;
	}

	/**
	 * A very simple method to include a Aggregator Template, allowing filtering and additions using hooks.
	 *
	 * @param  string  $name Which file we are talking about including
	 * @param  array   $data Any context data you need to expose to this file
	 * @param  boolean $echo If we should also print the Template
	 * @return string        Final Content HTML
	 */
	public function template( $name, $data = array(), $echo = true ) {
		// Clean this Variable
		$name = array_map( 'sanitize_title_with_dashes', (array) explode( '/', $name ) );

		$file = Tribe__Events__Main::instance()->plugin_path;
		$file .= 'src/admin-views/aggregator/' . implode( DIRECTORY_SEPARATOR, $name ) . '.php';

		/**
		 * A more Specific Filter that will include the template name
		 *
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		$file = apply_filters( 'tribe_aggregator_template_file', $file, $name, $data );

		if ( ! file_exists( $file ) ) {
			return false;
		}

		ob_start();
		/**
		 * Fires an Action before including the template file
		 *
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		do_action( 'tribe_aggregator_template_before_include', $file, $name, $data );

		// Make any provided variables available in the template's symbol table
		if ( is_array( $data ) ) {
			extract( $data );
		}

		include $file;

		/**
		 * Fires an Action After including the template file
		 *
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		do_action( 'tribe_aggregator_template_after_include', $file, $name, $data );
		$html = ob_get_clean();

		/**
		 * Allow users to filter the final HTML
		 *
		 * @param string $html     The final HTML
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		$html = apply_filters( 'tribe_aggregator_template_html', $html, $file, $name, $data );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * A simple shortcut to render the Template for the page
	 *
	 * @return string
	 */
	public function render() {
		return $this->template( 'page' );
	}
}
