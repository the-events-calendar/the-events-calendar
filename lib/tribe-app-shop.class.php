<?php

	// don't load directly
	if ( !defined( 'ABSPATH' ) ) die( '-1' );

	if ( !class_exists( 'TribeAppShop' ) ) {

		/**
		 *
		 */
		class TribeAppShop {

			/**
			 *
			 */
			const API_VERSION = "1.0";
			/**
			 *
			 */
			const API_ENDPOINT = "http://tri.be/api/app-shop/";

			const CACHE_KEY_BASE   = "tribe-app-shop";
			const CACHE_EXPIRATION = 300; //5 min

			const MENU_SLUG = "tribe-app-shop";

			/**
			 * Singleton instance
			 *
			 * @var null or TribeAppShop
			 */
			private static $instance = NULL;
			/**
			 * The slug for the new admin page
			 *
			 * @var string
			 */
			private $admin_page = NULL;


			/**
			 * Class constructor
			 */
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'add_menu_page' ), 100 );
				add_action( 'wp_before_admin_bar_render', array( $this, 'add_toolbar_item' ), 20 );
			}

			/**
			 * Adds the page to the admin menu
			 */
			public function add_menu_page() {
				$page_title = __( 'Event Add-Ons', 'tribe-events-calendar' );
				$menu_title = __( 'Event Add-Ons', 'tribe-events-calendar' );
				$capability = "edit_posts";


				$where = 'edit.php?post_type=' . TribeEvents::POSTTYPE;

				$this->admin_page = add_submenu_page( $where, $page_title, $menu_title, $capability, self::MENU_SLUG, array( $this, 'do_menu_page' ) );

				add_action( 'admin_print_styles-' . $this->admin_page, array( $this, 'enqueue' ) );

			}

			public function add_toolbar_item() {
				global $wp_admin_bar;

				$where = 'edit.php?post_type=' . TribeEvents::POSTTYPE;

				$wp_admin_bar->add_menu( array( 'id'     => 'tribe-events-app-shop',
				                                'title'  => __( 'Event Add-Ons', 'tribe-events-calendar' ),
				                                'href'   => admin_url( untrailingslashit( $where ) . "&page=" . self::MENU_SLUG ),
				                                'parent' => 'tribe-events-settings-group' ) );


			}

			public function enqueue() {
				wp_enqueue_style( 'app-shop', TribeEvents::instance()->pluginUrl . 'resources/app-shop.css' );
				wp_enqueue_script( 'app-shop', TribeEvents::instance()->pluginUrl . 'resources/app-shop.js' );

			}

			/**
			 *
			 */
			public function do_menu_page() {
				$remote = $this->get_all_products();
				if ( $remote ) {
					$products = NULL;
					if ( property_exists( $remote, 'data' ) ) {
						$products = $remote->data;
					}
					$banner = NULL;
					if ( property_exists( $remote, 'banner' ) ) {
						$banner = $remote->banner;
					}

					$categories = array_unique( wp_list_pluck( $products, 'category' ) );

					include_once( TribeEvents::instance()->pluginPath . 'admin-views/app-shop.php' );
				}
			}


			private function get_all_products() {

				$cache_key = self::CACHE_KEY_BASE . '-products';
				$products  = get_transient( $cache_key );

				if ( !$products ) {
					$products = $this->remote_get( 'get-products' );
					if ( $products && !$products->error ) {
						set_transient( $cache_key, $products, self::CACHE_EXPIRATION );
					}
				}

				if ( is_string( $products ) ) {
					$products = json_decode( $products );
				}
				return $products;

			}

			/**
			 * @param            $action
			 * @param array|null $args
			 * @return array|WP_Error
			 */
			private function remote_get( $action, $args = NULL ) {

				$url = trailingslashit( self::API_ENDPOINT . self::API_VERSION ) . $action;

				$ret = wp_remote_get( $url );

				if ( $ret && isset( $ret["body"] ) ) {
					return json_decode( $ret["body"] );
				}

				return NULL;

			}

			/**
			 * Static Singleton Factory Method
			 *
			 * @since  2.0.5
			 * @author jkudish
			 * @return TribeAppShop
			 */
			public static function instance() {
				if ( !isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}
				return self::$instance;
			}

		}

		TribeAppShop::instance();
	}