<?php
/**
 * Meta Factory
 *
 * Events have meta that may change in the way it is displayed across templates.
 * The meta factory provides a storage and templating engine similar to the WordPress
 * Widget Factory which allows for registration, sorting, assignment, templating and
 * deregistration of meta items within a Tribe Meta container (similar to "sidebar")
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Meta_Factory' ) ) {
	class Tribe__Events__Meta_Factory {

		var $meta = array();
		var $meta_group = array();

		const META_IDS = 'meta_ids';

		function __construct() {

		}

		/**
		 * register meta or meta_groups
		 *
		 * @param  string $meta
		 * @param  array  $args
		 *
		 * @return bool
		 */
		public static function register( $meta_id, $args = array() ) {
			global $_tribe_meta_factory;
			$defaults = array(
				'wrap'               => array(
					'before'         => '<div class="%s">',
					'after'          => '</div>',
					'label_before'   => '<label>',
					'label_after'    => '</label>',
					'meta_before'    => '<div class="%s">',
					'meta_separator' => '',
					'meta_after'     => '</div>'
				),
				'classes'            => array(
					'before'      => array( 'tribe-meta' ),
					'meta_before' => array( 'tribe-meta-value' )
				),
				'register_type'      => 'meta',
				'register_overwrite' => false,
				'register_callback'  => null,
				'filter_callback'    => null,
				'callback'           => null,
				'meta_value'         => null,
				'label'              => ucwords( preg_replace( '/[_-]/', ' ', $meta_id ) ),
				// if label is not set then use humanized form of meta_group_id
				'show_on_meta'       => true,
				// bool for automatically displaying meta within "the meta area" of a specific display
				'priority'           => 100
			);
			// before we merge args and defaults lets play nice with the template
			if ( ! empty( $args['wrap'] ) ) {
				$args['wrap'] = wp_parse_args( $args['wrap'], $defaults['wrap'] );
			}
			$args = wp_parse_args( $args, $defaults );

			// setup default meta ids placeholder for meta_group registration
			if ( $args['register_type'] == 'meta_group' && empty( $args[self::META_IDS] ) ) {
				$args[self::META_IDS] = array();
			}

			do_action( 'tribe_meta_factory_register', $meta_id, $args );

			// check if we should overwrite the existing registration args if set
			if ( isset( $_tribe_meta_factory->{$args['register_type']}[$meta_id] ) && ! $args['register_overwrite'] ) {
				return false;
				// otherwise merge existing args with new args and reregister
			} else {
				if ( isset( $_tribe_meta_factory->{$args['register_type']}[$meta_id] ) ) {
					$args = wp_parse_args( $args, $_tribe_meta_factory->{$args['register_type']}[$meta_id] );
				}
			}

			$_tribe_meta_factory->{$args['register_type']}[$meta_id] = $args;

			// associate a meta item to a meta group(s) isset
			if ( $args['register_type'] == 'meta' && ! empty( $args['group'] ) ) {
				foreach ( (array) $args['group'] as $group ) {
					// if group doesn't exist - then register it before proceeding
					if ( ! self::check_exists( $group, 'meta_group' ) ) {
						tribe_register_meta_group( $group );
						// if the meta_id has already been added to the group move onto the next one
					} elseif ( in_array( $meta_id, $_tribe_meta_factory->meta_group[$group][self::META_IDS] ) ) {
						continue;
					}
					$_tribe_meta_factory->meta_group[$group][self::META_IDS][] = $meta_id;
				}
			}

			// let the request know if we are successful for registering
			return true;
		}

		/**
		 * check to see if meta item has been defined
		 *
		 * @param  string $meta_id
		 * @param  string $type
		 *
		 * @return boolean
		 */
		public static function check_exists( $meta_id, $type = 'meta' ) {
			global $_tribe_meta_factory;
			$status = isset( $_tribe_meta_factory->{$type}[$meta_id] ) ? true : false;

			return apply_filters( 'tribe_meta_factory_check_exists', $status );
		}

		/**
		 * get meta arguments
		 *
		 * @param  string $meta_id
		 * @param  string $type
		 *
		 * @return array of arguments
		 */
		public static function get_args( $meta_id, $type = 'meta' ) {
			global $_tribe_meta_factory;
			$args = self::check_exists( $meta_id, $type ) ? $_tribe_meta_factory->{$type}[$meta_id] : array();

			return apply_filters( 'tribe_meta_factory_get_args', $args );
		}

		/**
		 * get the set order of meta items
		 * useful when generically displaying meta for skeleton view or bulk assignments
		 *
		 * @param  string $meta_id
		 *
		 * @return array of ordered meta ids
		 */
		public static function get_order( $meta_id = null ) {
			global $_tribe_meta_factory;

			$ordered_group = array();

			if ( self::check_exists( $meta_id, 'meta_group' ) ) {
				foreach ( $_tribe_meta_factory->meta_group[$meta_id][self::META_IDS] as $key ) {
					if ( $item = self::get_args( $key ) ) {
						$ordered_group[$item['priority']][] = $key;
					}
				}
			} else {
				foreach ( $_tribe_meta_factory->meta_group as $key => $item ) {
					$ordered_group[$item['priority']][] = $key;
				}
			}

			ksort( $ordered_group );

			return $ordered_group;
		}

		/**
		 * set the visibility of a meta item when using a bulk display tag
		 *
		 * @param string  $meta_id
		 * @param string  $type
		 * @param boolean $status
		 */
		public static function set_visibility( $meta_id, $type = 'meta', $status = true ) {
			global $_tribe_meta_factory;
			if ( self::check_exists( $meta_id, $type ) ) {
				$_tribe_meta_factory->{$type}[$meta_id]['show_on_meta'] = $status;
			}
		}

		/**
		 * embed css classes for templating the meta item on display
		 *
		 * @param  string $template
		 * @param  array  $classes
		 *
		 * @return string $template
		 */
		public static function embed_classes( $template, $classes = array() ) {
			if ( ! empty( $classes ) && is_array( $classes ) ) {

				// loop through the available class to template associations
				foreach ( $classes as $key => $class_list ) {
					if ( ! empty( $class_list ) &&
						 ! empty( $template[$key] ) &&
						 ( strpos( $template[$key], '%s' ) !== false || strpos( $template[$key], '%d' ) !== false )
					) {

						// if we're passed an array lets implode it
						$class_list = is_array( $class_list ) ? implode( ' ', $class_list ) : $class_list;

						// process the template string with all classes
						$template[$key] = vsprintf( $template[$key], $class_list );

					}
				}
			}

			return $template;
		}

		/**
		 * return a completed meta template for display
		 * @uses   self::embed_classes for css classes
		 *
		 * @param  string $label
		 * @param  string $meta
		 * @param  string $meta_id
		 * @param  string $type
		 *
		 * @return string $html finished meta item for display
		 */
		public static function template( $label, $meta, $meta_id, $type = 'meta' ) {
			global $_tribe_meta_factory;
			$template = self::embed_classes(
							$_tribe_meta_factory->{$type}[$meta_id]['wrap'],
							$_tribe_meta_factory->{$type}[$meta_id]['classes']
			);
			$html     = sprintf(
				'%s%s%s%s',
				$template['before'],
				! empty( $label ) ? $template['label_before'] . $label . $template['label_after'] : '',
				! empty( $meta ) ? $template['meta_before'] . $meta . $template['meta_after'] : '',
				$template['after']
			);

			return apply_filters( 'tribe_meta_factory_template', $html, $label, $meta, $template );
		}
	}
}
