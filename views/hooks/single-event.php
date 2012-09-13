<?php

/**
 * The abstracted view of a single event.
 * This view contains the hooks and filters required to create an effective single event view.
 *
 * You can recreate and ENTIRELY new single view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a single-event.php file in a tribe-events/ directory
 * within your theme directory, which will override the /views/single-event.php.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Event_Template')){
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {
		function init(){
			// start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// event map
			add_filter( 'tribe_events_single_event_the_map', array( __CLASS__, 'the_map' ), 1, 1 );

			// event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// event pagination
			add_filter( 'tribe_events_single_event_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// end single template
			apply_filters( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Back Button
		public function before_template( $post_id ){
			$html = '<span class="back"><a href="' . tribe_get_events_link( $post_id ) . '">' . __('&laquo; Back to Events', 'tribe-events-calendar') . '</a></span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_template');
		}
		public function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public function the_title( $title, $post_id ){
			return apply_filters('tribe_template_factory_debug', $title, 'tribe_events_single_event_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		// Template Notices
		public function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div><!-- .event-notices -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_notices');
		}
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_meta');
		}
		// Single event meta
		public function the_meta( $post_id ){
			$filter_name = 'tribe_events_single_event_the_meta';
			echo parent::debug( $filter_name );

?>
<div id="tribe-events-event-meta" itemscope itemtype="http://schema.org/Event">
	<?php // Event details ?>
	<dl class="column">
	
		<dt class="event-label event-label-name"><?php _e( 'Event:', 'tribe-events-calendar' ); ?></dt>
		<dd itemprop="name" class="event-meta event-meta-name"><span class="summary"><?php the_title(); ?></span></dd>
		
		<?php if ( tribe_get_start_date() !== tribe_get_end_date() ) { // Start & end date ?>
			<dt class="event-label event-label-start"><?php _e( 'Start:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="event-meta event-meta-start"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
			
			<dt class="event-label event-label-end"><?php _e( 'End:', 'tribe-events-calendar' ); ?></dt>
			<dd class="event-meta event-meta-end"><meta itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_end_date(); ?></dd>	
		<?php } else { // If all day event, show only start date ?>
			<dt class="event-label event-label-date"><?php _e( 'Date:', 'tribe-events-calendar' ); ?></dt> 
			<dd class="event-meta event-meta-date"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
		<?php } ?>
		
		<?php if ( tribe_get_cost() ) : // Cost ?>
			<dt class="event-label event-label-cost"><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></dt>
			<dd itemprop="price" class="event-meta event-meta-cost"><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		
		<?php tribe_meta_event_cats(); // Event categories ?>
		
		<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) ) : // Organizer URL ?>
			<dt class="event-label event-label-organizer"><?php _e( 'Organizer:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard author event-meta event-meta-author"><span class="fn url"><?php echo tribe_get_organizer_link(); ?></span></dd>
      	<?php elseif ( tribe_get_organizer() ): // Organizer name ?>
			<dt class="event-label event-label-organizer"><?php _e( 'Organizer:', 'tribe-events-calendar' ); ?></dt>
			<dd class="vcard author event-meta event-meta-author"><span class="fn url"><?php echo tribe_get_organizer(); ?></span></dd>
		<?php endif; ?>
		
		<?php if ( tribe_get_organizer_phone() ) : // Organizer phone ?>
			<dt class="event-label event-label-organizer-phone"><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
			<dd itemprop="telephone" class="event-meta event-meta-phone"><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		
		<?php if ( tribe_get_organizer_email() ) : // Organizer email ?>
			<dt class="event-label event-label-email"><?php _e( 'Email:', 'tribe-events-calendar' ); ?></dt>
			<dd itemprop="email" class="event-meta event-meta-email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
		<?php endif; ?>
		
		<dt class="event-label event-label-updated"><?php _e( 'Updated:', 'tribe-events-calendar' ); ?></dt>
		<dd class="event-meta event-meta-updated"><span class="date updated"><?php the_date(); ?></span></dd>
		
		<?php if ( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_get_recurrence_text' ) && tribe_is_recurring_event() ) : // Show info for reoccurring events ?>
			<dt class="event-label event-label-schedule"><?php _e( 'Schedule:', 'tribe-events-calendar' ); ?></dt>
         	<dd class="event-meta event-meta-schedule"><?php echo tribe_get_recurrence_text(); ?>
         		<?php if( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_all_occurences_link' ) ): ?>
         			<a href='<?php tribe_all_occurences_link(); ?>'><?php _e( '(See all)', 'tribe-events-calendar' ); ?></a>
         		<?php endif; ?>
         	</dd>
		<?php endif; ?>
		
	</dl><!-- .column -->
	
	<?php // Location ?>
	<dl class="column" itemprop="location" itemscope itemtype="http://schema.org/Place">
	
		<?php if( tribe_get_venue() ) : // Venue info ?>
			<dt class="event-label event-label-venue"><?php _e( 'Venue:', 'tribe-events-calendar' ); ?></dt> 
			<dd itemprop="name" class="event-meta event-meta-venue">
				<?php if( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link ?>
					<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
				<?php else: // Otherwise show venue name ?>
					<?php echo tribe_get_venue( get_the_ID() ); ?>
				<?php endif; ?>
			</dd>
		<?php endif; ?>
		
		<?php if( tribe_get_phone() ) : // Venue phone ?>
			<dt class="event-label event-label-venue-phone"><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt> 
			<dd itemprop="telephone" class="event-meta event-meta-venue-phone"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		
		<?php if( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
			<dt class="event-label event-label-address">
				<?php _e( 'Address:', 'tribe-events-calendar' ) ?><br />
				<?php if( tribe_show_google_map_link( get_the_ID() ) ) : // Google map ?>
					<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
				<?php endif; ?>
			</dt>
			<dd class="event-meta event-meta-address">
				<?php echo tribe_get_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
		
	</dl><!-- .column -->
  
   	<?php if( function_exists('tribe_the_custom_fields') && tribe_get_custom_fields( get_the_ID() ) ): ?>
	  	<?php tribe_the_custom_fields( get_the_ID() ); ?>
	<?php endif; ?>
</div><!-- #tribe-events-event-meta -->
<?php
			echo parent::debug( $filter_name, false );
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_meta');
		}
		// Embedded Google map
		public function the_map( $post_id ){
			$html = '';
			if( tribe_embed_google_map( $post_id ) &&  tribe_address_exists( $post_id ) ) 
				$html = tribe_get_embedded_map();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_map');
		}
		// Single event content
		public function before_the_content( $post_id ){
			$html = '<div class="entry">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_content');
		}
		public function the_content( $post_id ){
			$html = '';
			// Event featured image
			if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
				$html .= get_the_post_thumbnail();
			// Content
			$html .= '<div class="summary">' . get_the_content( $post_id ) . '</div>';
			// todo separate this into the tickets
			// if (function_exists('tribe_get_ticket_form') && tribe_get_ticket_form()) 
			// 	tribe_get_ticket_form();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .entry -->';
			// iCal link
			if( function_exists('tribe_get_single_ical_link') )
				$html .= '<a class="ical single" href="' . tribe_get_single_ical_link() . '">' . __('iCal Import', 'tribe-events-calendar') . '</a>';
			// gCal link
			if( function_exists('tribe_get_gcal_link') )
				$html .= '<a href="' . tribe_get_gcal_link() . '" class="gcal-add" title="' . __('Add to Google Calendar', 'tribe-events-calendar') . '">' . __('+ Google Calendar', 'tribe-events-calendar') . '</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_content');
		}
		// Single event navigation
		public function before_pagination( $post_id){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_pagination');
		}
		public function pagination( $post_id ){
			$html = '<div class="navlink tribe-previous">' . tribe_get_prev_event_link() . '</div><div class="navlink tribe-next">' . tribe_get_next_event_link() . '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_pagination');
		}
		public function after_template( $post_id ){
			$html = '<div style="clear:both"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_template');
		}
	}
	Tribe_Events_Single_Event_Template::init();
}