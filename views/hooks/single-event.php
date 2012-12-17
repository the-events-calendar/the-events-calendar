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

			// Check if event has passed
			$gmt_offset = (get_option('gmt_offset') >= '0' ) ? ' +' . get_option('gmt_offset') : " " . get_option('gmt_offset');
		 	$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
		 	if ( strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) { 
		 		TribeEvents::setNotice( __('This event has passed.', 'tribe-events-calendar') );
		 	} 

			// Start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			add_filter( 'tribe_events_single_event_featured_image', array(__CLASS__,'featured_image'), 1, 1);

			// Event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// Event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// Event pagination
			add_filter( 'tribe_events_single_event_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// End single template
			add_filter( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Template
		public static function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-single">';
			$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __( '&laquo; Back to Events', 'tribe-events-calendar-pro' ) .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_template');
		}
		public static function featured_image( $post_id ){
			$html = '';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'full');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_featured_image');
		}
		// Event Title
		public static function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public static function the_title( $post_id ){
			$html = the_title('<h2 class="entry-title summary">','</h2>', false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_title');
		}
		public static function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		// Event Notices
		public static function notices( $post_id ) {
			$html = tribe_events_the_notices(false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_notices');
		}
		// Event Content
		public static function before_the_content( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_content');
		}
		public static function the_content( $post_id ){
			ob_start();

			// Single event content ?>
			<div class="tribe-event-schedule tribe-clearfix">
				<h2><?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?><?php 	if ( tribe_get_cost() ) :  echo '<span class="tribe-divider">|</span><span class="tribe-event-cost">'. tribe_get_cost() .'</span>'; endif; ?></h2>
				
				<?php // iCal/gCal links
				if ( function_exists( 'tribe_get_single_ical_link' ) || function_exists( 'tribe_get_gcal_link' ) ) { ?>
					<div class="tribe-event-cal-links">
				<?php // iCal link
				if ( function_exists( 'tribe_get_single_ical_link' ) ) {
					echo '<a class="tribe-events-ical tribe-events-button-grey" href="' . tribe_get_single_ical_link() . '">' . __( 'iCal Import', 'tribe-events-calendar' ) . '</a>';
				}
				// gCal link
				if ( function_exists( 'tribe_get_gcal_link' ) ) {
					echo  '<a class="tribe-events-gcal tribe-events-button-grey" href="' . tribe_get_gcal_link() . '" title="' . __( 'Add to Google Calendar', 'tribe-events-calendar' ) . '">' . __( '+ Google Calendar', 'tribe-events-calendar' ) . '</a>';
				}
					echo '</div><!-- .tribe-event-cal-links -->';
				} ?>
			</div>
			
			<div class="entry-content description">

				<?php // Event content
				the_content(); ?>

			</div><!-- .description -->

			<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_content');
		}
		public static function after_the_content( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_content');
		}		
		// Event Meta
		public static function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_meta');
		}
		public static function the_meta( $post_id ){
			
			$tribe_event_custom_fields = '';
			if ( class_exists( 'TribeEventsPro' ) && function_exists( 'tribe_the_custom_fields' ) ) : // If pro, show venue w/ link 
				$tribe_event_custom_fields = tribe_get_custom_fields( get_the_ID() );
			endif; 	
			ob_start();
?>
	<div class="tribe-events-event-meta tribe-clearfix">
		<div class="tribe-events-meta-column">
			<dl>
			<h3 class="tribe-event-single-section-title"><dt><?php _e( 'Details', 'tribe-events-calendar' ); ?></dt></h3>			
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
			<?php if ( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_get_recurrence_text' ) && tribe_is_recurring_event() ) : // Show info for reoccurring events ?>
				<dt><?php _e( 'Schedule:', 'tribe-events-calendar' ); ?></dt>
	         	<dd class="tribe-events-event-meta-recurrence">
	         		<?php echo tribe_get_recurrence_text(); ?>
	         		<?php if ( class_exists( 'TribeEventsRecurrenceMeta' ) && function_exists( 'tribe_all_occurences_link' ) ): ?>
	         			<a href="<?php tribe_all_occurences_link(); ?>"><?php _e( '(See all)', 'tribe-events-calendar' ); ?></a>
	         		<?php endif; ?>
	         	</dd>
			<?php endif; ?>
			<?php if ( tribe_get_event_categories() ) : // Categories 
				echo tribe_get_event_categories( get_the_ID(), array(
				   'before' => '',
				   'sep' => ', ',
				   'after' => '',
				   'label' => null,
				   'label_before' => '<dt>',
				   'label_after' => '</dt>',
				   'wrap_before' => '<dd class="tribe-events-event-categories">',
				   'wrap_after' => '</dd>'
				));
			endif; ?>
	
			<?php tribe_meta_event_tags(__('Event Tags:', 'tribe-events-calendar')); ?>

			<?php if ( tribe_get_event_website_link() != '' ) { ?>
				<dt><?php _e( 'Website:', 'tribe-events-calendar-pro' ); ?></dt>
				<dd class="published event-url"><?php echo tribe_get_event_website_link( get_the_ID() ); ?></dd>
			<?php } ?>	
			<?php $origin_to_display = apply_filters( 'tribe_events_display_event_origin', '', get_the_ID() );
				if ( $origin_to_display != '' ) { ?>
				<dt><?php _e( 'Origin:', 'tribe-events-calendar-pro' ); ?></dt>
				<dd class="published event-origin"><?php echo $origin_to_display; ?></dd>
			<?php } ?>
			</dl>
		</div><!-- .tribe-events-meta-column -->
		<div class="tribe-events-meta-column">
			<?php  // START VENUE
				if ( tribe_embed_google_map( get_the_ID() ) && tribe_address_exists( get_the_ID() ) && ( $tribe_event_custom_fields || tribe_has_organizer() ) ) { 
				// When there is a venue map and custom fields or an organizer, the venue information shows in a seperate section 
			?>
			<?php } else { ?>
			<?php if ( tribe_get_venue() ) : // Venue info ?>
				<dl>
					<h3 class="tribe-event-single-section-title"><dt><?php _e( 'Venue', 'tribe-events-calendar' ); ?></dt></h3>
					<dd class="vcard fn org">
						<?php if ( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link ?>
							<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
						<?php else: // Otherwise show venue name ?>
							<?php echo tribe_get_venue( get_the_ID() ); ?>
						<?php endif; ?>
					</dd>
				
					<?php if ( tribe_get_phone() ) : // Venue phone ?>
						<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
						<dd class="vcard tel"><?php echo tribe_get_phone(); ?></dd>
					<?php endif; ?>

					<?php if ( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
						<dt><?php _e( 'Address:', 'tribe-events-calendar' ) ?></dt>
						<dd class="location">
							<?php echo tribe_get_full_address( get_the_ID() ); ?>
							<?php if ( tribe_show_google_map_link( get_the_ID() ) ) : // Google map ?>
								<a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
							<?php endif; ?>
						</dd>
					<?php endif; ?>

					<?php if ( tribe_get_venue_website_link() ) : // Venue website ?>
						<dt><?php _e( 'Website:', 'tribe-events-calendar' ) ?></dt>
						<dd class="vcard url">
							<?php echo tribe_get_venue_website_link(); ?>
						</dd>	
					<?php endif; ?>			
					</dl>
					<?php endif;  // END VENUE ?>

			<?php if ( !tribe_embed_google_map( get_the_ID() ) ) : // if no map, start a new column ?>
				</div><!-- .tribe-events-meta-column -->
				<div class="tribe-events-meta-column">	
			<?php endif; ?>	

			<?php } ?>	

			<?php // START ORGANIZER
				if ( tribe_has_organizer() ) : ?>
					<dl>
						<h3 class="tribe-event-single-section-title"><dt><?php _e( 'Organizer', 'tribe-events-calendar' ); ?></dt></h3>
						<dd class="vcard author fn org">
							<?php if ( class_exists( 'TribeEventsPro' ) ): // If pro, show organizer w/ link ?>
								<?php tribe_get_organizer_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
							<?php else: // Otherwise show organizer name ?>
								<?php echo tribe_get_organizer( get_the_ID() ); ?>
							<?php endif; ?>
						</dd>

					<?php if ( tribe_get_organizer_phone() ) : // Organizer phone ?>
						<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
						<dd class="vcard tel"><?php echo tribe_get_organizer_phone(); ?></dd>
					<?php endif; ?>

					<?php if ( tribe_get_organizer_email() ) : // Organizer email ?>
						<dt><?php _e( 'Email:', 'tribe-events-calendar' ); ?></dt>
						<dd class="vcard email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
					<?php endif; ?>

					<?php if ( tribe_get_organizer_website_link() ) : // Organizer website ?>
						<dt><?php _e( 'Website:', 'tribe-events-calendar' ) ?></dt>
						<dd class="vcard url">
							<?php echo tribe_get_organizer_website_link(); ?>
						</dd>	
					<?php endif; ?>
				</dl>
			<?php if ( tribe_embed_google_map( get_the_ID() ) && tribe_address_exists( get_the_ID() ) && $tribe_event_custom_fields ) : // If there's a map and custom fields, start a new column ?>
				</div><!-- .tribe-events-meta-column -->
				<div class="tribe-events-meta-column">
			<?php endif; ?>	

			<?php // END ORGANIZER
				endif; 
			?>

			<?php if ( $tribe_event_custom_fields ) { ?>
				<dl>
				<h3 class="tribe-event-single-section-title"><dt><?php _e( 'Other', 'tribe-events-calendar' ); ?></dt></h3>
				<?php echo tribe_the_custom_fields( get_the_ID() ); ?>
				</dl>
			<?php } ?>

			<?php if ( tribe_embed_google_map( get_the_ID() ) && tribe_address_exists( get_the_ID() ) && empty($tribe_event_custom_fields) && !tribe_has_organizer()  ){ ?>
				</div><!-- .tribe-events-meta-column -->
				<div class="tribe-events-meta-column">
					<div class="tribe-event-venue-map">
						<?php echo tribe_get_embedded_map(); ?>
					</div>
			<?php } ?>
		</div>	
									
		</div><!-- .tribe-events-event-meta -->
	<?php if ( tribe_embed_google_map( get_the_ID() ) && tribe_address_exists( get_the_ID() ) && ( $tribe_event_custom_fields || tribe_has_organizer() ) ) : // If there's a venue map, show this seperate section ?>
				<div class="tribe-event-single-section tribe-events-event-meta tribe-clearfix">
					<div class="tribe-events-meta-column">
						<dl>
						<h3 class="tribe-event-single-section-title"><dt><?php _e( 'Venue', 'tribe-events-calendar' ); ?></dt></h3>
						<?php if ( tribe_get_venue() ) : // Venue info ?>
									<dd class="vcard fn org">
										<?php if ( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link ?>
											<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
										<?php else: // Otherwise show venue name ?>
											<?php echo tribe_get_venue( get_the_ID() ); ?>
										<?php endif; ?>
									</dd>
						<?php endif; ?>

						<?php if ( tribe_get_phone() ) : // Venue phone ?>
							<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
							<dd class="vcard tel"><?php echo tribe_get_phone(); ?></dd>
						<?php endif; ?>

						<?php if ( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
							<dt><?php _e( 'Address:', 'tribe-events-calendar' ) ?></dt>
							<dd class="location">
								<?php echo tribe_get_full_address( get_the_ID() ); ?>
								<?php if ( tribe_show_google_map_link( get_the_ID() ) ) : // Google map ?>
								<a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
								<?php endif; ?>								
							</dd>
						<?php endif; ?>
						<?php if ( tribe_get_venue_website_link() ) : // Venue website ?>
							<dt><?php _e( 'Website:', 'tribe-events-calendar' ) ?></dt>
							<dd class="vcard url">
								<?php echo tribe_get_venue_website_link(); ?>
							</dd>	
						<?php endif; ?>
						</dl>						
					</div>
					<div class="tribe-event-venue-map">
						<?php echo tribe_get_embedded_map(); ?>
					</div>
				</div>
			<?php endif; ?>
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_meta');
		}
		public static function after_the_meta( $post_id ){
			$html = '';
			// Event Tickets - todo separate this into the tickets
			if ( function_exists( 'tribe_get_ticket_form' ) && tribe_get_ticket_form() ) {
				$html .= tribe_get_ticket_form();
			}
			if ( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link 
				ob_start();
					tribe_single_related_events();
				$html .= ob_get_clean();
			endif; 			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_meta');
		}	
		// Event Pagination
		public static function before_pagination( $post_id ) {
			$html = '<div class="tribe-events-loop-nav">';
			$html .= '<h3 class="tribe-visuallyhidden">'. __( 'Event navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_before_pagination' );
		}
		public static function pagination( $post_id ) {
			$html = '<li class="tribe-nav-previous">' . tribe_get_prev_event_link( '&laquo; %title%' ) . '</li>';
			$html .= '<li class="tribe-nav-next">' . tribe_get_next_event_link( '%title% &raquo;' ) . '</li>';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_pagination' );
		}
		public static function after_pagination( $post_id ) {
			$html = '</ul></div><!-- .tribe-events-loop-nav -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_pagination' );
		}	
		// After Single Template
		public static function after_template( $post_id ){
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_template');
		}
	}
	Tribe_Events_Single_Event_Template::init();
}
