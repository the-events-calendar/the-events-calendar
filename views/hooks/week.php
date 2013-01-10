<?php
/**
 *
 *
 * @for Week Grid Template
 * This file contains the hook logic required to create an effective week grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Week_Template' ) ) {
	class Tribe_Events_Week_Template extends Tribe_Template_Factory {

		public static function init() {
			// enqueue needed styles
			Tribe_PRO_Template_Factory::asset_package( 'ajax-weekview' );

			// Start week template
			add_filter( 'tribe_events_week_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			add_filter( 'tribe_events_week_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			
			// Week header
			add_filter( 'tribe_events_week_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_week_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_week_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_week_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_week_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Start week loop
			add_filter( 'tribe_events_week_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );

			add_filter( 'tribe_events_week_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );

			// End week loop
			add_filter( 'tribe_events_week_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
			
			// Week footer
			add_filter( 'tribe_events_week_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_week_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_week_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
			add_filter( 'tribe_events_week_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_week_after_footer', array( __CLASS__, 'after_footer' ), 1, 1 );

			// End week template
			add_filter( 'tribe_events_week_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start Week Template
		public static function before_template( $post_id ) {
			ob_start();
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
?>
			<div id="tribe-events-content" class="tribe-events-week-grid">
			<?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_before_template' );
		}
		// Week Title
		public static function the_title() {
			global $wp_query;

			// because we can't trust tribe_get_events_title will be set when run via AJAX
			$title = sprintf( __( 'week starting %s', 'tribe-events-calendar-pro' ),
				date( "l, F jS Y", strtotime( tribe_get_first_week_day( $wp_query->get( 'start_date' ) ) ) )
			);

			$html = sprintf( '<h2 class="tribe-events-page-title">'. __( 'Events for ', 'tribe-events-calendar-pro' ) .'%s</h2>',
				$title
			);

			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_the_title' );
		}
		// Week Header
		public static function before_header( $post_id ){
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '" data-date="'. $current_week .'">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_header');
		}
		// Week Navigation
		public static function before_header_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Week Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_header_nav');
		}
		public static function header_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' -7 days' ) ) .'" href="'. tribe_get_last_week_permalink( $current_week ) .'" rel="prev">&larr; '. __( 'Previous Week', 'tribe-events-calendar-pro' ) .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) .'" href="'. tribe_get_next_week_permalink( $current_week ) .'" rel="next">'. __( 'Next Week', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_header_nav');
		}
		public static function after_header_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_header_nav');
		}
		public static function after_header( $post_id ){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_header');
		}		
		// Start Week Loop
		public static function before_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_before_loop' );
		}
		public static function inside_before_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_inside_before_loop' );
		}
		// Week Grid
		public static function the_grid() {

			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			$start_of_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
			$week_length = 7; // days of the week
			$today = date( 'Y-m-d', strtotime( 'today' ) );
			$events->all_day = array();
			$events->daily = array();
			$events->hours = array( 'start'=>null, 'end'=>null );
			foreach ( $wp_query->posts as $event ) {
				if ( $event->tribe_is_allday ) {
					$events->all_day[] = $event;
				} else {
					$start_hour = date( 'G', strtotime( $event->EventStartDate ) );
					$end_hour = date( 'G', strtotime( $event->EventEndDate ) );
					if ( is_null( $events->hours['start'] ) || $start_hour < $events->hours['start'] ) {
						$events->hours['start'] = $start_hour;
					}
					if ( is_null( $events->hours['end'] ) || $end_hour > $events->hours['end'] ) {
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
			<span class="tribe-events-visuallyhidden"><?php _e( 'Hours', 'tribe-events-calendar-pro' ); ?></span>
		</div>

		<div class="tribe-grid-content-wrap">

			<?php
			for ( $n = 0; $n < $week_length; $n++ ) {
				$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
				$header_class = ( $day == $today ) ? 'tribe-week-today' : '';
				printf( '<div title="%s" class="column %s"><a href="%s" rel="bookmark">%s</a></div><!-- header column -->',
					$day,
					$header_class,
					trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ) ),
					date( 'D jS', strtotime( $start_of_week . " +$n days" ) )
				);
			} ?>

		</div><!-- .tribe-grid-content-wrap -->

	</div><!-- .tribe-grid-header -->

	<?php // All Day "Row" ?>
	<div class="tribe-grid-allday clearfix">

		<div class="column first"><?php _e( 'All Day', 'tribe-events-calendar-pro' ); ?></div>

		<div class="tribe-grid-content-wrap">

			<?php
			$placeholder = 0;
			for ( $n = 0; $n < $week_length; $n++ ) {
				$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
				$header_class = ( $day == $today ) ? 'tribe-week-today' : '';
				printf( '<div title="%s" class="column %s">', date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ), $header_class );
				if ( $placeholder > 0 ) {
					for ( $placeholder_i = 0; $placeholder_i <= $placeholder; $placeholder_i++ ) {
						echo '<div class="tribe-event-placeholder">placeholder</div>';
					}
				}
				foreach ( $events->all_day as $event ) {
					if ( date( 'Y-m-d', strtotime( $event->EventStartDate ) ) == $day ) {
						$span_class = '';
						$days_between = tribe_get_days_between( $event->EventStartDate, $event->EventEndDate );
						if ( $days_between > 0 ) {
							$day_span_length = $days_between > ( $week_length - $n ) ? ( $week_length - $n ) : $days_between;
							$span_class = 'tribe-dayspan' . $day_span_length;
						}
						printf( '<div id="tribe-events-event-'. $event->ID .'" class="%s" data-hour="all-day"><div><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3>',
							'hentry vevent ' . $span_class,
							get_permalink( $event->ID ),
							$event->post_title
						); ?>

						<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
							<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
							<div class="tribe-events-event-body">
								<div class="duration">
									<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventStartDate ) ); ?>">
										<?php if ( !empty( $event->EventStartDate ) )	
											echo date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventStartDate ) );
											if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
												echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventStartDate ) ); ?>
									</abbr><!-- .dtstart -->
									<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventEndDate ) ); ?>">
										<?php if ( !empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
											if ( date_i18n( 'Y-m-d', $event->EventStartDate ) == date_i18n( 'Y-m-d', $event->EventEndDate ) ) {
												$time_format = get_option( 'time_format', 'g:i a' );
												if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
													echo " – " . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
												} else {
													echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventEndDate ) );
													if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
									 					echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventEndDate ) ) . '<br />';
												}
											} ?>
									</abbr><!-- .dtend -->
								</div><!-- .duration -->

								<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
									<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75, 75 ) );?></div>
								<?php } ?>

								<p class="entry-summary description">
								<?php if( has_excerpt( $event->ID ) ) {
									echo TribeEvents::truncate( $event->post_excerpt, 30 );
								} else {
									echo TribeEvents::truncate( $event->post_content, 30 );
								} ?>
								</p><!-- .entry-summary -->

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

	<?php // Grid "Rows" ?>
	<div class="tribe-week-grid-outer-wrap">
		<div class="tribe-week-grid-inner-wrap">
			<?php
			// sam messing aboot
			for ( $grid_blocks = $events->hours['start']; $grid_blocks <= $events->hours['end']; $grid_blocks++ ) {
				printf( '<div class="tribe-week-grid-block" data-hour="%s"><div></div></div>', date( 'G', mktime( $grid_blocks ) ) );
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

			for ( $hour = $events->hours['start']; $hour <= $events->hours['end']; $hour++ ) {
				printf( '<div>%s</div>', date( 'gA', mktime( $hour ) ) );
			}

?>
		</div><!-- tribe-week-grid-hours -->

		<?php // Content ?>
		<div class="tribe-grid-content-wrap">

			<?php // Our day columns?
			for ( $n = 0; $n < $week_length; $n++ ) {
				$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
				$header_class = ( $day == $today ) ? 'tribe-week-today' : '';
				printf( '<div title="%s" class="column hfeed vcalendar %s">',
					date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ),
					$header_class
				);
				foreach ( $events->daily as $event ) {
					if ( date( 'Y-m-d', strtotime( $event->EventStartDate ) ) == $day ) {
						$duration = ( $event->EventDuration / 60 );
						echo '<div id="tribe-events-event-'. $event->ID .'" duration="'. $duration .'" data-hour="' . date( 'G', strtotime( $event->EventStartDate ) ) . '" data-min="' . date( 'i', strtotime( $event->EventStartDate ) ) . '">';
						printf( '<div class="hentry vevent"><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3></div>',
							get_permalink( $event->ID ),
							$event->post_title
						); ?>

						<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
							<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
							<div class="tribe-events-event-body">
								<div class="duration">
									<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventStartDate ) ); ?>">
										<?php if ( !empty( $event->EventStartDate ) )	
											echo date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventStartDate ) );
											if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
												echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventStartDate ) ); ?>
									</abbr><!-- .dtstart -->
									<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $event->EventEndDate ); ?>">
										<?php if ( !empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
											if ( date_i18n( 'Y-m-d', $event->EventStartDate ) == date_i18n( 'Y-m-d', $event->EventEndDate ) ) {
												$time_format = get_option( 'time_format', 'g:i a' );
												if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
													echo " – " . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
												} else {
													echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventEndDate ) );
													if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
									 					echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventEndDate ) ) . '<br />';
												}
											} ?>
									</abbr><!-- .dtend -->
								</div><!-- .duration -->

								<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
									<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75, 75 ) );?></div>
								<?php } ?>

								<p class="entry-summary description">
								<?php if( has_excerpt( $event->ID ) ) {
									echo TribeEvents::truncate( $event->post_excerpt, 30 );
								} else {
									echo TribeEvents::truncate( $event->post_content, 30 );
								} ?>
								</p><!-- .entry-summary -->

							</div><!-- .tribe-events-event-body -->
							<span class="tribe-events-arrow"></span>
							<div style="display:none"><?php print_r( $event ); ?></div>
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

    <?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_the_grid' );
		}

		// End Week Loop
		public static function inside_after_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_inside_after_loop' );
		}
		public static function after_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_after_loop' );
		}
		// Week Footer
		public static function before_footer( $post_id ){
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_footer');
		}
		// Week Navigation
		public static function before_footer_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Week Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_footer_nav');
		}
		public static function footer_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' -7 days' ) ) .'" href="'. tribe_get_last_week_permalink( $current_week ) .'" rel="prev">&larr; '. __( 'Prev Week', 'tribe-events-calendar-pro' ) .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) .'" href="'. tribe_get_next_week_permalink( $current_week ) .'" rel="next">'. __( 'Next Week', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_footer_nav');
		}
		public static function after_footer_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_footer_nav');
		}
		public static function after_footer( $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_footer');
		}
		// End Week Template
		public static function after_template( $post_id ) {
			$html = '';

			// iCal import button
			if ( function_exists( 'tribe_get_ical_link' ) ) {
				$html .= sprintf( '<a class="tribe-events-ical tribe-events-button-grey" title="%s" href="%s">%s</a>',
					esc_attr( 'iCal Import', 'tribe-events-calendar' ),
					tribe_get_ical_link(),
					__( 'iCal Import', 'tribe-events-calendar' )
				);
			}
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_after_template' );
		}
	}
	Tribe_Events_Week_Template::init();
}
