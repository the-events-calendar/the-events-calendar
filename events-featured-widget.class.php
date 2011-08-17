<?php
if( !class_exists( 'Events_Featured_Widget') ) {
	/**
	* Featured event
	*/
	class Events_Featured_Widget extends WP_Widget {
		
			public $pluginDomain = 'the-events-calendar';

			function Events_Featured_Widget() {
				$widget_ops = array('classname' => 'Events_Featured_Widget', 'description' => __( 'Your next upcoming event') );
				$this->WP_Widget('featured_event', __('Featured Event'), $widget_ops);
			}


			function widget( $args, $instance ) {
				global $wp_query, $tribe_ecp, $post;
				$old_post = $post;
				extract( $args, EXTR_SKIP );
				extract( $instance, EXTR_SKIP );
				// extracting $instance provides $title, $limit, $no_upcoming_events, $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone , $cost
				$title = apply_filters('widget_title', $title );
				
				if ( sp_get_option('viewOption') == 'upcoming') {
					$event_url = sp_get_listview_link();
				} else {
					$event_url = sp_get_gridview_link();
				}

				if( function_exists( 'sp_get_events' ) ) {
					$old_display = $wp_query->get('eventDisplay');
					$wp_query->set('eventDisplay', 'upcoming');
					$posts = sp_get_events( 'numResults=1&eventCat=' . $category );
					$template = $tribe_ecp->getTemplateHierarchy('widget-featured-display');
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
					foreach( $posts as $post ) : 
						setup_postdata($post);
						include $template;
					endforeach;

					$wp_query->set('eventDisplay', $old_display);
				} 
				else {
					_e('There are no upcoming events at this time.', $this->pluginDomain);
				}

				/* After widget (defined by themes). */
				echo $after_widget;
				$post = $old_post;
			}	
		
			function update( $new_instance, $old_instance ) {
				$instance = $old_instance;
				$instance['title'] = strip_tags($new_instance['title']);
				$instance['category'] = $new_instance['category'];

				return $instance;
			}

			function form( $instance ) {
				$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
				$title = strip_tags($instance['title']);
		?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
				<p>
					<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Category:',$this->pluginDomain);?>
					<?php 

						echo wp_dropdown_categories( array(
							'show_option_none' => 'All Events',
							'hide_empty' => 0,
							'echo' => 0,
							'name' => $this->get_field_name( 'category' ),
							'id' => $this->get_field_id( 'category' ),
							'taxonomy' => 'sp_events_cat',
							'selected' => $instance['category']
						));
					?>
				</p>
		<?php
			}
		
		}
	
		/* Add function to the widgets_ hook. */
		// hook is commented out until development is finished, allows WP's default calendar widget to work
		add_action( 'widgets_init', 'events_calendar_load_featured_widget',100);
		//add_action( 'widgets_init', 'get_calendar_custom' );
	
		//function get_calendar_custom(){echo "hi";}

		/* Function that registers widget. */
		function events_calendar_load_featured_widget() {
			global $pluginDomain;
			register_widget( 'Events_Featured_Widget' );
			// load text domain after class registration
			load_plugin_textdomain( 'the-events-calendar', false, basename(dirname(__FILE__)) . '/lang/');
		}
}