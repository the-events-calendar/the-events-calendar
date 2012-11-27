<?php

class TribeEventsTicketsMetabox {

	public static function do_meta_box( $post_id ) {
		$modules = apply_filters( 'tribe_events_tickets_modules', NULL );
		if ( empty( $modules ) )
			return;

		add_meta_box( 'tribetickets', __( 'Tickets', 'tribe-events-calendar' ), array( 'TribeEventsTicketsMetabox',
		                                                                               'do_modules_metaboxes' ), TribeEvents::POSTTYPE, 'normal', 'high' );
	}

	public static function do_modules_metaboxes( $post_id ) {

		$modules = apply_filters( 'tribe_events_tickets_modules', NULL );
		if ( empty( $modules ) )
			return;

		foreach ( $modules as $class => $title ) {
			if ( class_exists( $class ) ) {
				$obj = call_user_func( array( $class, 'get_instance' ) );
				$obj->do_meta_box( $post_id );
			}
		}
	}

	public static function add_admin_scripts( $hook ) {
		global $post;

		if ( ( $hook != 'post-new.php' && $hook != 'post.php' ) || TribeEvents::POSTTYPE != $post->post_type )
			return;

		wp_enqueue_script( 'events-tickets', plugins_url( 'resources/tickets.js', dirname( dirname( __FILE__ ) ) ) );
		wp_enqueue_style( 'events-tickets', plugins_url( 'resources/tickets.css', dirname( dirname( __FILE__ ) ) ) );


	}

}

add_action( 'add_meta_boxes', array( 'TribeEventsTicketsMetabox', 'do_meta_box' ) );
add_action( 'admin_enqueue_scripts', array( 'TribeEventsTicketsMetabox', 'add_admin_scripts' ) );
