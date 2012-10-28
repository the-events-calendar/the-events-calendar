<?php
/**
 * @for Week Grid Template
 * This file contains the hook logic required to create an effective week grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
 /*
 	@Samuel
 	Raw Wireframe: https://central.tri.be/attachments/54643/weekview.1.jpg
 	JS Notes
 	
 	All Day
 	You'll want to set up a bunch of events to get the different scenarios. Basically we need
 	the js to utilize the tribe-dayspan# class and then make that all day event span the proper
 	amount of days. Though this could happen with CSS, but don't think it can b/c of fluid.
 	Lastly on this, I've set a min-height on all-day columns of 60px, but you'll need to make sure
 	that all the columns keep the height of the tallest day "column".
 	
 	Regular Events
 	Regular events have several things going on. 
 	First: obviously based on the duration class for each event, each needs to get properly
 		   positioned vertically in it's column.
 	Second: we have two special cases where some events will have Same Time and/or other will
 			be Overlapping events. For Same Time events, ignore the mockup and go with how the
 			Overlapping events do it, where each consecutive Same Time event gets it's width decreased
 			a bit (something like this: width: 80%; I've already setup the following: right: 0; left: auto;)
 			and the events still overlap to save column real-estate.
 			For Overlapping events, same exact thing really.
 			
 	Third: tooltips. We are needing to implement onclick due to the nature of Same Time/Overlapping. And we have to
 		   be careful about overflow: hidden as it cuts of the tooltip. So, the title/content of the events we see
 		   in the grid should never overflow the grey bgd container, which for now has a height of 30px. So if you could
 		   use jquery to set the height of the events container used to position the parent wrapper on:
 		   .tribe-grid-body .hentry.vevent as well that would be aweomse–I already set overflow: hidden on it.
 		   So I did add the tribe-nudge-right bit or whatever, and have the tooltip markup below, if you could implement
 		   the onclick I'd appreciate it :)
 			
 	Let me know if you have questions! Thanks Samuel!
 			
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Week_Template')){
	class Tribe_Events_Week_Template extends Tribe_Template_Factory {

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_week_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// List pagination
			add_filter( 'tribe_events_week_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_week_the_header', array( __CLASS__, 'the_header' ), 1, 1 );
			add_filter( 'tribe_events_week_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// Start list loop
			add_filter( 'tribe_events_week_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );

			add_filter( 'tribe_events_week_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );
	
			// End list loop
			add_filter( 'tribe_events_week_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
				// End list template
			add_filter( 'tribe_events_week_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public function before_template( $post_id ){
			ob_start();
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
			?>
			<div id="tribe-events-content" class="tribe-events-week-grid">
				<title><?php wp_title(); ?></title>
			<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_template');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_loop');
		}
		public function inside_before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_inside_before_loop');
		}

		public function the_grid() {

			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			$start_of_week = tribe_get_first_week_day( $wp_query->get('start_date'));
			$week_length = 7; // days of the week
			$today = Date('Y-m-d',strtotime('today'));
			$events->all_day = array();
			$events->daily = array();
			$events->hours = array('start'=>null,'end'=>null);
			foreach($wp_query->posts as $event){
				if( $event->tribe_is_allday ){
					$events->all_day[] = $event;
				} else {
					$start_hour = Date('G',strtotime($event->EventStartDate));
					$end_hour = Date('G',strtotime($event->EventEndDate));
					if( is_null($events->hours['start']) || $start_hour < $events->hours['start'] ) {
						$events->hours['start'] = $start_hour;
					}
					if( is_null($events->hours['end']) || $end_hour > $events->hours['end'] ){
						$events->hours['end'] = $end_hour;
					}
					$events->daily[] = $event;
				}
			}

			ob_start();
?>
<div class="tribe-events-grid clearfix">

	<?php // Header "Row" ?>
	<div class="tribe-grid-header clearfix">
	
		<div class="column first">
			<span class="tribe-events-visuallyhidden"><?php _e('Hours', 'tribe-events-calendar-pro'); ?></span>
		</div>
		
		<div class="tribe-grid-content-wrap">
		
			<?php
			for( $n = 0; $n < $week_length; $n++ ) {
				$day = Date('Y-m-d', strtotime($start_of_week . " +$n days"));
				$header_class = ($day == $today) ? 'tribe-week-today' : '';
				printf('<div title="%s" class="column %s"><a href="%s" rel="bookmark">%s</a></div><!-- header column -->',
					$day,
					$header_class,
					trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($start_of_week . " +$n days") ) ),
					Date('D jS', strtotime($start_of_week . " +$n days"))
					);
			} ?>
		
		</div><!-- .tribe-grid-content-wrap -->
		
	</div><!-- .tribe-grid-header -->
	
	<?php // All Day "Row" ?>
	<div class="tribe-grid-allday clearfix">
	
		<div class="column first"><?php _e('All Day', 'tribe-events-calendar-pro'); ?></div>
		
		<div class="tribe-grid-content-wrap">
		
			<?php
			$placeholder = 0;
			for( $n = 0; $n < $week_length; $n++ ) {
				$day = Date('Y-m-d', strtotime($start_of_week . " +$n days"));
				$header_class = ($day == $today) ? 'tribe-week-today' : '';
				printf('<div title="%s" class="column %s">', Date('Y-m-d', strtotime($start_of_week . " +$n days")), $header_class);
				if( $placeholder > 0 ) {
					for( $placeholder_i = 0; $placeholder_i <= $placeholder; $placeholder_i++ ) {
						echo '<div class="tribe-event-placeholder">placeholder</div>';
					}
				}
				foreach( $events->all_day as $event ){
					if( Date('Y-m-d',strtotime($event->EventStartDate)) == $day ){
						$span_class = '';
						$days_between = tribe_get_days_between($event->EventStartDate, $event->EventEndDate);
						if($days_between > 0) {
							$day_span_length = $days_between > ($week_length - $n) ? ($week_length - $n) : $days_between;
							$span_class = 'tribe-dayspan' . $day_span_length;
						}
						printf('<div id="tribe-events-event-'. $event->ID .'" class="%s" data-hour="all-day"><div><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3>',
							'hentry vevent ' . $span_class,
							get_permalink( $event->ID ),
							$event->post_title
						); ?>
						
						<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
							<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
							<div class="tribe-events-event-body">
								<div class="duration">
									<?php
									/*
									@Tim
									@Comment: this is what actually needs to get implemented, I was having trouble
										   	  with getting the right bits in same for grid events as well, see below.
										   	  And if you could make sure all the tooltip content tags are correct,
										   	  I'd appreciate it!
										 
										<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $start ); ?>">
							<?php if ( !empty( $start ) )	echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start );
							if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
								echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
							</abbr><!-- .dtstart -->
							<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $end ); ?>">
							<?php if ( !empty( $end )  && $start !== $end ) {
								if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
									$time_format = get_option( 'time_format', 'g:i a' );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
										echo " – " . date_i18n( $time_format, $end );
								} else {
									echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), $end );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
									 	echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />';
								}
							} ?>
							</abbr><!-- .dtend -->
							
							*/ ?>
									<abbr class="tribe-events-abbr updated published dtstart" title="Our Start Date">
										Our Start Date
									</abbr><!-- .dtstart -->
									<abbr class="tribe-events-abbr dtend" title="Our End Date">
										– Our End Date
									</abbr><!-- .dtend -->
								</div><!-- .duration -->
						
								<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
									<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75,75 ) );?></div>
								<?php } ?>
						
								<p class="entry-summary description"><?php echo has_excerpt() ? TribeEvents::truncate( $$event->post_excerpt ) : TribeEvents::truncate( get_the_content(), 30 ); ?></p>

							</div><!-- .tribe-events-event-body -->
							<span class="tribe-events-arrow"></span>
						</div><!-- .tribe-events-tooltip -->
						<?php
						echo '</div></div>';
						
					}
				}
				echo '</div><!-- allday column -->';
			} ?>
		
		</div><!-- .tribe-grid-content-wrap -->
		
	</div><!-- .tribe-grid-allday -->
	
	<!--
		@Tim
		I know there is a prettier "loopier" way to do this :) We basically need a
		.tribe-week-grid-block for each hour.
	-->
	<?php // Grid "Rows" ?>
	<div class="tribe-week-grid-outer-wrap">
		<div class="tribe-week-grid-inner-wrap">
			<?php 
			// sam messing aboot
			for( $grid_blocks = $events->hours['start']; $grid_blocks <= $events->hours['end']; $grid_blocks++ ) {
				printf( '<div class="tribe-week-grid-block" data-hour="%s"><div></div></div>', Date('G',mktime($grid_blocks)) );
			}

			?>			
		</div><!-- .tribe-week-grid-inner-wrap -->
	</div><!-- .tribe-week-grid-outer-wrap -->
	
	<?php // Content / Events "Rows" ?>
	<!-- Days of the week & hours & events -->
	<div class="tribe-grid-body clearfix">
	
		<?php // Hours ?>
		<div class="column tribe-week-grid-hours">
			<?php 

			for( $hour = $events->hours['start']; $hour <= $events->hours['end']; $hour++ ) {
				printf( '<div>%s</div>', Date('gA',mktime($hour)) );
			}

			?>
		</div><!-- tribe-week-grid-hours -->
		
		<?php // Content ?>
		<div class="tribe-grid-content-wrap">
		
			<?php // Our day columns?
			for( $n = 0; $n < $week_length; $n++ ) {
				$day = Date('Y-m-d', strtotime($start_of_week . " +$n days"));
				$header_class = ($day == $today) ? 'tribe-week-today' : '';
				printf('<div title="%s" class="column hfeed vcalendar %s">',
					Date('Y-m-d', strtotime($start_of_week . " +$n days")),
					$header_class
					);				
				foreach( $events->daily as $event ){
					if( Date('Y-m-d',strtotime($event->EventStartDate)) == $day ){
						$duration = ($event->EventDuration / 60);
						echo '<div id="tribe-events-event-'. $event->ID .'" duration="'. $duration .'" data-hour="' . Date('G',strtotime($event->EventStartDate)) . '" data-min="' . Date('i',strtotime($event->EventStartDate)) . '">';
						printf('<div class="hentry vevent"><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3></div>',
							get_permalink( $event->ID ),
							$event->post_title
							); ?>
							
						<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
							<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
							<div class="tribe-events-event-body">
								<div class="duration">
									<?php
									/*
									@Tim
									@Comment: this is what actually needs to get implemented, I was having trouble
										   	  with getting the right bits in same for grid events as well, see below.
										   	  And if you could make sure all the tooltip content tags are correct,
										   	  I'd appreciate it!
										 
										<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $start ); ?>">
							<?php if ( !empty( $start ) )	echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start );
							if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
								echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
							</abbr><!-- .dtstart -->
							<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $end ); ?>">
							<?php if ( !empty( $end )  && $start !== $end ) {
								if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
									$time_format = get_option( 'time_format', 'g:i a' );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
										echo " – " . date_i18n( $time_format, $end );
								} else {
									echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), $end );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
									 	echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />';
								}
							} ?>
							</abbr><!-- .dtend -->
							
							*/ ?>
									<abbr class="tribe-events-abbr updated published dtstart" title="Our Start Date">
										Our Start Date
									</abbr><!-- .dtstart -->
									<abbr class="tribe-events-abbr dtend" title="Our End Date">
										– Our End Date
									</abbr><!-- .dtend -->
								</div><!-- .duration -->
						
								<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
									<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75,75 ) );?></div>
								<?php } ?>
						
								<p class="entry-summary description"><?php echo has_excerpt() ? TribeEvents::truncate( $$event->post_excerpt ) : TribeEvents::truncate( get_the_content(), 30 ); ?></p>

							</div><!-- .tribe-events-event-body -->
							<span class="tribe-events-arrow"></span>
							<div style="display:none"><?php print_r($event); ?></div>
						</div><!-- .tribe-events-tooltip -->
						<?php
						
						
						echo '</div><!-- #tribe-events-event-'. $event->ID .' -->';
					}
				}
				echo '</div>';
			} ?>
		
		</div><!-- .tribe-grid-content-wrap -->
	
	</div><!-- .tribe-grid-body -->
	
