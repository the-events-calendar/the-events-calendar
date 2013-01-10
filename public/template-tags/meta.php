<?php

/**
 * Meta Factory Classes
 *
 * @uses  Tribe_Meta_Factory
 * @since  3.0
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

/**
 * register a meta group
 *
 * @uses Tribe_Meta_Factory::register()
 * @param string $meta_group_id
 * @param array $args
 * @return bool $success
 * @since 3.0
 */
if ( !function_exists( 'tribe_register_meta_group' ) ) {
	function tribe_register_meta_group( $meta_group_id, $args = array() ) {
		// setup default for registering a meta group
		$defaults = array( 'register_type' => 'meta_group' );
		// parse the $default and $args into the second param for registering a meta item
		return Tribe_Meta_Factory::register( $meta_group_id, wp_parse_args( $args, $defaults) );
	}
}

/**
 * register a meta item
 *
 * @uses Tribe_Meta_Factory::register()
 * @param string $meta_group_id
 * @param array $args
 * @return bool $success
 * @since 3.0
 */
if ( !function_exists( 'tribe_register_meta' ) ) {
	function tribe_register_meta( $meta_id, $args = array() ) {
		return Tribe_Meta_Factory::register( $meta_id, $args );
	}
}
if( !function_exists('tribe_get_meta_group')){
	function tribe_get_meta_group( $meta_group_id, $is_the_meta = false ){

		do_action('tribe_get_meta_group', $meta_group_id, $is_the_meta );

		$type = 'meta_group';

		// die silently if the requested meta group is not registered
		if( ! Tribe_Meta_Factory::check_exists( $meta_group_id, $type ) )
			return false;

		$meta_group = Tribe_Meta_Factory::get_args( $meta_group_id, $type );
		$meta_ids = Tribe_Meta_Factory::get_order( $meta_group_id );
		$group_html = '';

		// internal check for hiding items in the meta
		if( $is_the_meta && ! $meta_group['show_on_meta'] ){
			return false;
		}

		foreach( $meta_ids as $meta_id_group ) {
			foreach( $meta_id_group as $meta_id ){
				$group_html .= tribe_get_meta( $meta_id, $is_the_meta );
			}
		}

		$params = array( $meta_group_id );

		if( !empty($meta['filter_callback']) ){
			return call_user_func_array($meta['filter_callback'], $params);
		}

		if( !empty($meta['callback']) ){
			$value = call_user_func_array($meta['callback'], $params);
		}

		$value = empty($value) ? $group_html : $value;

		$html = !empty($group_html) ? Tribe_Meta_Factory::template( $meta_group['label'], $value, $meta_group_id, 'meta_group' ) : '';
		
		return apply_filters('tribe_get_meta_group', $html, $meta_group_id );
	}
}
if ( !function_exists( 'tribe_get_meta' ) ) {
	function tribe_get_meta( $meta_id, $is_the_meta = false ) {

		do_action('tribe_get_meta', $meta_id, $is_the_meta );

		// die silently if the requested meta item is not registered
		if( ! Tribe_Meta_Factory::check_exists( $meta_id ) )
			return false;

		$meta = Tribe_Meta_Factory::get_args( $meta_id );

		// internal check for hiding items in the meta
		// if( $is_the_meta && ! $meta['show_on_meta'] ){
		if( ! $meta['show_on_meta'] ){
			return false;
		}

		$params = array( $meta_id );

		if( !empty($meta['filter_callback']) ){
			return call_user_func_array($meta['filter_callback'], $params);
		}

		if( !empty($meta['callback']) ){
			$value = call_user_func_array($meta['callback'], $params);
		}

		$value = empty($value) ? $meta['meta_value'] : $value;

		// if we have a value let's build the html template
		$html = !empty($value) ? Tribe_Meta_Factory::template( $meta['label'], $value, $meta_id ) : '';

		return apply_filters('tribe_get_meta', $html, $meta_id );
	}
}

if( !function_exists('tribe_set_the_meta_visibility')) {
	function tribe_set_the_meta_visibility( $meta_id, $status = true, $type = 'meta' ){
		Tribe_Meta_Factory::set_visibility( $meta_id, $type, $status );
	}
}

