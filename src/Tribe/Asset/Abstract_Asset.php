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

	}
