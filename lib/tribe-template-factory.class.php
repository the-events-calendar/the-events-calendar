<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_Template_Factory') ) {
	class Tribe_Template_Factory {
		public function debug_wrapper( $html, $filter_name ){
			return self::debug($filter_name) . $html . self::debug($filter_name, false);
		}
		public static function debug( $label = null, $start = TRUE, $echo = false) {
			if( defined('WP_DEBUG') && WP_DEBUG && !empty($label) ) {
				$label = (!$start) ? '/' . $label : $label;
				$html = "\n" . '<!-- ' . $label . ' -->' . "\n";
				if( $echo ) {
					echo $html;
				} else {
					return $html;
				}
			}
		}
	}
	add_filter( 'tribe_template_factory_debug', array( 'Tribe_Template_Factory', 'debug_wrapper' ), 1, 2 );
}