<?php


	class Tribe__Events__Asset__Select2 extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$css_path = Tribe_Template_Factory::getMinFile( $this->vendor_url . 'select2/select2.css', true );
			$path = Tribe_Template_Factory::getMinFile( $this->vendor_url . 'select2/select2.js', true );
			wp_enqueue_style( $this->prefix . '-select2-css', $css_path );
			wp_enqueue_script( $this->prefix . '-select2', $path, 'jquery', '3.2' );
			Tribe_Template_Factory::add_vendor_script( $this->prefix . '-select2' );
		}
	}