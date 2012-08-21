<?php

	class TribeEventsTicketsMetabox {


		/**
		 * @static
		 * @param $post_id
		 */
		public static function do_meta_box( $post_id ) {
			add_meta_box( 'tribetickets', 'Tickets', array( 'TribeEventsTicketsMetabox',
			                                                'do_modules_metaboxes' ), TribeEvents::POSTTYPE, 'normal', 'high' );

		}

		/**
		 * @static
		 * @param $post_id
		 */
		public static function do_modules_metaboxes(  $post_id ) {
			$modules = apply_filters( 'tribe_events_tickets_modules', NULL );
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

					if ( class_exists( 'TribeSpinJS' ) ) {
						TribeSpinJS::load();
					}

				}
			}
		}


	}

	if ( is_admin() ) {
		add_action( 'add_meta_boxes', array( 'TribeEventsTicketsMetabox',
		                                     'do_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( 'TribeEventsTicketsMetabox',
		                                            'add_admin_scripts' ) );
	}