</div><!-- .tribe-events-grid -->

<script>
	jQuery(document).ready(function($){
					
		function tribe_find_overlapped_events($week_events) {			    

			$week_events.each(function() {
				var $this = $(this);
				var $target = $(this).next();
				if($target.length){
					var tAxis = $target.offset();
					var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
					var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];			    
					var thisPos = $this.offset();
					var i_x = [thisPos.left, thisPos.left + $this.outerWidth()]
					var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

					if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0]) {
						$this.css({"left":"0","width":"75%"});
						$target.css({"right":"0","width":"75%"});
					}
				}

			});			
		}
					
		var $week_events = $(".tribe-grid-content-wrap .column > div[id*='tribe-events-event-']");
		
		$week_events.hide();
		
		$week_events.each(function() {
			var $this = $(this);			
			var event_hour = $this.attr("data-hour");
			if(event_hour == 'all-day') {
				$this.show();
			} else {
				var event_length = $this.attr("duration") - 14;	
				var event_min = $this.attr("data-min");
				var $event_target = $('.tribe-week-grid-block[data-hour="' + event_hour + '"]');
				var event_position = 
					$event_target.offset().top -
					$event_target.parent().offset().top - 
					$event_target.parent().scrollTop();
				event_position = parseInt(Math.round(event_position)) + parseInt(event_min);

				$this.css({"height":event_length + "px","top":event_position + "px"}).show();
			}
		});
		
		tribe_find_overlapped_events($week_events);
		
		var all_day_height = $(".tribe-grid-allday .tribe-grid-content-wrap").height();
		
		$(".tribe-grid-allday .column").height(all_day_height);
		
	});
