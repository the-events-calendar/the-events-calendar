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

if ( !class_exists( 'Tribe_Events_Pro_Single_Organizer_Template' ) ) {
	class Tribe_Events_Pro_Single_Organizer_Template extends Tribe_Template_Factory {
		public static function init() {

			// setup the template for the meta group
			tribe_set_the_meta_template( 'tribe_event_organizer', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<address class="organizer-address">',
					'meta_after'=>'</address>',
					'meta_separator' => '<span class="tribe-divider">|</span>'
				), 'meta_group');

			// setup the template for the meta items
			tribe_set_the_meta_template( array(
					'tribe_event_organizer_phone',
					'tribe_event_organizer_email',
					'tribe_event_organizer_website'
				), array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<span class="%s">',
					'meta_after'=>'</span>'
				));

			// remove the title for the group & meta items
			tribe_set_meta_label('tribe_event_organizer', '', 'meta_group');
			tribe_set_meta_label( array( 
				'tribe_event_organizer_phone' => '',
				'tribe_event_organizer_email' => '',
				'tribe_event_organizer_website' => ''
				));

			// turn off the venue name in the group
			tribe_set_the_meta_visibility( 'tribe_event_organizer_name', false);

			// provide for meta actions before loading the template
			do_action('tribe_events_pro_single_organizer_meta_init' );
						
			// Remove the title from the list view
			add_filter( 'tribe_events_list_the_title', '__return_null', 2, 1 );

			// Remove the comments template
			add_filter('comments_template', array(__CLASS__, 'remove_comments_template') );
			
			// Start single organizer template
			add_filter( 'tribe_events_single_organizer_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Start single organizer
			add_filter( 'tribe_events_single_organizer_before_organizer', array( __CLASS__, 'before_organizer' ), 1, 1 );

			// Organizer map
			add_filter( 'tribe_events_single_organizer_featured_image', array( __CLASS__, 'featured_image' ), 1, 1 );
			add_filter( 'tribe_events_single_organizer_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// Organizer meta
			add_filter( 'tribe_events_single_organizer_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_organizer_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_organizer_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// End single organizer
			add_filter( 'tribe_events_single_organizer_after_organizer', array( __CLASS__, 'after_organizer' ), 1, 1 );

			// Load up the event list
			add_filter( 'tribe_events_single_organizer_upcoming_events', array( __CLASS__, 'upcoming_events' ), 1, 1 );

			// End single organizer template
			add_filter( 'tribe_events_single_organizer_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}

		public static function remove_comments_template($template) {
			remove_filter('comments_template', array(__CLASS__, 'remove_comments_template') );
			return TribeEvents::instance()->pluginPath . 'admin-views/no-comments.php';
		}

		// Start Single organizer Template
		public static function before_template( $post_id ) {
			$html = '<div id="tribe-events-content" class="tribe-events-organizer">';
						$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __( '&larr; Back to Events', 'tribe-events-calendar-pro' ) .'</a></p>';			
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_template' );
		}
		// Start Single organizer
		public static function before_organizer( $post_id ) {
			$html = '<div class="tribe-events-organizer-meta tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_organizer' );
		}
		// Organizer Featured Image
		public static function featured_image( $post_id ){
			$html = '';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'full');
			}			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_organizer_featured_image');
		}
		// Organizer Title
		public static function the_title( $post_id ){
			$html = the_title('<h2 class="entry-title summary">','</h2>', false);
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_the_title' );
		}
		// Organizer Meta
		public static function before_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_before_the_meta' );
		}
		public static function the_meta( $post_id ) {

			$content = get_the_content();
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);

			$html = sprintf('%s%s',
				tribe_get_meta_group( 'tribe_event_organizer' ),
				!empty($content) ? '<div class="tribe-organizer-description tribe-content">' . $content . '</div>' : ''
				);

/*
			ob_start();
?>
			<address class="organizer-address">
				<?php if ( tribe_get_organizer_phone() ) : // organizer phone ?>
	 				<span class="vcard tel"><?php echo tribe_get_organizer_phone(); ?></span>
	 			<?php endif; ?>
	 			<?php if ( tribe_get_organizer_phone() && tribe_get_organizer_link( get_the_ID(), false, false )  ) : // organizer phone ?>
	 				<span class="tribe-divider">|</span>
	 			<?php endif; ?>
	 			<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) ) : // organizer website ?>
	 				<span class="vcard author fn org"><?php echo tribe_get_organizer_website_link( $post_id ); ?></span>
	 			<?php endif; ?>
	 			<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) &&  tribe_get_organizer_email() ) : // organizer phone ?>
	 				<span class="tribe-divider">|</span>
	 			<?php endif; ?>	 			
	 			<?php if ( tribe_get_organizer_email() ) : // organizer email ?>
	 				<span class="vcard email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></span>
	 			<?php endif; ?>
 			</address>
			<?php if ( get_the_content() != '' ): // Organizer content ?>
				<div class="organizer-description">	
					<?php the_content(); ?>
				</div>	
 			<?php endif ?> 			
<?php
			$html = ob_get_clean();
			*/
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

			$html = sprintf( 
				tribe_include_view_list( $args )
				);
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_upcoming_events' );
		}
		// End Single Organizer Template
		public static function after_template( $post_id ) {
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_organizer_after_template' );
		}
	}
	Tribe_Events_Pro_Single_Organizer_Template::init();
}
