<?php
/**
 * @for Day Grid Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }


if( !class_exists('Tribe_Events_Day_Template')){
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_day_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// List pagination
			add_filter( 'tribe_events_day_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			add_filter( 'tribe_events_day_the_header', array( __CLASS__, 'the_header' ), 1, 1 );
			add_filter( 'tribe_events_day_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Start list loop
			add_filter( 'tribe_events_day_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_day_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );

			add_filter( 'tribe_events_day_the_event', array( __CLASS__, 'the_event' ), 1, 1 );
	
			// End list loop
			add_filter( 'tribe_events_day_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_day_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
				// End list template
			add_filter( 'tribe_events_day_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public function before_template( $post_id ){
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
			ob_start(); ?>
			<div id="tribe-events-content" class="tribe-events-day-grid">
				<title><?php wp_title(); ?></title>
			<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_template');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			global $wp_query;
			ob_start();
?>
	<table cellspacing="0" cellpadding="0" class="tribe-events-grid">
		<thead>
			<tr>
				<th scope="column"><?php echo Date("l, F jS Y", strtotime($wp_query->get('start_date'))); ?></th>
			</tr>
		</thead>
		<tbody class="hfeed">
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_loop');
		}
		public function inside_before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_before_loop');
		}

		public function the_event( $post_id ){
			ob_start(); 
?>
	<div class="hentry vevent">
		<h4 class="entry-title summary"><a href="<?php echo get_the_permalink( $post_id ); ?>" class="url" rel="bookmark">Intro to Spinning</a></h4>
		<p class="updated published"><abbr class="tribe-events-abbr dtstart" title="2010-09-13">All Day</abbr></p>
		<p class="location"><a href="" rel="bookmark">Room Name</a></p>
		<p class="entry-content description">I saw for the first time the earth's shape. I could easily see the shores of continents, islands, great rivers, folds of the terrain, large bodies of water.</p>
		<ul class="tribe-events-grid-meta">
			<li><a href="" rel="tag">Category A</a>,</li>
			<li><a href="" rel="tag">Category B</a></li>
		</ul>
	</div><!-- .hentry .vevent -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_the_event');
		}

		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '</tbody><!-- .hfeed --></table><!-- .tribe-events-grid -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_loop');
		}

		public function before_header( $post_id ){
			$html = '<div id="tribe-events-calendar-header" class="clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_pagination');
		}
		public function the_header( $post_id ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$dropdown = ob_get_clean();

			// echo '<pre>';
			// print_r($wp_query->posts);
			// echo '</pre>';
			$current_day = $wp_query->get('start_date');
			// Display Day Navigation
			// <-- Previous Day | Month/Day/Year Selector | Next Day -->
			$html = sprintf('<span class="tribe-events-week-nav"><span class="tribe-events-prev-week"><a href="%s">%s</a></span> %s <span class="tribe-events-next-week"><a href="%s">%s</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="" style="display: none" /></span></span>',
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " -1 day") ) ),
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								$dropdown,
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " +1 day") ) ),
								__( 'Tomorrow', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_pagination');
		}
		public function after_header( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '';

			// iCal import button
			if( function_exists( 'tribe_get_ical_link' ) ){
				$html .= sprintf('<a class="tribe-events-ical" title="%s" href="%s">%s</a>',
					esc_attr( 'iCal Import', 'tribe-events-calendar' ),
					tribe_get_ical_link(),
					__( 'iCal Import', 'tribe-events-calendar' )
					);
			}
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_template');		
		}
	}
	Tribe_Events_Day_Template::init();
}