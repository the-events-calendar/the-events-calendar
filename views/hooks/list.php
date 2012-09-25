<?php
/**
 * @for Events List Template
 * This file contains the hook logic required to create an effective event list view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_List_Template')){
	class Tribe_Events_List_Template extends Tribe_Template_Factory {

		private $first = true;

		public static function init(){
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
	
			// event notices
			add_filter( 'tribe_events_list_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// list pagination
			add_filter( 'tribe_events_list_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// end list template
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="upcoming">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_template');
		}
		// List View Buttons
		public function the_view_buttons( $post_id ){
			$html = '';
			if(!tribe_is_day())
				$html .= '<div id="tribe-events-calendar-header" class="clearfix">';
				$html .= '<span class="tribe-events-calendar-buttons">';
				$html .= '<a class="tribe-events-button-on" href="'. tribe_get_listview_link() .'">'. __( 'Event List', 'tribe-events-calendar' ) .'</a>';
				$html .= '<a class="tribe-events-button-off" href="'. tribe_get_gridview_link() .'">'. __( 'Calendar', 'tribe-events-calendar' ) .'</a>';
				$html .= '</span><!-- .tribe-events-calendar-buttons -->';
				$html .= '</div><!-- #tribe-events-calendar-header -->';			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_view_buttons');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			$html = '<div id="tribe-events-loop" class="tribe-events-events post-list clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		public function inside_before_loop( $post_id ){
			
			// Get our wrapper classes
			$string = '';
			$tribe_cat_ids = tribe_get_event_cat_ids( $post_id ); 
			foreach( $tribe_cat_ids as $tribe_cat_id ) { 
				$string .= 'tribe-events-category-'. $tribe_cat_id .' '; 
			}
			$tribe_classes_default = 'clearfix tribe-events-event';
			$tribe_classes_venue = tribe_get_venue_id() ? 'tribe-events-venue-'. tribe_get_venue_id() : '';
			$tribe_classes_organizer = tribe_get_organizer_id() ? 'tribe-events-organizer-'. tribe_get_organizer_id() : '';
			$tribe_classes_categories = $string;
			$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;
			
			$html = '<div id="post-'. get_the_ID() .'" class="'. $class_string .'" itemscope itemtype="http://schema.org/Event">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop');
		}
		// Event Start Date
		public function the_start_date( $post_id ){
			$html = '';
			if (tribe_is_new_event_day() && !tribe_is_day())
				$html .= '<h4 class="event-day">'. tribe_get_start_date() .'</h4>';
			if (tribe_is_day() && $this->first) {
				$this->first = false;
				$html .= '<h4 class="event-day">'. tribe_event_format_date( strtotime( get_query_var( 'eventDate' ) ), false ) .'</h4>';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_start_date');
		}
		// Event Title
		public function the_title( $post_id ){
			$html = '<h2 class="entry-title" itemprop="name"><a href="'. tribe_get_event_link() .'" title="'. the_title_attribute( 'echo=0' ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}
		// Event Content
		public function before_the_content( $post_id ){
			$html = '<div class="entry-content tribe-events-event-entry" itemprop="description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. TribeEvents::truncate($post_id->post_excerpt) .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 80) .'</p>';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .entry-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
		}
		// Event Meta
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
?>
			<div class="tribe-events-event-list-meta" itemprop="location" itemscope itemtype="http://schema.org/Place">
				<table cellspacing="0">
				<?php if ( tribe_is_multiday() || !tribe_get_all_day() ): ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Start:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'End:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="endDate" content="<?php echo tribe_get_end_date(); ?>"><?php echo tribe_get_end_date(); ?></td>
					</tr>
				<?php else: ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Date:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php
				$venue = tribe_get_venue();
				if ( !empty( $venue ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Venue:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="name">
						<?php if( class_exists( 'TribeEventsPro' ) ): ?>
								<?php tribe_get_venue_link( $post_id, class_exists( 'TribeEventsPro' ) ); ?>
							<?php else: ?>
								<?php echo tribe_get_venue( $post_id ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
				
				<?php
				$phone = tribe_get_phone();
				if ( !empty( $phone ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Phone:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="telephone"><?php echo $phone; ?></td>
					</tr>
				<?php endif; ?>
				
				<?php if ( tribe_address_exists( $post_id ) ) : ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Address:', 'tribe-events-calendar' ); ?><br />
						<?php if( get_post_meta( $post_id, '_EventShowMapLink', true ) == 'true' ) : ?>
							<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php __( 'Google Map', 'tribe-events-calendar' ); ?></a>
						<?php endif; ?></td>
						<td class="tribe-events-event-meta-value"><?php echo tribe_get_full_address( $post_id ); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php
				$cost = tribe_get_cost();
				if ( !empty( $cost ) ) :
				?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php echo __( 'Cost:', 'tribe-events-calendar' ); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
					</tr>
				<?php endif; ?>
				
				</table>
			</div><!-- .tribe-events-event-list-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_meta');
		}
		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '</div><!-- #post -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_loop');
		}
		// Event Notices
		public function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div><!-- .event-notices -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_notices');
		}
		// List Pagination
		public function before_pagination( $post_id ){
			$html = '</div><!-- #tribe-events-loop -->';
			$html .= '<div id="tribe-events-nav-below" class="tribe-events-nav clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_pagination');
		}
		public function pagination( $post_id ){
			// Display Previous Page Navigation
			$html = '<div class="tribe-events-nav-previous">';
			if(tribe_is_upcoming() && get_previous_posts_link())
				$html .= get_previous_posts_link( '<span>'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</span>' );
			elseif(tribe_is_upcoming() && !get_previous_posts_link())
				$html .= '<a href="'. tribe_get_past_link() .'"><span>'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</span></a>';
			elseif(tribe_is_past() && get_next_posts_link()) 
				$html .= get_next_posts_link( '<span>'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</span>' );
			$html .= '</div><!-- .tribe-events-nav-previous -->';
			// Display Next Page Navigation
			$html .= '<div class="tribe-events-nav-next">';
			if(tribe_is_upcoming() && get_next_posts_link())
				$html .= get_next_posts_link( '<span>'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</span>' );
			elseif(tribe_is_past() && get_previous_posts_link())
				$html .= get_previous_posts_link( '<span>'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</span>' );
			elseif(tribe_is_past() && !get_previous_posts_link()) 
				$html .= '<a href="'. tribe_get_upcoming_link() .'"><span>'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</span></a>';
			$html .= '</div><!-- .tribe-events-nav-next -->';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '</div><!-- #tribe-events-nav-below -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_pagination');
		}
		// End List Template
		public function after_template( $hasPosts = false, $post_id ){
			$html = '';
			if (!empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a title="'. esc_attr( 'iCal Import', 'tribe-events-calendar' ) .'" class="ical" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
			$html .= '</div><!-- #tribe-events-content -->';
			$html .= '<div class="tribe-clear"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}