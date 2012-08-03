<?php
	class TribeSpinJS {

		static $version = "1.2.5";

		static function register() {
			$tec = TribeEvents::instance();
			$url = trailingslashit( $tec->pluginUrl );
			wp_register_script( 'spin-js', $url . 'vendor/spin.js/dist/spin.min.js', array( 'jquery' ), TribeSpinJS::$version );
		}

		static function load() {
			wp_enqueue_script( 'spin-js' );
		}

	}

	add_action( 'wp_enqueue_scripts', array( 'TribeSpinJS',
	                                         'register' ), 1 );

	add_action( 'admin_enqueue_scripts', array( 'TribeSpinJS',
	                                            'register' ), 1 );
