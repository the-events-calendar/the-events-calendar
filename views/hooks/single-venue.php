<?php
/**
 * @for Single Venue Template
 * This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Venue_Template')){
	class Tribe_Events_Single_Venue_Template extends Tribe_Template_Factory {
		public static function init(){
			// start single venue template
			add_filter( 'tribe_events_single_venue_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// start single venue
			add_filter( 'tribe_events_single_venue_before_venue', array( __CLASS__, 'before_venue' ), 1, 1 );
	
			// venue map
			add_filter( 'tribe_events_single_venue_map', array( __CLASS__, 'map' ), 1, 1 );
		
			// venue meta
			add_filter( 'tribe_events_single_venue_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// end single venue
			add_filter( 'tribe_events_single_venue_after_venue', array( __CLASS__, 'after_venue' ), 1, 1 );
	
			// start upcoming event loop
			add_filter( 'tribe_events_single_venue_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
	
			// venue loop title
			add_filter( 'tribe_events_single_venue_loop_title', array( __CLASS__, 'loop_title' ), 1, 1 );
			
			add_filter( 'tribe_events_single_venue_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );
			
			// event start date
			add_filter( 'tribe_events_single_venue_the_start_date', array( __CLASS__, 'the_start_date' ), 1, 1 );
			
			// event title
			add_filter( 'tribe_events_single_venue_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// event content
			add_filter( 'tribe_events_single_venue_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );
			
			// event meta
			add_filter( 'tribe_events_single_venue_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );
		
			add_filter( 'tribe_events_single_venue_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			
			// end upcoming event loop
			add_filter( 'tribe_events_single_venue_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
			// end single venue template
			add_filter( 'tribe_events_single_venue_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Venue Template
		public function before_template( $post_id ){
			$html = '<span class="back"><a href="'. tribe_get_events_link() .'">'. __( '&laquo; Back to Events', 'tribe-events-calendar-pro' ) .'</a></span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_template');
		}
		// Start Single Venue
		public function before_venue( $post_id ){
			$html = '<div id="tribe-events-event-meta">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_venue');
		}
		// Venue Map
		public function venue_map( $post_id ){
			$html = '<div style="margin: 0 0 10px 0; float: right;">';
			$html .= tribe_get_embedded_map( get_the_ID(), '350px', '200px' );
			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_map');
		}
		// Venue Meta
		public function before_the_meta( $post_id ){
			$html = '<dl class="column location" itemscope itemtype="http://schema.org/Place">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
?>
			<dt class="venue-label venue-label-name"><?php echo __( 'Name:', 'tribe-events-calendar-pro' ); ?></dt> 
			<dd itemprop="name" class="venue-meta venue-meta-name"><?php the_title(); ?></dd>
		
			<?php if( tribe_get_phone() ) : // Venue phone ?>
			<dt class="venue-label venue-label-phone"><?php echo __( 'Phone:', 'tribe-events-calendar-pro' ); ?></dt> 
 			<dd itemprop="telephone" class="venue-meta venue-meta-phone"><?php echo tribe_get_phone(); ?></dd>
 			<?php endif; ?>
 		
			<?php if( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
			<dt class="venue-label venue-label-address">
				<?php echo __( 'Address:', 'tribe-events-calendar-pro' ); ?><br />
				<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
				<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="<?php echo __( 'Click to view a Google Map', 'tribe-events-calendar-pro' ); ?>" target="_blank"><?php echo __( 'Google Map', 'tribe-events-calendar-pro' ); ?></a>
				<?php endif; ?>
			</dt>
 			<dd class="venue-meta venue-meta-address">
				<?php echo tribe_get_full_address( get_the_ID() ); ?>
 			</dd>
 			<?php endif; ?>
		
			<?php if ( get_the_content() != '' ): // Venue content ?>
			<dt class="venue-label venue-label-description"><?php echo __( 'Description:', 'tribe-events-calendar-pro' ); ?></dt>
			<dd class="venue-meta venue-meta-description"><?php the_content(); ?></dd>
 			<?php endif ?>			
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '</dl><!-- .column -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_meta');
		}
		// End Single Venue
		public function after_venue( $post_id ){
			$html = '</div><!-- #tribe-events-event-meta -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_venue');
		}
		// Start Upcoming Event Loop
		public function before_loop( $post_id ){
			$html = '<div id="tribe-events-loop" class="tribe-events-events post-list clearfix upcoming venue-events">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_loop');
		}
		// Venue Loop Title
		public function loop_title( $post_id ){
			$html = '<h2 class="tribe-events-cal-title">'. __( 'Upcoming Events At This Venue', 'tribe-events-calendar-pro' ) .'</h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_loop_title');
		}
		
		public function inside_before_loop( $post_id ){
			$html = '<div id="post-'. the_ID() .'" '. post_class( $first ? 'tribe-events-event clearfix first': 'tribe-events-event clearfix' ); $first = false; .' itemscope itemtype="http://schema.org/Event">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_inside_before_loop');
		}
			
		// Event Start Date
		public function the_start_date( $post_id ){
			$html = '';
			if(tribe_is_new_event_day())
 				$html .= '<h4 class="event-day">'. tribe_get_start_date( null, false ) .'</h4>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_start_date');
		}
		// Event Title
		public function the_title( $post_id ){
			$html = '<h2 class="entry-title" itemprop="name"><a href="'. tribe_get_event_link() .'" title="'. the_title_attribute( 'echo=0' ) .'" rel="bookmark" itemprop="url">'. get_the_title() .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_title');
		}
		// Event Content
		public function before_the_content( $post_id ){
			$html = '<div class="entry-content tribe-events-event-entry" itemprop="description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_content');
		}
		public function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= get_the_excerpt();
			else
				$html .= get_the_content();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .tribe-events-event-entry -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_content');
		}
		// Event Meta
		public function before_the_meta( $post_id ){
			$html = '<div class="tribe-events-event-list-meta">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
?>
			<table>
 				<?php if (tribe_is_multiday()): ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Start:', 'tribe-events-calendar-pro' ); ?></td>
						<td class="tribe-events-event-meta-value"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_start_date(); ?></td>
					</tr>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'End:', 'tribe-events-calendar-pro' ); ?></td>
						<td class="tribe-events-event-meta-value"><meta itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_end_date(); ?></td>
					</tr>
 				<?php else: ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Date:', 'tribe-events-calendar-pro' ); ?></td>
						<td class="tribe-events-event-meta-value"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_start_date(); ?></td>
					</tr>
 				<?php endif; ?>
 				<?php
 					$cost = tribe_get_cost();
 					if ( !empty( $cost ) ) :
 				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Cost:', 'tribe-events-calendar-pro' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
					</tr>
 				<?php endif; ?>
 			</table>				
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '</div><!-- .tribe-events-event-list-meta -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_meta');
		}
		
		public function inside_after_loop( $post_id ){
			$html = '</div><!-- #post -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_inside_after_loop');
		}
		
		// End Upcoming Event Loop
		public function after_loop( $post_id ){
			$html = '</div><!-- #tribe-events-loop -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_loop');
		}	
		// End Single Venue Template
		public function after_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_template');
		}
	}
	Tribe_Events_Single_Venue_Template::init();
}