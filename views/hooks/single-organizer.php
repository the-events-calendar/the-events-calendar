<?php
/**
 *
 *
 * @for Single organizer Template
 * This file contains the hook logic required to create an effective single organizer view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Single_organizer_Template' ) ) {
	class Tribe_Events_Pro_Single_organizer_Template extends Tribe_Template_Factory {
		public static function init() {
			// Start single organizer template
			add_filter( 'tribe_events_single_organizer_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Start single organizer
			add_filter( 'tribe_events_single_organizer_before_organizer', array( __CLASS__, 'before_organizer' ), 1, 1 );

			// organizer map
			add_filter( 'tribe_events_single_organizer_featured_image', array( __CLASS__, 'featured_image' ), 1, 1 );

			// organizer meta
			add_filter( 'tribe_events_single_organizer_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_organizer_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_organizer_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// End single organizer
			add_filter( 'tribe_events_single_organizer_after_organizer', array( __CLASS__, 'after_organizer' ), 1, 1 );

			// load up the event list
			add_filter( 'tribe_events_single_organizer_upcoming_events', array( __CLASS__, 'upcoming_events' ), 1, 1 );

			// End single organizer template
			add_filter( 'tribe_events_single_organizer_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single organizer Template
		public static function before_template( $post_id ) {
			$html = '<div id="tribe-events-content" class="tribe-events-organizer">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_template' );
		}
		// Start Single organizer
		public static function before_organizer( $post_id ) {
			$html = '<div class="tribe-events-organizer-meta">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_organizer' );
		}
		// organizer Map
		public static function featured_image( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_featured_image' );
		}
		// organizer Meta
		public static function before_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_the_meta' );
		}
		public static function the_meta( $post_id ) {
			ob_start();
?>

			<dt><?php echo __( 'Name:', 'tribe-events-calendar-pro' ); ?></dt>
			<dd class="vcard fn org"><?php the_title(); ?></dd>

			<?php if ( tribe_get_organizer_phone() ) : // organizer phone ?>
				<dt><?php echo __( 'Phone:', 'tribe-events-calendar-pro' ); ?></dt>
 				<dd class="vcard tel"><?php echo tribe_get_organizer_phone(); ?></dd>
 			<?php endif; ?>
 			<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) ) : // organizer phone ?>
				<dt><?php echo __( 'Email:', 'tribe-events-calendar-pro' ); ?></dt>
 				<dd class="vcard author fn org"><?php echo tribe_get_organizer_link(); ?></dd>
 			<?php endif; ?>
 			<?php if ( tribe_get_phone() ) : // organizer phone ?>
				<dt><?php echo __( 'Website:', 'tribe-events-calendar-pro' ); ?></dt>
 				<dd class="vcard email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
 			<?php endif; ?>


<?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_the_meta' );
		}
		public static function after_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_after_the_meta' );
		}
		// End Single organizer
		public static function after_organizer( $post_id ) {
			$html = '</div><!-- .tribe-events-organizer-meta -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_after_organizer' );
		}

		// Event List View
		public static function upcoming_events( $organizer_id ) {
			global $post;
			$args = array(
				'organizer' => $post->ID,
				'eventDisplay' => 'upcoming' );

			$html = sprintf( '<h3 class="tribe-events-upcoming">%s <span>%s</span></h3> %s',
				__( 'Upcoming events organized by', 'tribe-events-calendar-pro' ),
				$post->post_title,
				tribe_include_view_list( $args )
				);
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_upcoming_events' );
		}

		// End Single organizer Template
		public static function after_template( $post_id ) {
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_after_template' );
		}
	}
	Tribe_Events_Pro_Single_organizer_Template::init();
}
