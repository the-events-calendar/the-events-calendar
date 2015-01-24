<?php


	class Tribe__Events__Asset__Events_Css extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			// check if responsive should be killed
			if ( apply_filters( 'tribe_events_kill_responsive', false ) ) {
				add_filter( 'tribe_events_mobile_breakpoint', '__return_zero' );
			}

			$stylesheets = array();
			$mobile_break = tribe_get_mobile_breakpoint();

			// Get the selected style option
			$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

			// from `some-style-option`
			// to `Tribe__Events__Asset__Events_Css_Some_Style_Option`
			$child_class_name = $this->get_child_class_name( $style_option );

			/**
			 * @var Tribe__Events__Asset__Abstract_Events_Css
			 */
			$child_class_instance = new $child_class_name;

			// `$stylesheets` passed by reference
			$child_class_instance->handle( $stylesheets, $mobile_break );

			// put override css at the end of the array
			$stylesheets['tribe-events-calendar-override-style'] = 'tribe-events/tribe-events.css';

			// do the enqueues
			foreach ( $stylesheets as $name => $css_file ) {
				if ( $name == 'tribe-events-calendar-override-style' ) {
					$user_stylesheet_url = Tribe__Events__Templates::locate_stylesheet( 'tribe-events/tribe-events.css' );
					if ( $user_stylesheet_url ) {
						wp_enqueue_style( $name, $user_stylesheet_url );
					}
				} else {

					// get full URL
					$url = tribe_events_resource_url( $css_file );

					// get the minified file
					$url = Tribe__Events__Template_Factory::getMinFile( $url, true );

					// apply filters
					$url = apply_filters( 'tribe_events_stylesheet_url', $url, $name );

					// set the $media attribute
					if ( $name == 'tribe-events-calendar-mobile-style' || $name == 'tribe-events-calendar-full-mobile-style' ) {
						$media = "only screen and (max-width: {$mobile_break}px)";
						wp_enqueue_style( $name, $url, array( 'tribe-events-calendar-style' ), Tribe__Events__Events::VERSION, $media );
					} else {
						wp_register_style( $name, $url, array(), Tribe__Events__Events::VERSION );
						wp_enqueue_style( $name );
					}
				}
			}
		}

		private function get_child_class_name( $style_option ) {
			$base_class_name = __CLASS__ . '_';
			// from `some-style-option` to `Some_Style_Option`
			$child_class_frag = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $style_option ) ) );
			if ( $child_class_frag == 'Tribe' ) {
				$child_class_frag = 'Default';
			}

			return $base_class_name . $child_class_frag;
		}

		/**
		 * @return string
		 */
		private function get_default_child_class_path() {
			return dirname( __FILE__ ) . '/' . __CLASS__ . '_Default.php';
		}

		/**
		 * @return string
		 */
		private function get_abstract_child_class_path() {
			return dirname( __FILE__ ) . '/' . __CLASS__ . '_Abstract_Events_Css.php';
		}

		/**
		 * @param $child_class_name
		 *
		 * @return string
		 */
		protected function get_child_class_path( $child_class_name ) {
			$child_class_path = dirname( __FILE__ ) . '/' . $child_class_name . '.php';

			return $child_class_path;
		}
	}