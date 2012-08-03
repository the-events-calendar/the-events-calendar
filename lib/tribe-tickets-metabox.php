<?php

	class TribeEventsTicketsMetabox {


		/**
		 * @static
		 * @param $post_id
		 */
		public static function do_meta_box( $post_id ) {
			$modules = apply_filters( 'tribe_events_tickets_modules', NULL );

			if ( $modules ) {
				self::do_modules_metaboxes( $modules, $post_id );
			}
		}

		/**
		 * @static
		 * @param $modules
		 * @param $post_id
		 */
		private static function do_modules_metaboxes( $modules, $post_id ) {
			foreach ( $modules as $class => $title ) {
				if ( class_exists( $class ) ) {
					$obj = call_user_func( array( $class,
					                              'get_instance' ) );
					$obj->do_meta_box( $post_id );

				}
			}
		}


		/**
		 * @static
		 * @param $hook
		 */
		public static function add_admin_scripts( $hook ) {
			global $post;

			if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
				if ( TribeEvents::POSTTYPE === $post->post_type ) {
					wp_register_script( 'events-tickets', plugins_url( 'resources/tickets.js', dirname( __FILE__ ) ) );
					wp_enqueue_script( 'events-tickets' );

					wp_register_style( 'events-tickets', plugins_url( 'resources/tickets.css', dirname( __FILE__ ) ) );
					wp_enqueue_style( 'events-tickets' );


				}
			}
		}


	}

	add_action( 'tribe_events_details_table_bottom', array( 'TribeEventsTicketsMetabox', 'do_meta_box' ) );
	add_action( 'admin_enqueue_scripts', array( 'TribeEventsTicketsMetabox', 'add_admin_scripts' ) );
