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

		static $timeslots = array();
		static $loop_increment = 0;

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
				<!--
					@Tim
					I noticed when using the navigation that the url seems to get updated, but not the events or
					or events page date. Looks like maybe the ajax is busted, b/c when I refresh on a given dates page,
					then the title updated to the correct day date.
				-->
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
	
		<h3><?php echo Date("l, F jS Y", strtotime($wp_query->get('start_date'))); ?></h3>
		
		<div class="tribe-events-loop-day hfeed vcalendar">
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_loop');
		}
		public function inside_before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_before_loop');
		}

		public function the_event( $post_id ){
			global $wp_query, $post;

			$html = '';

			// setup the "start time" for the event header
			$start_time = ( $post->tribe_is_allday ) ? 
				__( 'All Day', 'tribe-events-calendar' ) :
				tribe_get_start_date( null, false, 'ga ' );

			// determine if we want to open up a new time block
			if( ! in_array( $start_time, self::$timeslots ) ) {

				self::$timeslots[] = $start_time;	

				// close out any prior opened time blocks
				$html .= ( self::$loop_increment > 0 ) ? '</div>' : '';

				// open new time block
				$html .= '<div class="tribe-events-day-time-slot">';

				// time vs all day header
				$html .= sprintf( '<h5>%s</h5>', $start_time );

			}

			ob_start(); 
?>				
				<div class="hentry vevent">
					<h4 class="entry-title summary"><a href="<?php the_permalink(); ?>" class="url" rel="bookmark"><?php the_title(); ?></a></h4>
					
					<?php if ( tribe_get_start_date() !== tribe_get_end_date() ) { // Start & end date ?>
						<p class="updated published">
							<abbr class="tribe-events-abbr dtstart" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date( null, false, 'ga ' ) . '- '; ?></abbr>
							<abbr class="tribe-events-abbr dtend" title="<?php echo tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_end_date( null, false, 'ga' ); ?></abbr>
						</p>
					<?php } else { // If all day event, show only start date ?>
						<p class="updated published"><abbr class="tribe-events-abbr dtstart" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php _e( 'All Day', 'tribe-events-calendar' ); ?></abbr></p>
					<?php } ?>
					
					<?php if( tribe_get_venue() ) { // Venue ?>
					<p class="location vcard fn org"><a href="#" rel="bookmark"><?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?></a></p>
					<?php } ?>
					
					<p class="entry-content description"><?php echo get_the_excerpt(); ?></p>

<?php 
/*
						TODO: @Ryan 
						See my note https://central.tri.be/issues/18055#note-4 we really should be using 
						tribe_meta_event_cats() instead of a new tag...
 */
?>
				<?php tribe_get_event_categories( $post_id, array('echo'=>true) ); ?>
				</div><!-- .hentry .vevent -->
				
<?php
			$html .= ob_get_clean();

			// close out the last time block
			$html .= ( count($wp_query->posts) == self::$loop_increment ) ? '</div>' : '';

			// internal increment to keep track of position within the loop
			self::$loop_increment++;

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_the_event');
		}

		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '</div><!-- .tribe-events-loop-day -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_loop');
		}

		public function before_header( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_pagination');
		}
		public function the_header( $post_id ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

?>
			<!--
				@Tim
				We need to implement the date picker here and for week view. I was going to attempt it, 
				but with everything that I think needs to be adjusted, we could save time with the crunch we 
				are in by having you implement as this ajax nav seems busted anyway.
			-->
<?php
			$current_day = $wp_query->get('start_date');
			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );
			// Display Day Navigation
			// <-- Previous Day | Month/Day/Year Selector | Next Day -->
			$html = sprintf('<div id="tribe-events-header"><h3 class="tribe-events-visuallyhidden">%s</h3><ul class="tribe-events-sub-nav"><li class="tribe-events-nav prev"><a href="%s" data-day="%s" rel="prev">&#x2190; %s</a></li><li class="tribe-events-nav next"><a href="%s" data-day="%s" rel="next">%s &#x2192;</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="Loading events" /></li></ul></div>',
								__( 'Day Navigation', 'tribe-events-calendar' ),
								tribe_get_day_permalink( $yesterday ),
								$yesterday,
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								tribe_get_day_permalink( $tomorrow ),
								$tomorrow,
								__( 'Tomorrow', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_pagination');
		}
		public function after_header( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '';

			// iCal import button
			if( function_exists( 'tribe_get_ical_link' ) ){
				$html .= sprintf('<a class="tribe-events-ical tribe-events-button-grey" title="%s" href="%s">%s</a>',
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