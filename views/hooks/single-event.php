<?php
/**
 *
 *
 * @for Single Event Template
 * This file contains the hook logic required to create an effective single event view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Single_Event_Template' ) ) {
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {
		public static function init() {


			/**
			 * Setup default meta templates
			 * @var array
			 */
			$meta_template_keys = apply_filters('tribe_events_single_event_meta_template_keys', array(
					'tribe_event_date',
					'tribe_event_cost',
					'tribe_event_category',
					'tribe_event_tag',
					'tribe_event_website',
					'tribe_event_origin',
					'tribe_event_venue_name',
					'tribe_event_venue_phone',
					'tribe_event_venue_address',
					'tribe_event_venue_website',
					'tribe_event_organizer_name',
					'tribe_event_organizer_phone',
					'tribe_event_organizer_email',
					'tribe_event_organizer_website',
					'tribe_event_custom_meta'
				));
			$meta_templates = apply_filters('tribe_events_single_event_meta_templates', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'<dt>',
					'label_after'=>'</dt>',
					'meta_before'=>'<dd class="%s">',
					'meta_after'=>'</dd>'
				));
			tribe_set_the_meta_template( $meta_template_keys, $meta_templates);

			/**
			 * Setup default meta group templates
			 * @var array
			 */
			$meta_group_template_keys = apply_filters( 'tribe_events_single_event_meta_group_template_keys', array(
					'tribe_event_details',
					'tribe_event_venue',
					'tribe_event_organizer'
				));
			$meta_group_templates = apply_filters('tribe_events_single_event_meta_group_templates', array(
					'before'=>'<div class="%s"><dl>',
					'after'=>'</dl></div>',
					'label_before'=>'<h3 class="%s"><dt>',
					'label_after'=>'</dt></h3>',
					'meta_before'=>'',
					'meta_after'=>''
				));

			tribe_set_the_meta_template( $meta_group_template_keys, $meta_group_templates, 'meta_group');

			// provide for meta actions before loading the template
			do_action('tribe_events_single_event_meta_init', $meta_template_keys, $meta_templates, $meta_group_template_keys, $meta_group_templates );

			// Check if event has passed
			$gmt_offset = ( get_option( 'gmt_offset' ) >= '0' ) ? ' +' . get_option( 'gmt_offset' ) : " " . get_option( 'gmt_offset' );
			$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
			if ( strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) {
				TribeEvents::setNotice( 'event-past', __( 'This event has passed.', 'tribe-events-calendar' ) );
			}

			// Start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Event header
			add_filter( 'tribe_events_single_event_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_single_event_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_single_event_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_single_event_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Event featured image
			add_filter( 'tribe_events_single_event_featured_image', array( __CLASS__, 'featured_image' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// Event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );
			
			// Event footer
			add_filter( 'tribe_events_single_event_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_single_event_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_single_event_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_single_event_after_footer', array( __CLASS__, 'after_footer' ), 1, 1 );

			// End single template
			add_filter( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Template
		public static function before_template( $post_id ) {
			$html = '<div id="tribe-events-content" class="tribe-events-single">';
			$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __( '&laquo; Back to Events', 'tribe-events-calendar-pro' ) .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_template');
		}
		// Event Title
		public static function before_the_title( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title' );
		}
		public static function the_title( $post_id ) {
			$html = the_title( '<h2 class="entry-title summary">', '</h2>', false );
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_the_title' );
		}
		public static function after_the_title( $post_id ) {
			$html = '';
			ob_start();

			// Single event meta ?>
			<div class="tribe-event-schedule tribe-clearfix">
				<h3><?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?><?php  if ( tribe_get_cost() ) :  echo '<span class="tribe-divider">|</span><span class="tribe-event-cost">'. tribe_get_cost( null, true ) .'</span>'; endif; ?></h3>
			</div>

			<?php
			$html .= ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title' );
		}
		// Event Notices
		public static function notices( $post_id ) {
			$html = tribe_events_the_notices( false );
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_notices' );
		}
		// Event Header
		public static function before_header( $post_id ){
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_header');
		}
		// Event Navigation
		public static function before_header_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Event Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_header_nav');
		}
		public static function header_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-events-nav-previous">' . tribe_get_prev_event_link( '&laquo; %title%' ) . '</li>';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-events-nav-next">' . tribe_get_next_event_link( '%title% &raquo;' );
			
			// Loading spinner
			$html .= '<img class="tribe-events-ajax-loading tribe-events-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-events-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_header_nav');
		}
		public static function after_header_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_header_nav');
		}
		public static function after_header( $post_id ){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_header');
		}
		// Event Featured Image
		public static function featured_image( $post_id ) {
			$html = '';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image( null, 'full' );
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_featured_image');
		}
		// Event Content
		public static function before_the_content( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_content' );
		}
		public static function the_content( $post_id ) {
			ob_start();

			// Single event content ?>

			<div class="tribe-single-event-description tribe-content">

			<?php // Event content
			the_content(); ?>

			</div><!-- .description -->

			<?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_the_content' );
		}
		public static function after_the_content( $post_id ) {
			$html = '';
			ob_start();

			// Single event calendar imports ?>
			<?php // iCal/gCal links
			if ( function_exists( 'tribe_get_single_ical_link' ) || function_exists( 'tribe_get_gcal_link' ) ) {
				echo '<div class="tribe-event-cal-links">';
					// gCal link
					if ( function_exists( 'tribe_get_gcal_link' ) ) {
						echo '<a class="tribe-events-gcal tribe-events-button" href="' . tribe_get_gcal_link() . '" title="' . __( 'Add to Google Calendar', 'tribe-events-calendar' ) . '">' . __( '+ Google Calendar', 'tribe-events-calendar' ) . '</a>';
					}
					// iCal link
					if ( function_exists( 'tribe_get_single_ical_link' ) ) {
						echo '<a class="tribe-events-ical tribe-events-button" href="' . tribe_get_single_ical_link() . '">' . __( '+ iCal Import', 'tribe-events-calendar' ) . '</a>';
					}
				echo '</div><!-- .tribe-event-cal-links -->';
			} ?>

			<?php
			$html .= ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_content' );
		}
		// Event Meta
		public static function before_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_meta' );
		}

		public static function the_meta( $post_id ) {

			$event_id = get_the_ID();
			$skeleton_mode = apply_filters( 'tribe_events_single_event_the_meta_skeleton', false, $event_id ) ;
			$group_venue = apply_filters( 'tribe_events_single_event_the_meta_group_venue', false, $event_id );

			$html = '<div class="tribe-events-event-meta tribe-clearfix">';

			if ( $skeleton_mode ) {

				// show all visible meta_groups in skeleton view
				$html .= tribe_get_the_event_meta();

			} else {

				// Event Details
				$html .= tribe_get_meta_group( 'tribe_event_details' );

				// When there is no map show the venue info up top
				if ( ! $group_venue && ! tribe_embed_google_map( $event_id ) ) {
					// Venue Details
					$html .= tribe_get_meta_group( 'tribe_event_venue' );
					$group_venue = false;
				} else if ( ! $group_venue && ! tribe_has_organizer( $event_id ) && tribe_address_exists( $event_id ) && tribe_embed_google_map( $event_id ) ) {
					$html .= sprintf( '%s<div class="tribe-events-meta-group">%s</div>',
						tribe_get_meta_group( 'tribe_event_venue' ),
						tribe_get_meta( 'tribe_venue_map' )
					);
					$group_venue = false;
				} else {
					$group_venue = true;
				}

				// Organizer Details
				if ( tribe_has_organizer( $event_id ) ) {
					$html .= tribe_get_meta_group( 'tribe_event_organizer' );
				}

				$html .= apply_filters( 'tribe_events_single_event_the_meta_addon', '', $event_id );

			}

			$html .= '</div><!-- .tribe-events-event-meta -->';

			if ( ! $skeleton_mode && $group_venue ) {
				// If there's a venue map and custom fields or organizer, show venue details in this seperate section

				$html .= apply_filters( 'tribe_events_single_event_the_meta_venue_row', sprintf( '<div class="tribe-event-single-section tribe-events-event-meta tribe-clearfix">%s%s</div>',
						tribe_get_meta_group( 'tribe_event_venue' ),
						tribe_get_meta( 'tribe_venue_map' )
					) );
			}

			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_the_meta' );
		}
		public static function after_the_meta( $post_id ) {
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
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_meta' );
		}
		// Event Footer
		public static function before_footer( $post_id ){
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_footer');
		}
		// Event Navigation
		public static function before_footer_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Event Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_footer_nav');
		}
		public static function footer_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-events-nav-previous">'. tribe_get_prev_event_link( '&laquo; %title%' ) .'</li>';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-events-nav-next">'. tribe_get_next_event_link( '%title% &raquo;' ) .'</li><!-- .tribe-events-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_footer_nav');
		}
		public static function after_footer_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_footer_nav');
		}
		public static function after_footer( $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_footer');
		}
		// After Single Template
		public static function after_template( $post_id ) {
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_template' );
		}
	}
	Tribe_Events_Single_Event_Template::init();
}