<?php


	class Tribe__Events__Asset__Chosen extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery' ) );
			$css_path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'chosen/public/chosen.css', true );
			$path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'chosen/public/chosen.jquery.js', true );
			wp_enqueue_style( $this->prefix . '-chosen-style', $css_path );

			$handle = $this->prefix . '-chosen-jquery';
			wp_enqueue_script( $handle, $path, $deps, '0.9.5', false );
			Tribe__Events__Template_Factory::add_vendor_script( $handle );
		}
	}