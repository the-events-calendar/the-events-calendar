<?php
/**
 * Bootstrap Events Templating system, which by default will hook into
 * the WordPress normal template workflow to allow the injection the Events
 * archive.
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use WP_Query;
use Tribe__Notices;

class Template_Bootstrap {
	/**
	 * Disables the Views V1 implementation of a Template Hijack
	 *
	 * @todo   use a better method to remove Views V1 from been initialized
	 *
	 * @since  4.9.2
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
	 * @since  4.9.2
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
	 * @since  4.9.2
	 *
	 * @return object
	 */
	public function get_template_object() {
		$setting = $this->get_template_setting();

		return $setting === 'page'
			? tribe( Template\Page::class )
			: tribe( Template\Event::class );
	}

	/**
	 * Fetches the HTML for the Single Event page using the legacy view system
	 *
	 * @since  4.9.4
	 *
	 * @return string
	 */
	protected function get_v1_single_event_html() {
		if ( ! tribe_is_showing_all() && tribe_is_past_event() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), tribe_get_event_label_singular_lowercase() ) );
		}

		ob_start();
		echo '<main id="tribe-events-pg-template" class="tribe-events-pg-template">';
		tribe_events_before_html();
		tribe_get_view( 'single-event' );
		tribe_events_after_html();
		echo '</main>';

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Gets the View HTML
	 *
	 * @todo Stop handling kitchen sink template here.
	 *
	 * @since  4.9.2
	 *
	 * @return string
	 */
	public function get_view_html() {
		$query     = tribe_get_global_query_object();
		$context   = tribe_context();
		$view_slug = $context->get( 'view' );

		if (
			'single-event' === $view_slug
			&& ! tribe_is_showing_all()
			&& ! \Tribe__Templates::is_embed()
		) {
			$html = $this->get_v1_single_event_html();
		} elseif ( isset( $query->query_vars['tribe_events_views_kitchen_sink'] ) ) {
			$context = [
				'query' => $query,
			];

			/**
			 * @todo  Replace with actual code for view and move this to correct kitchen sink
			 */
			$template = Arr::get( $context['query']->query_vars, 'tribe_events_views_kitchen_sink', 'page' );
			if ( ! in_array( $template, tribe( Kitchen_Sink::class )->get_available_pages() ) ) {
				$template = 'page';
			}

			$html = tribe( Kitchen_Sink::class )->template( $template, $context, false );
		} else {
			$html = View::make( $view_slug, $context )->get_html();
		}

		return $html;
	}

	/**
	 * Determines when we should bootstrap the template for The Events Calendar
	 *
	 * @since  4.9.2
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
	 * @since 4.9.2
	 *
	 * @param string $template The template located by WordPress.
	 *
	 * @return string Path to the File that initalizes the template
	 */
	public function filter_template_include( $template ) {

		// Determine if we should load bootstrap or bail.
		if ( ! $this->should_load() ) {
			return $template;
		}

		return $this->get_template_object()->get_path();
	}
}