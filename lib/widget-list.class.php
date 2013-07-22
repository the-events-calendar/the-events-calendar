<?php
/**
 * Event List Widget
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsListWidget' ) ) {
	class TribeEventsListWidget extends WP_Widget {

        /**
         * The main widget method.
         *
         * @return void
         */
        function TribeEventsListWidget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'tribe-events-list-widget', 'description' => __( 'A widget that displays upcoming events.', 'tribe-events-calendar' ) );

			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'tribe-events-list-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'tribe-events-list-widget', __( 'Events List', 'tribe-events-calendar' ), $widget_ops, $control_ops );
		}

        /**
         * The main widget output function.
         *
         * @param array $args
         * @param array $instance
         * @return string The widget output (html).
         */
        function widget( $args, $instance ) {
			return $this->widget_output( $args, $instance );
		}

        /**
         * The main widget output function (called by the class's widget() function).
         *
         * @param array $args
         * @param array $instance
         * @param string $template_name The template name.
         * @param string $subfolder The subfolder where the template can be found.
         * @param string $namespace The namespace for the widget template stuff.
         * @param string $pluginPath The pluginpath so we can locate the template stuff.
         */
        function widget_output( $args, $instance, $template_name='list-widget', $subfolder = 'widgets', $namespace = '/', $pluginPath = '' ) {
			global $wp_query, $tribe_ecp, $post;
			extract( $args, EXTR_SKIP );
			// The view expects all these $instance variables, which may not be set without pro
			$instance = wp_parse_args($instance, array(
				'limit' => 5,
				'title' => '',
			));
			extract( $instance, EXTR_SKIP );

			// temporarily unset the tribe bar params so they don't apply
			$hold_tribe_bar_args =  array();
			foreach ( $_REQUEST as $key => $value ) {
				if ( $value && strpos( $key, 'tribe-bar-' ) === 0 ) {
					$hold_tribe_bar_args[$key] = $value;
					unset( $_REQUEST[$key] );
				}
			}

			// extracting $instance provides $title, $limit
			$title = apply_filters('widget_title', $title );
			if ( ! isset( $category ) || $category === '-1' ) {
				$category = 0;
			}

			if ( tribe_get_option( 'viewOption' ) == 'upcoming' ) {
				$event_url = tribe_get_listview_link( $category );
			} else {
				$event_url = tribe_get_gridview_link( $category );
			}

			if ( function_exists( 'tribe_get_events' ) ) {

				$args = array(
					'eventDisplay'   => 'upcoming',
					'posts_per_page' => $limit,
				);

				if ( ! empty( $category ) ) {
					$args['tax_query'] = array(
						array(
							'taxonomy'         => TribeEvents::TAXONOMY,
							'terms'            => $category,
							'field'            => 'ID',
							'include_children' => false
						)
					);
				}

				$posts    = tribe_get_events( $args );
			}

			// if no posts, and the don't show if no posts checked, let's bail
			if ( ! $posts && $no_upcoming_events ) {
				return;
			}

			/* Before widget (defined by themes). */
			echo $before_widget;

			/* Title of widget (before and after defined by themes). */
			echo ( $title ) ? $before_title . $title . $after_title : '';

			if ( $posts ) {
				/* Display list of events. */
				echo '<ol class="hfeed vcalendar">';
				foreach( $posts as $post ) :
					setup_postdata( $post );
					tribe_get_template_part( 'widgets/list-widget' );
				endforeach;
				echo "</ol><!-- .hfeed -->";

				/* Display link to all events */
				echo '<p class="tribe-events-widget-link"><a href="' . $event_url . '" rel="bookmark">' . __('View All Events', 'tribe-events-calendar' ) . '</a></p>';
			}
			else {
				echo '<p>' . __('There are no upcoming events at this time.', 'tribe-events-calendar') . '</p>';
			}

			/* After widget (defined by themes). */
			echo $after_widget;
			wp_reset_query();

			// reinstate the tribe bar params
			if ( ! empty( $hold_tribe_bar_args ) ) {
				foreach ( $hold_tribe_bar_args as $key => $value ) {
					$_REQUEST[$key] = $value;
				}
			}
			
		}

        /**
         * The function for saving widget updates in the admin section.
         *
         * @param array $new_instance
         * @param array $old_instance
         * @return array The new widget settings.
         */
        function update( $new_instance, $old_instance ) {
				$instance = $old_instance;

				/* Strip tags (if needed) and update the widget settings. */
				$instance['title'] = strip_tags( $new_instance['title'] );
				$instance['limit'] = $new_instance['limit'];
				$instance['no_upcoming_events'] = $new_instance['no_upcoming_events'];

				return $instance;
		}

        /**
         * Output the admin form for the widget.
         *
         * @param array $instance
         * @return string The output for the admin widget form.
         */
        function form( $instance ) {
			/* Set up default widget settings. */
			$defaults = array( 'title' => __( 'Upcoming Events', 'tribe-events-calendar' ), 'limit' => '5', 'no_upcoming_events' => false);
			$instance = wp_parse_args( (array) $instance, $defaults );
			$tribe_ecp = TribeEvents::instance();
			include( $tribe_ecp->pluginPath . 'admin-views/widget-admin-list.php' );
		}
	}

	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'events_list_load_widgets', 90 );

	/**
     * Function that registers widget.
     *
     * @return void
	 */
	function events_list_load_widgets() {
		register_widget( 'TribeEventsListWidget' );
	}
}
