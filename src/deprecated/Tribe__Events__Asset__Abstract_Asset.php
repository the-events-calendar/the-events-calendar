<?php


	abstract class Tribe__Events__Asset__Abstract_Asset {

		/**
		 * @var string
		 */
		protected $name;

		/**
		 * @var array
		 */
		protected $deps;

		/**
		 * @var string
		 */
		protected $vendor_url;

		/**
		 * @var string
		 */
		protected $prefix;

		/**
		 * @var Tribe__Events__Main
		 */
		protected $tec;

		/**
		 * @var array An array specifying aliases for this asset package scripts or styles.
		 *
		 * @see Tribe__Events__Asset__Abstract_Asset::has_script_alias()
		 */
		protected $aliases = array();

		public function set_name( $name ) {
			$this->name = $name;
		}

		public function set_deps( $deps ) {
			$this->deps = $deps;
		}

		public function set_vendor_url( $vendor_url ) {
			$this->vendor_url = $vendor_url;
		}

		public function set_prefix( $prefix ) {
			$this->prefix = $prefix;
		}

		public function set_tec( $tec ) {
			$this->tec = $tec;
		}

		/*
		 * Handles the asset request
		 */
		abstract public function handle();

		/**
		 * @return array
		 */
		public function get_aliases() {
			return $this->aliases;
		}

		/**
		 * @param array $aliases
		 */
		public function set_aliases( $aliases ) {
			$this->aliases = $aliases;
		}

		/**
		 * Filters the script version.
		 *
		 * Uses `Tribe__Events__Main::VERSION` by default.
		 *
		 * @param string $filter The filter name, `tribe_events_js_version` by
		 *                       default.
		 *
		 * @return mixed|void
		 */
		protected function filter_js_version( $filter = null ) {
			$filter = is_string( $filter ) ? $filter : 'tribe_events_js_version';

			return apply_filters( $filter, Tribe__Events__Main::VERSION );
		}

		/**
		 * Whether drop-in replacement for this asset package script have been already loaded
		 * from other plugins or not.
		 *
		 * The drop-in replacements in the `aliases` property are manually curated and tested on a
		 * single asset package base; the method will not make any guessing or interpolation.
		 * E.g. the `Select2` asset package specifies that if ACF is active and the `select2` handle
		 * has been queued already then there is no need to queue our own version of `select2`.
		 *
		 * If a more complex and context aware test is needed to discern the presence of script
		 * alias then the value of an `aliases` entry can be set to a callable that should return a bool value, e.g.:
		 *
		 *      $aliases = array(
		 *          'select2' => array(
		 *              'some-plugin/plugin.php' => 'select-2-js', // normal handle test
		 *              'another-plugin/plugin.php' => array( 'Tribe__Spotter' , 'loaded_select_2' ),
		 *           ),
		 *      );
		 *
		 * @param string $slug The slug this script is registered with in the `aliases` property.
		 *
		 * @return bool `true` if a know and tested plugin is active and has already queued a drop-in
		 *              replacement for the script or `false` otherwise.
		 */
		public function has_script_alias( $slug ) {
			if ( ! isset( $this->aliases[ $slug ] ) ) {
				return false;
			}

			global $wp_scripts;

			foreach ( $this->aliases[ $slug ] as $plugin => $registration_handle ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				if ( ! is_plugin_active( $plugin ) ) {
					continue;
				}

				if ( ! is_callable( $registration_handle ) ) {
					if ( in_array( $registration_handle, $wp_scripts->queue ) ) {
						return true;
					}
				} else {
					$has_alias = call_user_func( $registration_handle, $slug );
					if ( $has_alias ) {
						return true;
					}
				}
			}

			return false;
		}

	}