if(!function_exists('tribe_set_the_meta_template')){
	function tribe_set_the_meta_template( $meta_id, $template = array(), $type = 'meta' ){
		if( is_array( $meta_id) ) {
			foreach( $meta_id as $id ) {
				tribe_set_the_meta_template( $id, $template, $type );
			}
		} else {
			global $_tribe_meta_factory;

			// die silently if the requested meta group is not registered
			if( ! Tribe_Meta_Factory::check_exists( $meta_id, $type ) )
				return false;

			if( !empty( $template ) ){
				$_tribe_meta_factory->{$type}[$meta_id]['wrap'] = $template;
			}
		}
		
	}
}

if(!function_exists('tribe_set_meta_priority')){
	function tribe_set_meta_priority( $meta_id, $priority = 100, $type = 'meta' ){
		if( is_array( $meta_id) ) {
			foreach( $meta_id as $id => $priority) {
				tribe_set_meta_priority( $id, $priority, $type );
			}
		} else {
			global $_tribe_meta_factory;

			// die silently if the requested meta group is not registered
			if( ! Tribe_Meta_Factory::check_exists( $meta_id, $type ) )
				return false;

			if( !empty( $priority ) ){
				$_tribe_meta_factory->{$type}[$meta_id]['priority'] = $priority;
			}
		}
	}
}

if(!function_exists('tribe_set_meta_value')){
	function tribe_set_meta_value( $meta_id, $value, $value_type = 'meta_value', $type = 'meta' ){
		if( is_array( $meta_id) ) {
			foreach( $meta_id as $id => $priority) {
				tribe_set_meta_priority( $id, $priority, $type );
			}
		} else {
			global $_tribe_meta_factory;

			// die silently if the requested meta group is not registered
			if( ! Tribe_Meta_Factory::check_exists( $meta_id, $type ) )
				return false;

			$_tribe_meta_factory->{$type}[$meta_id][$value_type] = $value;
		}
	}
}

if(!function_exists('tribe_set_meta_label')){
	function tribe_set_meta_label( $meta_id, $label = '', $type = 'meta' ){
		if( is_array( $meta_id) ) {
			foreach( $meta_id as $id => $label) {
				tribe_set_meta_label( $id, $label, $type );
			}
		} else {
			global $_tribe_meta_factory;

			// die silently if the requested meta group is not registered
			if( ! Tribe_Meta_Factory::check_exists( $meta_id, $type ) )
				return false;

			$_tribe_meta_factory->{$type}[$meta_id]['label'] = $label;
		}
	}
}

if( !function_exists('tribe_get_the_event_meta')) {
	function tribe_get_the_event_meta(){
		$html = '';
		foreach( Tribe_Meta_Factory::get_order() as $meta_groups ){
			foreach( $meta_groups as $meta_group_id ){
				$html .= tribe_get_meta_group( $meta_group_id, true );
			}
		}

		return apply_filters('tribe_get_the_event_meta', $html);
	}
}

/**
 *  Simple diplay of meta group tag
 *
 * @uses tribe_get_meta_group()
 * @param string $meta_group_id
 * @return echo tribe_get_meta_group( $meta_group_id )
 */
if( !function_exists('tribe_display_the_event_meta')){
	function tribe_display_the_event_meta(){
		echo apply_filters('tribe_display_the_event_meta', tribe_get_the_event_meta());
	}
}

/**
 *  Simple diplay of meta group tag
 *
 * @uses tribe_get_meta_group()
 * @param string $meta_group_id
 * @return echo tribe_get_meta_group( $meta_group_id )
 */
if( !function_exists('tribe_display_meta_group')){
	function tribe_display_meta_group( $meta_group_id ){
		echo apply_filters('tribe_display_meta_group', tribe_get_meta_group( $meta_group_id ));
	}
}

/**
 *  Simple diplay of meta tag
 *
 * @uses tribe_get_meta()
 * @param string $meta_id
 * @return echo tribe_get_meta( $meta_id )
 */
if ( !function_exists( 'tribe_display_meta' ) ) {
	function tribe_display_meta( $meta_id ) {
		echo apply_filters('tribe_display_meta', tribe_get_meta( $meta_id ));
	}
}