</script>
		
    <?php 
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_the_grid');
		}

		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_loop');
		}

		public function before_header( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_pagination');
		}
		public function the_header( $post_id ){
			global $wp_query;
			// echo '<pre>';
			// print_r($wp_query->posts);
			// echo '</pre>';
			$current_week = tribe_get_first_week_day( $wp_query->get('start_date') );
			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$dropdown = ob_get_clean();

			// Display Week Navigation
			$html = sprintf('<div id="tribe-events-header"><h3 class="tribe-events-visuallyhidden">%s</h3><ul class="tribe-events-sub-nav"><li class="tribe-events-nav-prev"><a href="%s" rel="prev">%s</a></li><li>%s</li><li class="tribe-events-nav-next"><a href="%s" rel="next">%s</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="Loading events" /></li></ul></div>',
								__( 'Week Navigation', 'tribe-events-calendar' ),
								tribe_get_last_week_permalink( $current_week ),
								'&#x2190;' . tribe_get_previous_month_text(),
								$dropdown,
								tribe_get_next_week_permalink( $current_week ),
								tribe_get_next_month_text()  . '&#x2192;',
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_pagination');
		}
		public function after_header( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '';

			// iCal import button
			if( function_exists( 'tribe_get_ical_link' ) ){
				$html .= sprintf('<a class="tribe-events-ical tribe-events-button-grey" title="%s" href="%s">%s</a>',
					esc_attr( 'iCal Import', 'tribe-events-calendar' ),
					tribe_get_ical_link(),
					__( 'iCal Import', 'tribe-events-calendar' )
					);
			}
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_template');		
		}
	}
	Tribe_Events_Week_Template::init();
}
