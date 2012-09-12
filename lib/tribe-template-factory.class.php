<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_Template_Factory') ) {
	class Tribe_Template_Factory {
		public static function debug( $label = null, $start = TRUE, $echo = TRUE) {
			if( defined('WP_DEBUG') && WP_DEBUG && !empty($label) ) {
				$label = (!$start) ? '/' . $label : $label;
				$html = '<!-- ' . $label . ' -->' . "\n";
				if( $echo ) {
					echo $html;
				} else {
					return $html;
				}
			}
		}
	}
}