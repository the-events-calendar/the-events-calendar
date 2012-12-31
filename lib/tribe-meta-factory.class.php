<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_Meta_Factory') ) {
	class Tribe_Meta_Factory {
		
		var $meta = array();
		var $meta_group = array();

		const META_IDS = 'meta_ids';

		function __contstruct(){

		}

		/**
		 * register meta or meta_groups
		 * 
		 * @param  string $meta
		 * @param  array  $args
		 * @return bool
		 */
		public static function register($meta_id, $args = array()) {
			global $tribe_meta_factory;
			$defaults = array(
				'wrap' => array(
					'before'=>'<div class="tribe-meta">',
					'after'=>'</div>',
					'label_before'=>'<label>',
					'label_after'=>'</label>',
					'meta_before'=>'<div class="tribe-meta-value">',
					'meta_after'=>'</div>'
					),
				'classes_for' => array(),
				'register_type' => 'meta',
				'register_overwrite' => false,
				'register_callback' => null,
				'filter_callback' => null,
				'callback' => null,
				'meta_value' => null,
				'label' => ucwords( preg_replace('/[_-]/', ' ', $meta_id) ), // if label is not set then use humanized form of meta_group_id
				'show_on_meta' => true, // bool for automatically displaying meta within "the meta area" of a specific display
				'priority' => 100
				);
			$args = wp_parse_args($args, $defaults);

			// setup default meta ids placeholder for meta_group registration
			if( $args['register_type'] == 'meta_group' && empty($args[self::META_IDS]) ) {
				$args[self::META_IDS] = array();
			}

			do_action( 'tribe_meta_factory_register', $meta_id, $args );

			// check if we should overwrite the existing registration args if set
			if( isset($tribe_meta_factory->{$args['register_type']}[$meta_id]) && ! $args['register_overwrite'] ) {
				return false;
			// otherwise merge existing args with new args and reregister
			} else if( isset($tribe_meta_factory->{$args['register_type']}[$meta_id])) {
				$args = wp_parse_args( $args, $tribe_meta_factory->{$args['register_type']}[$meta_id] );
			}

			$tribe_meta_factory->{$args['register_type']}[$meta_id] = $args;

			// associate a meta item to a meta group(s) isset
			if( $args['register_type'] == 'meta' && ! empty($args['group']) ) {
				foreach( (array) $args['group'] as $group ) {
					// if group doesn't exist - then register it before proceeding
					if( ! self::check_exists( $group, 'meta_group' ) ) {
						tribe_register_meta_group( $group );
					// if the meta_id has already been added to the group move onto the next one
					} elseif(in_array($meta_id, $tribe_meta_factory->meta_group[$group][self::META_IDS])) {
						continue;
					}
					$tribe_meta_factory->meta_group[$group][self::META_IDS][] = $meta_id;
				}
			}

			// let the request know if we are successful for registering
			return true;
		}

		public static function check_exists( $meta_id, $type = 'meta' ){
			global $tribe_meta_factory;
			$status = isset( $tribe_meta_factory->{$type}[$meta_id] ) ? true : false ;
			return apply_filters('tribe_meta_factory_check_exists', $status );
		}

		public static function get_args( $meta_id, $type = 'meta' ){
			global $tribe_meta_factory;
			$args = self::check_exists( $meta_id, $type ) ? $tribe_meta_factory->{$type}[$meta_id] : array();
			return apply_filters('tribe_meta_factory_get_args', $args );
		}

		public static function get_order( $meta_id = null ){
			global $tribe_meta_factory;

			$ordered_group = array();

			if( self::check_exists( $meta_id, 'meta_group' ) ){
				foreach($tribe_meta_factory->meta_group[$meta_id][self::META_IDS] as $key ){
					if( $item = self::get_args( $key ) ){
						$ordered_group[ $item['priority'] ][] = $key;
					}
				}
			} else {
				foreach($tribe_meta_factory->meta_group as $key => $item ){
					$ordered_group[ $item['priority'] ][] = $key;
				}
			}

			ksort($ordered_group);

			return $ordered_group;
		}

		public static function set_visibility( $meta_id, $type = 'meta', $status = true ){
			global $tribe_meta_factory;
			if( self::check_exists( $meta_id, $type ) ){
				$tribe_meta_factory->{$type}[$meta_id]['show_on_meta'] = $status;
			}
		}

		public static function template( $label, $meta, $template ) {
			$defaults = array(
				'before'=>'<div>',
				'after'=>'</div>',
				'label_before'=>'<label>',
				'label_after'=>'</label>',
				'meta_before'=>'<div>',
				'meta_after'=>'</div>'
				);
			$template = wp_parse_args($template, $defaults);
			$html = sprintf('%s%s%s%s',
				$template['before'],
				!empty($label) ? $template['label_before'] . $label . $template['label_after'] : '',
				!empty($meta) ? $template['meta_before'] . $meta . $template['meta_after'] : '',
				$template['after']
				);
			return apply_filters('tribe_meta_factory_template', $html, $label, $meta, $template );
		}
	}
	global $tribe_meta_factory;
	$tribe_meta_factory = new Tribe_Meta_Factory();	
}