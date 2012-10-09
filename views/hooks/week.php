<?php
/**
 * @for Week Grid Template
 * This file contains the hook logic required to create an effective week grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Week_Template')){
	class Tribe_Events_Week_Template extends Tribe_Template_Factory {

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_week_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// List pagination
			add_filter( 'tribe_events_week_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_week_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_week_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// Start list loop
			add_filter( 'tribe_events_week_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );

			add_filter( 'tribe_events_week_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );
	
			// End list loop
			add_filter( 'tribe_events_week_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
				// End list template
			add_filter( 'tribe_events_week_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public function before_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_template');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_loop');
		}
		public function inside_before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_inside_before_loop');
		}

		public function the_grid() {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			$start_of_week = tribe_get_first_week_day( $wp_query->get('start_date'));

			ob_start();
?>
			<table class="tribe-events-week">
				<thead>
					<tr>
						<th>&nbsp;</th>
					<?php
						for( $n = 0; $n < 7; $n++ ) {
							printf('<th id="tribe-events-%1$s" title="%3$s"><a href="%2$s">%3$s</a></th>',
								strtolower(Date('l', strtotime($start_of_week . " +$n days"))),
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($start_of_week . " +$n days") ) ),
								Date('l jS', strtotime($start_of_week . " +$n days"))
								);
						}
					?>
					</tr>
				</thead>

			</table>
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_the_grid');
		}

		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_loop');
		}

		// List Pagination
		public function before_pagination( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_pagination');
		}
		public function pagination( $post_id ){
			global $wp_query;
			echo '<pre>';
			print_r($wp_query->posts);
			echo '</pre>';
			$current_week = tribe_get_first_week_day( $wp_query->get('start_date') );
			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$dropdown = ob_get_clean();
			
			// Display Week Navigation
			$html = sprintf('<span class="tribe-events-week-nav"><span class="tribe-events-prev-week"><a href="%s">%s</a></span> %s <span class="tribe-events-next-week"><a href="%s">%s</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="" style="display: none" /></span></span>',
								tribe_get_last_week_permalink( $current_week ),
								__( 'Previous Week', 'tribe-events-calendar-pro' ),
								$dropdown,
								tribe_get_next_week_permalink( $current_week ),
								__( 'Next Week', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_template');		
		}
	}
	Tribe_Events_Week_Template::init();
}