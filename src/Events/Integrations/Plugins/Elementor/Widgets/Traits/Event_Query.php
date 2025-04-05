<?php
/**
 * Provides methods for fetching events via the Event_Query control group.
 *
 * @since   5.4.0
 *
 * @package TEC\Events\Integrations\Elementor\Widgets\Traits
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Traits;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Controls\Groups;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use Tribe__Utils__Array as Arr;
use Tribe__Events__Main as TEC;

/**
 * Trait Categories
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Widgets\Traits
 */
trait Event_Query {
	/**
	 * @var string Event Query control prefix.
	 */
	protected $event_query_control_prefix = 'event_query';

	/**
	 * @var bool Whether or not we should default the repository to the current date/time.
	 */
	protected $default_repository_to_current_date = true;

	/**
	 * Provides a "trimmed" slug for usage in classes and such (removes the "event_" prefix)
	 * and converts all underscores to dashes.
	 *
	 * This is here for the widgets that use this trait but do not extend Abstract_Widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function trim_slug(): string {
		return str_replace( [ 'event_', '_' ], [ '', '-' ], static::get_slug() );
	}

	/**
	 * Method for adding the event_query section in the widget controls.
	 *
	 * @since 5.4.0
	 */
	public function add_event_query_section() {
		$this->start_controls_section(
			'event_query_section',
			[
				'label' => __( 'Event Query', 'the-events-calendar' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->event_query_control_prefix = $this::trim_slug() . '_event_query';

		$this->add_group_control( Groups\Event_Query::get_type(), [ 'name' => $this->event_query_control_prefix ] );

		$this->end_controls_section();
	}

	/**
	 * Translates prefixed event_query settings to simpler values.
	 *
	 * @since 5.4.0
	 *
	 * @param array<string> $settings Widget settings.
	 *
	 * @return array
	 */
	public function get_event_query_settings( $settings = [] ) {
		if ( empty( $settings ) ) {
			$settings = $this->get_settings_for_display();
		}

		$query_settings = [];
		$prefix_length  = strlen( $this->event_query_control_prefix . '_' );

		foreach ( $settings as $key => $value ) {
			if ( 0 !== strpos( $key, $this->event_query_control_prefix ) ) {
				continue;
			}

			$query_settings[ substr( $key, $prefix_length ) ] = $value;
		}

		return $query_settings;
	}

	/**
	 * Gets the event repository based on widget settings.
	 *
	 * @since 5.4.0
	 *
	 * @param array<string> $settings Widget settings.
	 *
	 * @return \Tribe__Events__Repositories__Event
	 */
	public function build_event_repository( $settings = [] ) {
		$repository = tribe_events();

		/**
		 * Handle meta
		 */

		if ( Arr::get( $settings, 'search' ) ) {
			$repository->search( $settings['search'] );
		}

		if ( Arr::get( $settings, 'category' ) ) {
			$repository->where( 'category', $settings['category'] );
		}

		if ( Arr::get( $settings, 'post_tag' ) ) {
			$repository->where( 'post_tag', $settings['post_tag'] );
		}

		$featured = Arr::get( $settings, 'featured' );

		if ( $featured && 'include' !== $featured ) {
			$repository->where( 'featured', 'only' == $featured );
		}

		$all_day = Arr::get( $settings, 'all_day' );
		if ( $all_day && 'include' !== $all_day ) {
			$repository->where( 'all_day', 'only' == $all_day );
		}

		$multi_day = Arr::get( $settings, 'multiday' );
		if ( $multi_day && 'include' !== $multi_day ) {
			$repository->where( 'multiday', 'only' == $multi_day );
		}

		$series = Arr::get( $settings, 'series' );
		if ( $series && 'include' !== $series ) {
			$repository->where( 'series', 'only' == $series );

			if ( 'only' === $series ) {
				$repository->where( 'meta_not_equals', '_EventRecurrence', 'a:3:{s:5:"rules";a:0:{}s:10:"exclusions";a:0:{}s:11:"description";N;}' );
			}
		}

		$has_geoloc = Arr::get( $settings, 'has_geoloc' );
		if ( $has_geoloc && 'include' !== $has_geoloc ) {
			$repository->where( 'has_geoloc', 'only' == $has_geoloc );
		}

		/**
		 * If the following do not manipulate the repository,
		 * we need to establish a date-based default.
		 */

		if ( 'current' === Arr::get( $settings, 'id_selection' ) ) {
			global $post;

			if ( isset( $post->ID ) ) {
				$repository->in( absint( $post->ID ) );
				$this->default_repository_to_current_date = false;
			}
		}

		if ( Arr::get( $settings, 'id' ) ) {
			$repository->in( absint( $settings['id'] ) );
			$this->default_repository_to_current_date = false;
		}

		if ( Arr::get( $settings, 'slug' ) ) {
			$repository->where( 'name', $settings['slug'] );
			$this->default_repository_to_current_date = false;
		}

		$repository = $this->setup_repository_dates( $repository, $settings, 'start' );
		$repository = $this->setup_repository_dates( $repository, $settings, 'end' );

		if ( $this->default_repository_to_current_date ) {
			$repository = $repository->where(
				'starts_on_or_after',
				\Tribe__Date_Utils::build_date_object( time() )->format( \Tribe__Date_Utils::DBDATEFORMAT )
			);
		}

		return $repository;
	}

	/**
	 * Takes widget settings and adds where conditionals for dates.
	 *
	 * @since 5.4.0
	 *
	 * @param \Tribe__Events__Repositories__Event $repository Event Repository.
	 * @param array<string>                       $settings   Widget settings.
	 * @param string                              $which      Which date type to analyze. 'start' or 'end'.
	 *
	 * @return \Tribe__Events__Repositories__Event
	 */
	protected function setup_repository_dates( $repository, $settings = [], $which = 'start' ) {
		$when = Arr::get( $settings, "{$which}s_when" );

		if ( ! $when ) {
			return $repository;
		}

		$method = Arr::get( $settings, "{$which}s_method" );
		$suffix = 'custom' === $method ? '_custom' : '';

		$date       = Arr::get( $settings, "{$which}_date{$suffix}" );
		$date_start = Arr::get( $settings, "{$which}_date_start{$suffix}" );
		$date_end   = Arr::get( $settings, "{$which}_date_end{$suffix}" );

		switch ( $when ) {
			case 'on':
				if ( $date ) {
					$date       = \Tribe__Date_Utils::build_date_object( $date )->format( \Tribe__Date_Utils::DBDATEFORMAT );
					$date_start = $date . ' 00:00:00';
					$date_end   = $date . ' 23:59:59';
					$repository->where( "{$which}s_between", $date_start, $date_end );
					$this->default_repository_to_current_date = false;
				}
				break;
			case 'before':
				if ( $date ) {
					$repository->where( "{$which}s_before", $date );
					$this->default_repository_to_current_date = false;
				}
				break;
			case 'after':
				if ( $date ) {
					$repository->where( "{$which}s_after", $date );
					$this->default_repository_to_current_date = false;
				}
				break;
			case 'on_or_after':
				if ( $date ) {
					$repository->where( "{$which}s_on_or_after", $date );
					$this->default_repository_to_current_date = false;
				}
				break;
			case 'on_or_before':
				if ( $date ) {
					$repository->where( "{$which}s_on_or_before", $date );
					$this->default_repository_to_current_date = false;
				}
				break;
			case 'between':
				if ( $date_start && $date_end ) {
					$repository->where( "{$which}s_between", $date_start, $date_end );
					$this->default_repository_to_current_date = false;
				}
				break;
		}

		return $repository;
	}

	/**
	 * Sets the id field from the repository results if it isn't already set.
	 *
	 * @since 5.4.0
	 *
	 * @param array $settings Widget settings.
	 *
	 * @return array
	 */
	public function set_id_from_repository_if_unset( $settings = [] ) {
		if ( Arr::get( $settings, 'id' ) ) {
			return $settings;
		}

		$repository = $this->build_event_repository( $settings );
		$post       = $repository->first();

		if ( ! empty( $post ) ) {
			$settings['id'] = $post->ID;
		}

		return $settings;
	}

	/**
	 * Get the ID of the event/post the widget is used in.
	 *
	 * @since 6.4.0
	 *
	 * @return ?int The ID of the current item (parent post) the widget is in. Null if not found.
	 */
	public function get_id_from_repository(): ?int {
		$settings   = $this->get_event_query_settings();
		$repository = $this->build_event_repository( $settings );
		$post       = $repository->first();

		if ( ! empty( $post ) ) {
			return $post->ID;
		}

		return null;
	}

	/**
	 * An internal, filterable function to get the ID of the event/post the widget is used in.
	 *
	 * @since 6.4.0
	 *
	 * @return ?int The ID of the current item (parent post) the widget is in. False if not found.
	 */
	protected function event_id(): ?int {
		$event_id = null;

		if ( ! $this->is_type_instance() ) {
			$setting_id    = $this->event_query_control_prefix . '_id_selection';
			$query_setting = Arr::get( $this->get_settings_for_display(), $setting_id );

			if ( ! empty( $query_setting ) && 'current' !== $query_setting ) {
				$event_id = $this->get_id_from_repository();
			}
		}

		if ( empty( $event_id ) ) {
			$event_id = get_the_ID();
		}

		if ( empty( $event_id ) ) {
			$event_id = tribe_get_request_var( 'post', false );
		}

		if ( empty( $event_id ) ) {
			$event_id = tribe_get_request_var( 'preview_id', false );
		}

		$slug = self::get_slug();

		// Initially check if the global post is an event.
		if (
			is_admin() &&
			get_post_type( $event_id ) !== TEC::POSTTYPE &&
			'elementor' === tribe_get_request_var( 'action' )
		) {
			$event_id = (int) tribe_get_request_var( 'post', false );
		}

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since 6.4.0
		 *
		 * @param int             $event_id The event ID.
		 * @param Abstract_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( 'tec_events_elementor_widget_event_id', (int) $event_id, $this );

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since 6.4.0
		 *
		 * @param int             $event_id The event ID.
		 * @param Abstract_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( "tec_events_elementor_widget_{$slug}_event_id", (int) $event_id, $this );

		if ( get_post_type( $event_id ) !== TEC::POSTTYPE ) {
			return null;
		}

		return $event_id > 0 ? $event_id : null;
	}
}
