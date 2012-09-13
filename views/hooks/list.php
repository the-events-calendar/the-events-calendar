<?php

/**
 * The abstracted view of the events list template.
 * This view contains the hooks and filters required to create an effective list view.
 *
 * You can recreate and ENTIRELY new list view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a list.php file in a tribe-events/ directory 
 * within your theme directory, which will override the /views/list.php.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_List_Template')){
	class Tribe_Events_List_Template extends Tribe_Template_Factory {
		function init(){
			// start list template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// list view buttons
			add_filter( 'tribe_events_list_the_view_buttons', array( __CLASS__, 'the_view_buttons' ), 1, 1 );
	
			// start list loop
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );
	
			// event start date
			add_filter( 'tribe_events_list_the_start_date', array( __CLASS__, 'the_start_date' ), 1, 1 );
	
			// event title
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );
	
			// event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );
	
			// end list loop
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_list_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
			// event notice
			add_filter( 'tribe_events_list_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// list pagination
			add_filter( 'tribe_events_list_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_prev_pagination', array( __CLASS__, 'prev_pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_next_pagination', array( __CLASS__, 'next_pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// end list template
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="upcoming">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_template');
		}
		// View buttons
		public function the_vew_buttons( $post_id ){
			$html = '';
			if(!tribe_is_day())
				$html .= '<div id="tribe-events-calendar-header" class="clearfix">';
				$html .= '<span class="tribe-events-calendar-buttons">';
				$html .= '<a class="tribe-events-button-on" href="'. tribe_get_listview_link() .'">'. _e( 'Event List', 'tribe-events-calendar' ) .'</a>';
				$html .= '<a class="tribe-events-button-off" href="'. tribe_get_gridview_link() .'">'. _e( 'Calendar', 'tribe-events-calendar' ) .'</a>';
				$html .= '</span><!-- .tribe-events-calendar-buttons -->';
				$html .= '</div><!-- #tribe-events-calendar-header -->';			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_view_buttons');
		}
		public function before_loop( $post_id ){
			$html = '<div id="tribe-events-loop" class="tribe-events-events post-list clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		
		// Start Loop
		if ( have_posts() ) :
		$hasPosts = true; $first = true;
		while ( have_posts() ) : the_post();
		global $more; $more = false;
		
		// Start Event
		public function inside_before_loop( $post_id ){
			$html = '<div id="post-'. the_ID() .'" '. post_class( 'tribe-events-event clearfix' ) .' itemscope itemtype="http://schema.org/Event">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop');
		}
		public function the_start_date( $post_id ){
			$html = '';
			if (tribe_is_new_event_day() && !tribe_is_day())
				$html .= '<h4 class="event-day">'. tribe_get_start_date( null, false ) .'</h4>';
			if (tribe_is_day() && $first) : $first = false;
				$html .= '<h4 class="event-day">'. tribe_event_format_date( strtotime( get_query_var( 'eventDate' ) ), false ) .'</h4>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_start_date');
		}
		
		public function the_title( $post_id ){
			$html = '<h2 class="entry-title" itemprop="name"><a href="'. tribe_get_event_link() .'" title="'. the_title_attribute( 'echo=0' ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}
		
		public function before_the_content( $post_id ){
			$html = '<div class="entry-content tribe-events-event-entry" itemprop="description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= get_the_excerpt();
			else
				$html .= get_the_content( $post_id );
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .tribe-events-event-entry -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
		}
		// Event Details
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_meta');
		}
		public function the_meta( $post_id ){
			$filter_name = 'tribe_events_list_the_meta';
			echo parent::debug( $filter_name );

?>
			<div class="tribe-events-event-list-meta" itemprop="location" itemscope itemtype="http://schema.org/Place">
				<table cellspacing="0">
				<?php if ( tribe_is_multiday() || !tribe_get_all_day() ): ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Start:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'End:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="endDate" content="<?php echo tribe_get_end_date(); ?>"><?php echo tribe_get_end_date(); ?></td>
					</tr>
				<?php else: ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Date:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php
				$venue = tribe_get_venue();
				if ( !empty( $venue ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Venue:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="name">
						<?php if( class_exists( 'TribeEventsPro' ) ): ?>
								<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
							<?php else: ?>
								<?php echo tribe_get_venue( get_the_ID() ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
				
				<?php
				$phone = tribe_get_phone();
				if ( !empty( $phone ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="telephone"><?php echo $phone; ?></td>
					</tr>
				<?php endif; ?>
				
				<?php if ( tribe_address_exists( get_the_ID() ) ) : ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Address:', 'tribe-events-calendar' ); ?><br />
						<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
							<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
						<?php endif; ?></td>
						<td class="tribe-events-event-meta-value"><?php echo tribe_get_full_address( get_the_ID() ); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php
				$cost = tribe_get_cost();
				if ( !empty( $cost ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
					</tr>
				<?php endif; ?>
				
				</table>
			</div><!-- .tribe-events-event-list-meta -->	
<?php
			echo parent::debug( $filter_name, false );
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_meta');
		}
		public function inside_after_loop( $post_id ){
			$html = '</div><!-- #post -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_after_loop');
		}
		
		// End Loop
		endwhile;
		
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_loop');
		}
		
		// If No Events
		else :
		
		// Template Notices
		public function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div><!-- .event-notices -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_notices');
		}
		// Specific Notices for this template below
?>
		<?php // Messages if currently no events
			$tribe_ecp = TribeEvents::instance();
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					if( tribe_is_upcoming() ) {
						$is_cat_message = sprintf( __( ' listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					} else if( tribe_is_past() ) {
						$is_cat_message = sprintf( __( ' listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					}
				}
			?>
			
			<?php if( tribe_is_day() ): ?>
				<?php printf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) ); ?>
			<?php endif; ?>

			<?php if( tribe_is_upcoming() ) { ?>
				<?php _e( 'No upcoming events', 'tribe-events-calendar' );
				echo !empty( $is_cat_message ) ? $is_cat_message : "."; ?>
			<?php } elseif( tribe_is_past() ) { ?>
				<?php _e( 'No previous events' , 'tribe-events-calendar' );
				echo !empty($is_cat_message) ? $is_cat_message : "."; ?>
			<?php } ?>
<?php
		endif;
		
		// Navigation
		public function before_pagination( $post_id ){
			$html = '</div><!-- #tribe-events-loop -->';
			$html .= '<div id="tribe-events-nav-below" class="tribe-events-nav clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_pagination');
		}
		// Display Previous Page Navigation
		public function prev_pagination( $post_id ){
			$html = '<div class="tribe-events-nav-previous">';
			if(tribe_is_upcoming() && get_previous_posts_link())
				$html .= previous_posts_link( '<span>'.__( '&laquo; Previous Events', 'tribe-events-calendar' ).'</span>' );
			elseif(tribe_is_upcoming() && !get_previous_posts_link())
				$html .= '<a href="'. tribe_get_past_link() .'"><span>'. _e( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</span></a>';
			elseif(tribe_is_past() && get_next_posts_link()) 
				$html .= next_posts_link( '<span>'.__( '&laquo; Previous Events', 'tribe-events-calendar' ).'</span>' );
			$html .= '</div><!-- .tribe-events-nav-previous -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_prev_pagination');
		}
		// Display Next Page Navigation
		public function next_pagination( $post_id ){
			$html = '<div class="tribe-events-nav-next">';
			if(tribe_is_upcoming() && get_next_posts_link())
				$html .= next_posts_link( '<span>'.__( 'Next Events &raquo;', 'tribe-events-calendar' ).'</span>' );
			elseif(tribe_is_past() && get_previous_posts_link())
				$html .= previous_posts_link( '<span>'.__( 'Next Events &raquo;', 'tribe-events-calendar' ).'</span>' );
			elseif(tribe_is_past() && !get_previous_posts_link()) 
				$html .= '<a href="'. tribe_get_upcoming_link() .'"><span>'. _e( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</span></a>';
			$html .= '</div><!-- .tribe-events-nav-next -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_next_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '</div><!-- #tribe-events-nav-below -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_pagination');
		}
		public function after_template( $post_id ){
			$html = '';
			if (!empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a title="'. esc_attr_e( 'iCal Import', 'tribe-events-calendar' ) .'" class="ical" href="'. tribe_get_ical_link() . _e( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}