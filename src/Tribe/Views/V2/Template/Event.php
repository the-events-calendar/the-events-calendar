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

use Tribe\Events\Collections\Lazy_Post_Collection;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe\Events\Views\V2\View;
use Tribe\Utils\Lazy_String;
use Tribe\Utils\Post_Thumbnail;

class Event {
	/**
	 * @var boolean Whether or not we are currently filtering out content due to password protection
	 */
	protected $managing_sensitive_info = false;

	/**
	 * The current template bootstrap instance.
	 *
	 * @since 5.0.0
	 *
	 * @var Template_Bootstrap
	 */
	protected $template_bootstrap;

	/**
	 * Event constructor.
	 *
	 * @since 5.0.0
	 *
	 * @param Template_Bootstrap $template_bootstrap The current template bootstrap instance.
	 */
	public function __construct( Template_Bootstrap $template_bootstrap ) {
		$this->template_bootstrap = $template_bootstrap;
	}

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
		$path      = $fake_view->get_template()->get_template_file( 'default-template' );

		return $path;
	}

	/**
	 * Add/remove filters to hide/show sensitive event info on password protected posts
	 *
	 * @since 5.0.0
	 *
	 * @param int|\WP_Post $post The post ID or object to filter.
	 **/
	public function manage_sensitive_info( $post ) {
		$password_required = post_password_required( $post );

		if ( ! $this->managing_sensitive_info && $password_required ) {
			add_filter( 'tribe_events_event_schedule_details', '__return_empty_string' );
			add_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
			add_filter( 'tribe_event_meta_venue_name', '__return_empty_string' );
			add_filter( 'tribe_event_meta_venue_address', '__return_empty_string' );
			add_filter( 'tribe_event_featured_image', '__return_empty_string' );
			add_filter( 'tribe_get_venue', '__return_empty_string' );
			add_filter( 'tribe_get_cost', '__return_empty_string' );

			if ( tribe_context()->doing_ajax() ) {
				add_filter( 'the_title', [ $this, 'filter_get_the_title' ], 10, 2 );
			}

			if ( $this->template_bootstrap->is_single_event() ) {
				add_filter( 'the_title', '__return_empty_string' );
				add_filter( 'tribe_get_template_part_templates', '__return_empty_array' );
			}

			$this->managing_sensitive_info = true;
		} elseif ( $this->managing_sensitive_info && ! $password_required ) {
			remove_filter( 'tribe_events_event_schedule_details', '__return_empty_string' );
			remove_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
			remove_filter( 'tribe_event_meta_venue_name', '__return_empty_string' );
			remove_filter( 'tribe_event_meta_venue_address', '__return_empty_string' );
			remove_filter( 'tribe_event_featured_image', '__return_empty_string' );
			remove_filter( 'tribe_get_venue', '__return_empty_string' );
			remove_filter( 'tribe_get_cost', '__return_empty_string' );

			if ( tribe_context()->doing_ajax() ) {
				remove_filter( 'the_title', [ $this, 'filter_get_the_title' ], 10 );
			}

			if ( $this->template_bootstrap->is_single_event() ) {
				remove_filter( 'the_title', '__return_empty_string' );
				remove_filter( 'tribe_get_template_part_templates', '__return_empty_array' );
			}

			$this->managing_sensitive_info = false;
		}
	}

	/**
	 * Filters the post title as WordPress does in `get_the_title` to apply the password-protected prefix in
	 * the context of AJAX requests.
	 *
	 * @since 5.0.0
	 *
	 * @param string      $title   The post title.
	 * @param int|\WP_Post $post_id The post ID, or object, to apply the filter for.
	 *
	 * @return string The filtered post title.
	 */
	public function filter_get_the_title( $title, $post_id = 0 ) {
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return $title;
		}

		if ( ! empty( $post->post_password ) ) {
			/* translators: %s: Protected post title. */
			$prepend = __( 'Protected: %s' );

			/**
			 * @see get_the_title() for the original filter documentation.
			 */
			$protected_title_format = apply_filters( 'protected_title_format', $prepend, $post );
			$title                  = sprintf( $protected_title_format, $title );
		}

		return $title;
	}

	/**
	 * Filters and modifies the event WP_Post object returned from the `tribe_get_event` function to hide some
	 * sensitive information if required.
	 *
	 * @since 5.0.0
	 *
	 * @param \WP_Post $event The event post object, decorated w/ properties added by the `tribe_get_event` function.
	 *
	 * @return \WP_Post The event post object, decorated w/ properties added by the `tribe_get_event` function, some of
	 *                  them updated to hide sensitive information, if required.
	 */
	public function filter_event_properties( \WP_Post $event ) {
		if ( post_password_required( $event ) ) {
			$props = [
				'start_date',
				'start_date_utc',
				'end_date',
				'end_date_utc',
				'cost',
				'recurring',
				'permalink_all',
			];

			foreach ( $props as $prop ) {
				$event->{$prop} = '';
			}

			foreach ( [ 'venues', 'organizers' ] as $lazy_collection ) {
				$event->{$lazy_collection} = new Lazy_Post_Collection( '__return_empty_array' );
			}

			foreach ( [ 'plain_schedule_details', 'schedule_details', 'excerpt' ] as $lazy_string ) {
				$event->{$lazy_string} = new Lazy_String( '__return_empty_string' );
			}

			$event->thumbnail = new Post_Thumbnail( - 1 );
		}

		return $event;
	}
}
