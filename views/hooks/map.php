<?php
/**
 * @for Map Template
 * This file contains the hook logic required to create an effective map view.
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Map_Template')){
	class Tribe_Events_Map_Template extends Tribe_Template_Factory {
		public static function init(){

			// Start map template
			add_filter( 'tribe_events_map_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Map
			add_filter( 'tribe_events_map_before_the_map', array( __CLASS__, 'before_the_map' ), 1, 1 );
			add_filter( 'tribe_events_map_the_map', array( __CLASS__, 'the_map' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_map', array( __CLASS__, 'after_the_map' ), 1, 1 );
	
			// Options
			add_filter( 'tribe_events_map_before_the_options', array( __CLASS__, 'before_the_options' ), 1, 1 );
			add_filter( 'tribe_events_map_the_options', array( __CLASS__, 'the_options' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_options', array( __CLASS__, 'after_the_options' ), 1, 1 );
	
//			// Results
			add_filter( 'tribe_events_map_before_the_results', array( __CLASS__, 'before_the_results' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_results', array( __CLASS__, 'after_the_results' ), 1, 1 );

//			// Pagination
			add_filter( 'tribe_events_map_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_map_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_map_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );


			// End map template
			add_filter( 'tribe_events_map_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
			
			remove_filter( 'tribe_events_filter_view_before_template', array( __CLASS__, 'beginSidebar' ) );
			remove_filter( 'tribe_events_filter_view_filters', array( __CLASS__, 'displayFilters' ) );
			remove_filter( 'tribe_events_filter_view_before_filters', array( __CLASS__, 'formTag' ) );
			remove_filter( 'tribe_events_filter_view_after_filters', array( __CLASS__, 'endFormTag' ) );
			remove_filter( 'tribe_events_filter_view_after_template', array( __CLASS__, 'endSidebar' ) );
			
		}
		// Start Map Template
		public function before_template( $post_id ){
			$html = '<div id="tribe-geo-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_template');
		}
		// Map
		public function before_the_map( $post_id ){
			$html = '<div id="tribe-geo-map-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_map');
		}
		public function the_map( $post_id ){
			$html = '<div id="tribe-geo-loading"><span></span></div>';
			$html .= '<div id="tribe-geo-map"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_map');
		}
		public function after_the_map( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_map');
		}
		// Options
		public function before_the_options( $post_id ){
			$html = '<div id="tribe-geo-options">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_options');
		}
		public function the_options( $post_id ){
			$html = '<h2>'. __( 'Refine your search:', 'tribe-events-calendar-pro' ) .'</h2>';
			$html .= '<div id="tribe-geo-links"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_options');
		}
		public function after_the_options( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_options');
		}
		// Start Results
		public function before_the_results( $post_id ){
			$html = '<div id="tribe-geo-results">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}
		public function the_results( $post_id ){
			ob_start();
?>
		<div class="tribe-geo-result-entry<?php if ( $result_count == $counter ) echo ' tribe-geo-result-last'; ?>">
			<?php if ( has_post_thumbnail( $event->ID ) ) {
				$noThumb = false;
				$result_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $event->ID ), 'medium' );
			?>
				<div class="tribe-geo-result-thumb">
					<a href="<?php echo tribe_get_event_link( $event->ID ) ?>" title="<?php echo $event->post_title; ?>"><img src="<?php echo $result_thumb[0]; ?>" alt="<?php echo $event->post_title; ?>"/></a>
				</div>
			<?php } ?>
	
			<div class="tribe-geo-result-data<?php if( $noThumb ) echo ' tribe-geo-no-thumb'; ?>">
				<h2><a href="<?php echo tribe_get_event_link( $event->ID ) ?>"><?php echo $event->post_title; ?></a></h2>
				<?php if ( tribe_get_cost( $event->ID ) ) { ?>
					<div class="tribe-geo-result-cost">
						<span><?php echo tribe_get_cost( $event->ID ); ?></span>
					</div>
				<?php } ?>
				
				<div class="tribe-clear"></div>
		
				<?php if ( tribe_is_multiday( $event->ID ) ) { ?>
					<span class="tribe-geo-result-date"><?php echo tribe_get_start_date( $event->ID, false ) . ' @ ' . tribe_get_start_date( $event->ID, false, 'g:i A' ) . ' - ' . tribe_get_end_date( $event->ID, false ); ?></span>
				
				<?php } else { ?>
					<span class="tribe-geo-result-date"><?php echo tribe_get_start_date( $event->ID, false ) . ' @ ' . tribe_get_start_date( $event->ID, false, 'g:i A' ); ?></span>
				<?php } ?>
		
				<span class="tribe-geo-result-venue">
					<?php if ( $this->is_geoloc_query() ) { ?>
						<strong>[<?php echo tribe_get_distance_with_unit( $event->distance ); ?>]</strong>
					<?php } ?>
					<?php echo  tribe_get_venue( $event->ID ) . ', ' . tribe_get_full_address( $event->ID ); ?>
				</span>

				<div class="tribe-geo-result-excerpt">
					<?php if ( has_excerpt( $event->ID ) )
						echo '<p>' . TribeEvents::truncate( $event->post_excerpt ) . '</p>'; else
						echo '<p>' . TribeEvents::truncate( $event->post_content, 80 ) . '</p>';
					?>
				</div>
			</div>
		</div>
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_results');
		}
		// Pagination
		public function before_pagination( $post_id ){
			$html = '<div class="tribe-events-loop-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_pagination');
		}
		public function pagination( $post_id ){
			$html = '<a href="#" id="tribe_map_paged_prev" class="tribe_map_paged">'. __('<< Previous Events') .'</a>';
			$html .= '<a href="#" id="tribe_map_paged_next" class="tribe_map_paged">'. __('Next Events >>') .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_pagination');
		}
		// End Results
		public function after_the_results( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_results');		
		}
		// End Map Template
		public function after_template( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_template');		
		}
	}
	Tribe_Events_Map_Template::init();
}