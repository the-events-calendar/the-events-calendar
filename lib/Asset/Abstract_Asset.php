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
		 * @var string
		 */
		protected $resources_url;

		/**
		 * @var TribeEvents
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

		public function set_resources_url( $resources_url ) {
			$this->resources_url = $resources_url;
		}

		public function set_tec( TribeEvents $tec ) {
			$this->tec = $tec;
		}

		/*
		 * Handles the asset request
		 */
		abstract public function handle();

	}