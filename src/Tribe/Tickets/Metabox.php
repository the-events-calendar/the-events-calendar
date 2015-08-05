<?php

/**
 *    Class in charge of registering and displaying
 *  the tickets metabox in the event edit screen.
 *  Metabox will only be added if there's a
 *     Tickets Pro provider (child of TribeTickets)
 *     available.
 */
class Tribe__Events__Tickets__Metabox {

	/**
	 * Registers the tickets metabox if there's at least
	 * one Tribe Tickets module (provider) enabled
	 * @static
	 *
	 * @param $post_id
	 */
	public static function maybe_add_meta_box( $post_id ) {
		$modules = apply_filters( 'tribe_events_tickets_modules', null );
		if ( empty( $modules ) ) {
			return;
		}

		add_meta_box(
			'tribetickets', __( 'Tickets', 'tribe-events-calendar' ), array(
			'Tribe__Events__Tickets__Metabox',
				'do_modules_metaboxes'
			), Tribe__Events__Main::POSTTYPE, 'normal', 'high'
		);
	}

	/**
	 * Loads the content of the tickets metabox if there's at
	 * least one Tribe Tickets module (provider) enabled
	 * @static
	 *
	 * @param $post_id
	 */
	public static function do_modules_metaboxes( $post_id ) {

		$modules = apply_filters( 'tribe_events_tickets_modules', null );
		if ( empty( $modules ) ) {
			return;
		}

		Tribe__Events__Tickets__Tickets_Pro::instance()->do_meta_box( $post_id );
	}

	/**
	 * Enqueue the tickets metabox JS and CSS
	 * @static
	 *
	 * @param $hook
	 */
	public static function add_admin_scripts( $hook ) {
		global $post;

		$modules = apply_filters( 'tribe_events_tickets_modules', null );

		/* Only load the resources in the event edit screen, and if there's a provider available */
		if ( ( $hook != 'post-new.php' && $hook != 'post.php' ) || Tribe__Events__Main::POSTTYPE != $post->post_type || empty( $modules ) ) {
			return;
		}


		wp_enqueue_style  ( 'events-tickets', tribe_events_resource_url('tickets.css') , array(), apply_filters( 'tribe_events_css_version', Tribe__Events__Main::VERSION ) );
		wp_enqueue_script ( 'events-tickets', tribe_events_resource_url('tickets.js'), array(), apply_filters( 'tribe_events_js_version', Tribe__Events__Main::VERSION ) );

		$upload_header_data = array(
			'title'  => __( 'Ticket header image', 'tribe-events-calendar' ),
			'button' => __( 'Set as ticket header', 'tribe-events-calendar' )
		);
		wp_localize_script( 'events-tickets', 'HeaderImageData', $upload_header_data );


		$nonces = array(
			'add_ticket_nonce'    => wp_create_nonce( 'add_ticket_nonce' ),
			'edit_ticket_nonce'   => wp_create_nonce( 'edit_ticket_nonce' ),
			'remove_ticket_nonce' => wp_create_nonce( 'remove_ticket_nonce' )
		);

		wp_localize_script( 'events-tickets', 'TribeTickets', $nonces );


	}
}

