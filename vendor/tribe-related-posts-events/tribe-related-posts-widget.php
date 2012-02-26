<?php
/**
* Related event widget
*/
// Don't load directly
if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if( !class_exists( 'TribeRelatedPostsWidget' ) ) {
	class TribeRelatedPostsWidget extends WP_Widget {
		
		/**
		 * Class instantiation function
		 *
		 * @since 1.1
		 * @author Paul Hughes
		 * @return void
		 */
		function TribeRelatedPostsWidget() {
			// Widget settings.
			$widget_ops = array( 'classname' => 'tribe_related_posts_widget', 'description' => __( 'Displays posts related to the post.', 'tribe-events-calendar-pro' ) );
			// Create the widget.
			$this->WP_Widget( 'tribe_related-posts-widget', __( 'Related Posts', 'tribe-events-calendar-pro' ), $widget_ops );
		}

		/**
		 * Main widget function.
		 *
		 * @since 1.1
		 * @author Paul Hughes
		 * @param array $args the widget arguments.
		 * @param array $instance the widget instance variables.
		 * @return void.
		 */
		function widget($args, $instance) {
			extract($args);
			echo $before_widget;
			$title = $before_title.apply_filters( 'widget_title', $instance['title'] ).$after_title;
			tribe_related_posts( false, $instance['count'], false, $instance['only_display_related'], $instance['thumbnails'], $instance['post_type'] );
			echo $after_widget;
		}

		/**
		 * Include the file for the administration view of the widget.
		 *
		 * @since 1.1
		 * @author Paul Hughes
		 * @param array $instance the widget instance variables.
		 * @return void
		 */
		function form( $instance ) {
			$defaults = array(
				'title' => '',
				'count' => 3,
				'thumbnails' => false,
				'only_display_related' => false,
				'post_type' => 'post',
			);
			$post_types_args = array( 'public' => true );
			$post_types = get_post_types( $post_types_args );
			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p><label for="<?php echo $this->get_field_id( 'title '); ?>"><?php _e( 'Title:','tribe-events-calendar-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></p>
			<p><label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:','tribe-events-calendar-pro' );?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" >
			<?php for ($i=1; $i<=5; $i++)
				{ ?>
				<option <?php selected( $instance['count'], $i ); ?>> <?php echo $i;?> </option>
			<?php } ?>
			</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'thumbnails' ); ?>"><?php _e( 'Display Thumbnails?','tribe-events-calendar-pro' ); ?></label>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['thumbnails'], true ); ?> id="<?php echo $this->get_field_id( 'thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'thumbnails' ); ?>" />
			</p>
			<p><label for="<?php echo $this->get_field_id( 'only_display_related' ); ?>"><?php _e( 'Only Display Related Posts?','tribe-events-calendar-pro' ); ?></label>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['only_display_related'], true ); ?> id="<?php echo $this->get_field_id( 'only_display_related' ); ?>" name="<?php echo $this->get_field_name( 'only_display_related' ); ?>" />
			</p>
			<p><label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:','tribe-events-calendar-pro' );?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" value="<?php echo $instance['post_type']; ?>" >
			<?php foreach( $post_types as $post_type )
				{ ?>
				<option <?php selected( $instance['post_type'], $post_type ); ?>> <?php echo $post_type;?> </option>
			<?php } ?>
			</select>
			</p>
		<?php }

		/**
		 * Function allowing updating of widget information.
		 *
		 * @since 1.1
		 * @author Paul Hughes
		 * @param array $new_instance new instance variables.
		 * @param array $old_instance old instance variables.
		 * @return array $instance the returned new instance variable values.
		 */
		function update( $new_instance, $old_instance ) {
			$instance = parent::update( $new_instance, $old_instance );

			$instance['title'] = $new_instance['title'];
			$instance['count'] = $new_instance['count'];
			$instance['thumbnails'] = ( isset( $new_instance['thumbnails'] ) ? true : false );
			$instance['only_display_related'] = ( isset( $new_instance['only_display_related'] ) ? true : false );
			$instance['post_type'] = $new_instance['post_type'];

			return $instance;
		}

	}

	// Load the widget with the 'widgets_init' action.
	add_action( 'widgets_init', 'tribe_related_posts_register_widget', 100 );

	/**
	 * Register the widget with wordpress install.
	 *
	 * @since 1.1
	 * @author Paul Hughes
	 * @return void
	 */
	function tribe_related_posts_register_widget() {
		register_widget ( 'TribeRelatedPostsWidget' );
	}
}