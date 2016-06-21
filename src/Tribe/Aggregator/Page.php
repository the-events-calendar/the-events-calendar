<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Aggregator__Page {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependecies
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register' ) );

		// Setup Tabs Instance
		$this->tabs = Tribe__Events__Aggregator__Tabs::instance();
	}

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
	 * Checks if we are in the correct screen
	 *
	 * @return boolean
	 */
	public function is_screen() {
		return Tribe__Admin__Helpers::is_screen( $this->ID );
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

		// Escape after the filter
		return esc_url( $url );
	}

	public function get_menu_label() {
		return esc_html__( 'Aggregator', 'the-events-calendar' );
	}

	public function get_page_title() {
		return esc_html__( 'Events Aggregator', 'the-events-calendar' );
	}

	public function register() {
		$cpt = get_post_type_object( Tribe__Events__Main::POSTTYPE );
		$this->ID = add_submenu_page(
			$this->get_url( array( 'page' => null ), true ),
			$this->get_page_title(),
			$this->get_menu_label(),
			$cpt->cap->publish_posts,
			self::$slug,
			array( $this, 'render' )
		);
	}

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

		include( $file );

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

	public function render() {
		$this->template( 'page' );
	}
}