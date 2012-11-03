<?php
/**
 * @for Single Event Template
 * This file contains the hook logic required to create an effective single event view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Event_Template')){
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// Event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// Event map
			add_filter( 'tribe_events_single_event_the_map', array( __CLASS__, 'the_map' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// Event pagination
			add_filter( 'tribe_events_single_event_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// End single template
			apply_filters( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Template
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-single">';
			$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link( $post_id ) . '" rel="bookmark">'. __('&laquo; Back to Events', 'tribe-events-calendar') .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_template');
		}
		// Event Title
		public function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public function the_title( $title, $post_id ){
			$title = '<h2 class="entry-title summary">'. $title .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $title, 'tribe_events_single_event_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		// Event Notices
		public function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_notices');
		}
		// Event Meta
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
?>
<div class="tribe-events-event-meta">
	
	<dl class="tribe-events-column">
	
		<dt><?php _e( 'Event:', 'tribe-events-calendar' ); ?></dt>
		<dd class="summary"><?php the_title(); ?></dd>
		
		<?php if ( tribe_get_start_date() !== tribe_get_end_date() ) { // Start & end date ?>
			<dt><?php _e( 'Start:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="published dtstart"><abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr></dd>
			
			<dt><?php _e( 'End:', 'tribe-events-calendar' ); ?></dt>
			<dd class="dtend"><abbr class="tribe-events-abbr" title="<?php echo tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_end_date(); ?></abbr></dd>	
		<?php } else { // If all day event, show only start date ?>
			<dt><?php _e( 'Date:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="published dtstart"><abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr></dd>
		<?php } ?>
		
		<?php if ( tribe_get_cost() ) : // Cost ?>
			<dt><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></dt>
			<dd class="tribe-events-event-cost"><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		
		<?php 

		$args = array(
				'before' => '<dd class="tribe-event-categories">',
				'sep' => ', ',
				'after' => '</dd>',
				'label' => __( 'Category', 'tribe-events-calendar' ),
				'label_before' => '<dt>',
				'label_after' => '</dt>',
				'wrap_before' => '',
				'wrap_after' => ''
			);
		// Event categories 
		echo tribe_get_event_categories( $post_id, $args );

		// tribe_meta_event_cats(); 

		?>
		
		<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) ) : // Organizer URL ?>
			<dt><?php _e( 'Organizer:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard author fn org"><?php echo tribe_get_organizer_link(); ?></dd>
      	<?php elseif ( tribe_get_organizer() ): // Organizer name ?>
			<dt><?php _e( 'Organizer:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard author fn org"><?php echo tribe_get_organizer(); ?></dd>
		<?php endif; ?>
		
		<?php if ( tribe_get_organizer_phone() ) : // Organizer phone ?>
			<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard tel"><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		
		<?php if ( tribe_get_organizer_email() ) : // Organizer email ?>
			<dt><?php _e( 'Email:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
		<?php endif; ?>
		
		<dt><?php _e( 'Updated:', 'tribe-events-calendar' ); // Last event updated date ?></dt>
		<dd class="updated"><abbr class="tribe-events-abbr" title="<?php the_time( 'c' ); ?>"><?php the_time( 'F j, Y' ); ?></abbr></dd>
		
		<?php if ( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_get_recurrence_text' ) && tribe_is_recurring_event() ) : // Show info for reoccurring events ?>
			<dt><?php _e( 'Schedule:', 'tribe-events-calendar' ); ?></dt>
         	<dd class="tribe-events-event-meta-recurrence">
         		<?php echo tribe_get_recurrence_text(); ?>
         		<?php if( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_all_occurences_link' ) ): ?>
         			<a href="<?php tribe_all_occurences_link(); ?>"><?php _e( '(See all)', 'tribe-events-calendar' ); ?></a>
         		<?php endif; ?>
         	</dd>
		<?php endif; ?>
		
	</dl><!-- .tribe-events-column -->
	
	<?php // Location ?>
	<dl class="tribe-events-column location">
	
		<?php if( tribe_get_venue() ) : // Venue info ?>
			<dt><?php _e( 'Venue:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="vcard fn org">
				<?php if( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link ?>
					<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
				<?php else: // Otherwise show venue name ?>
					<?php echo tribe_get_venue( get_the_ID() ); ?>
				<?php endif; ?>
			</dd>
		<?php endif; ?>
		
		<?php if( tribe_get_phone() ) : // Venue phone ?>
			<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="vcard tel"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		
		<?php if( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
			<dt><?php _e( 'Address:', 'tribe-events-calendar' ) ?><br />
				<?php if( tribe_show_google_map_link( get_the_ID() ) ) : // Google map ?>
				<a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
				<?php endif; ?>
			</dt>
			<dd class="location">
				<?php echo tribe_get_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
		
	</dl><!-- .tribe-events-column -->
  
   	<?php if( function_exists('tribe_the_custom_fields') && tribe_get_custom_fields( get_the_ID() ) ): ?>
	  	<?php tribe_the_custom_fields( get_the_ID() ); ?>
	<?php endif; ?>
	
</div><!-- .tribe-events-event-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_meta');
		}
		// Embedded Google Map
		public function the_map( $post_id ){
			$html = '';
			if(tribe_embed_google_map(get_the_ID()) && tribe_address_exists(get_the_ID()))
				$html .= tribe_get_embedded_map();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_map');
		}		
		// Event Content
		public function before_the_content( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_content');
		}
		public function the_content( $post_id ){
			ob_start();

			// Single event content ?>
			<div class="entry-content description">
				
				<?php // Event image
				if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) {
					the_post_thumbnail();
				}
				// Event content
				the_content(); ?>
		
			</div><!-- .description -->
	
			<?php // Event Tickets - todo separate this into the tickets
			if ( function_exists( 'tribe_get_ticket_form' ) && tribe_get_ticket_form() ) { 
				tribe_get_ticket_form(); 
			}
			
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '';
			// iCal link
			if( function_exists('tribe_get_single_ical_link') )
				$html .= '<a class="tribe-events-ical tribe-events-button-grey" href="' . tribe_get_single_ical_link() . '">' . __('iCal Import', 'tribe-events-calendar') . '</a>';
			// gCal link
			if( function_exists('tribe_get_gcal_link') )
				$html .= '<a class="tribe-events-gcal tribe-events-button-grey" href="' . tribe_get_gcal_link() . '" title="' . __('Add to Google Calendar', 'tribe-events-calendar') . '">' . __('+ Google Calendar', 'tribe-events-calendar') . '</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_content');
		}
		// Event Pagination
		public function before_pagination( $post_id){
			$html = '<div class="tribe-events-loop-nav">';
			$html .= '<h3 class="tribe-visuallyhidden">'. __( 'Event navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_pagination');
		}
		public function pagination( $post_id ){
			$html = '<li class="tribe-nav-previous">' . tribe_get_prev_event_link() . '</li>';
			$html .= '<li class="tribe-nav-next">' . tribe_get_next_event_link() . '</li>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '</ul></div><!-- .tribe-events-loop-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_pagination');
		}
		// After Single Template
		public function after_template( $post_id ){
			$html = '</div>!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_template');
		}
	}
	Tribe_Events_Single_Event_Template::init();
}
