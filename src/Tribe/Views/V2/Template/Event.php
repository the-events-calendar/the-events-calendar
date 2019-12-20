<?php
/**
 * Initializer for The Events Calendar for the template structure using Event
 *
 * Can be changed on Events > Settings > Display
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2\Template;

use Tribe__Events__Main;
use Tribe\Events\Views\V2\View;

class Event {
	/**
	 * @var boolean Whether or not we are currently filtering out content due to password protection
	 */
	protected $managing_sensitive_info = false;

	/**
	 * Determines the Path for the PHP file to be used as the main template
	 * For Page base template setting it will select from theme or child theme
	 *
	 * @todo  Integrate with Template + Context classes
	 *
	 * @since  4.9.2
	 *
	 * @return string
	 */
	public function get_path() {
		$fake_view = View::make( 'reflector' );
		$path = $fake_view->get_template()->get_template_file( 'index' );
		return $path;
	}

	/**
	 * Add/remove filters to hide/show sensitive event info on password protected posts
	 *
	 * @param WP_Post $post
	 *
	 **/
	public function manage_sensitive_info( $post ) {
		if ( post_password_required( $post ) ) {
			add_filter( 'tribe_events_event_schedule_details', '__return_empty_string' );
			add_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
			add_filter( 'tribe_event_meta_venue_name', '__return_empty_string' );
			add_filter( 'tribe_event_meta_venue_address', '__return_empty_string' );
			add_filter( 'tribe_event_featured_image', '__return_empty_string' );
			add_filter( 'tribe_get_venue', '__return_empty_string' );
			add_filter( 'tribe_get_cost', '__return_empty_string' );

			if ( is_singular( Tribe__Events__Main::POSTTYPE ) ) {
				add_filter( 'the_title', '__return_empty_string' );
				add_filter( 'tribe_get_template_part_templates', '__return_empty_array' );
			}

			$this->managing_sensitive_info = true;
		} elseif ( $this->managing_sensitive_info ) {
			remove_filter( 'tribe_events_event_schedule_details', '__return_empty_string' );
			remove_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
			remove_filter( 'tribe_event_meta_venue_name', '__return_empty_string' );
			remove_filter( 'tribe_event_meta_venue_address', '__return_empty_string' );
			remove_filter( 'tribe_event_featured_image', '__return_empty_string' );
			remove_filter( 'tribe_get_venue', '__return_empty_string' );
			remove_filter( 'tribe_get_cost', '__return_empty_string' );

			if ( is_singular( Tribe__Events__Main::POSTTYPE ) ) {
				remove_filter( 'the_title', '__return_empty_string' );
				remove_filter( 'tribe_get_template_part_templates', '__return_empty_array' );
			}

			$this->managing_sensitive_info = false;
		}
	}
}
