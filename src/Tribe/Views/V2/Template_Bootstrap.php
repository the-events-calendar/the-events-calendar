<?php
/**
 * Bootstrap Events Templating system, which by default will hook into
 * the WordPress normal template workflow to allow the injection the Events
 * archive.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;
use WP_Query;

class Template_Bootstrap {
	/**
	 * Hook the bootstraping into WordPress filter and actions
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );

		add_action( 'tribe_common_loaded', [ $this, 'disable_v1' ], 1 );
	}

	/**
	 * Disables the Views V1 implementation of a Template Hijack
	 *
	 * @todo   use a better method to remove Views V1 from been initialized
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function disable_v1() {
		remove_action( 'plugins_loaded', [ 'Tribe__Events__Templates', 'init' ] );
	}

	/**
	 * Determines with backwards compatibility in mind, which template user has selected
	 * on the Events > Settings page as their base Default template
	 *
	 * @since  TBD
	 *
	 * @return string Either 'event' or 'page' based templates
	 */
	public function get_template_setting() {
		$template = 'event';
		$default_value = 'default';
		$setting = tribe_get_option( 'tribeEventsTemplate', $default_value );

		if ( $default_value === $setting ) {
			$template = 'page';
		}

		return $template;
	}

	/**
	 * Based on the base template setting we fetch the respective object
	 * to handle the inclusion of the main file.
	 *
	 * @since  TBD
	 *
	 * @return object
	 */
	public function get_template_object() {
		$setting = $this->get_template_setting();

		return tribe( "events.views.v2.template.{$setting}" );
	}

	/**
	 * Determines when we should bootstrap the template for The Events Calendar
	 *
	 * @since  TBD
	 *
	 * @param  WP_Query $query Which WP_Query object we are going to load on
	 *
	 * @return boolean
	 */
	public function should_load( $query = null ) {
		if ( ! $query instanceof WP_Query ) {
			$query = tribe_get_global_query_object();
		}

		if ( ! $query instanceof WP_Query ) {
			return false;
		}

		/**
		 * Bail if we are not dealing with our Post Type
		 *
		 * @todo  needs support for Venues and Template
		 */
		if ( ! in_array( TEC::POSTTYPE, (array) $query->get( 'post_type' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Filters the `template_include` filter to return the Views router template if required..
	 *
	 * @since TBD
	 *
	 * @param string $template The template located by WordPress.
	 *
	 * @return string Path to the File that initalizes the template
	 */
	public function filter_template_include( $template ) {

		// Determine if we should lood bootstrap or bail
		if ( ! $this->should_load() ) {
			return $template;
		}

		return $this->get_template_object()->get_path();
	}
}