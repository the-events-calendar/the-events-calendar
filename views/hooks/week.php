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

			ob_start();
?>
	<table cellspacing="0" cellpadding="0" class="tribe-events-grid">
		<thead>
			<tr>
				<th scope="col" class="tribe-grid-first"><span>Hours</span></th>
				<?php
					for( $n = 0; $n < 7; $n++ ) {
						$header_class = (Date('Y-m-d',strtotime($start_of_week . " +$n days")) == Date('Y-m-d',strtotime('today'))) ? 'tribe-grid-today' : '';
						printf('<th title="%s" scope="col" class="%s"><a href="%s" rel="bookmark">%s</a></th>',
							Date('Y-m-d', strtotime($start_of_week . " +$n days")),
							$header_class,
							trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($start_of_week . " +$n days") ) ),
							Date('l jS', strtotime($start_of_week . " +$n days"))
							);
					}
				?>
			</tr>
		</thead>
		<tbody>
		
			<?php // our dummy row for all day events ?>
			<tr class="tribe-week-dummy-row" style="height: 72px;">
				<td></td>
				<td class="tribe-week-today"></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr><!-- .tribe-week-dummy-row -->
		
			<?php // our all day events row ?>
			<tr class="tribe-week-allday-row">
				<td class="tribe-week-allday-th"><div style="margin-top: -72px;">All Day</div></td>
				<td colspan="7">
					<div style="margin-top: -72px;">
						<table cellpadding="0" cellspacing="0">
							<tbody class="hfeed">
								<tr>
									<td colspan="2">
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">Multiday all day event</a></div>
									</td>
									<td colspan="4">
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">Multiday all day event</a></div>
									</td>
									<td>
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">All day event</a></div>
									</td>
								</tr>
							</tbody><!-- .hfeed -->
						</table>
					</div>
				</td>
			</tr><!-- .tribe-week-allday-row -->
		
			<?php // our week grid ?>
			<tr class="tribe-week-grid-bgd">
				<td></td>
				<td colspan="7">
					<div class="tribe-week-grid-outer-wrap">
						<div class="tribe-week-grid-inner-wrap">
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
						</div><!-- .tribe-week-grid-inner-wrap -->
					</div><!-- .tribe-week-grid-outer-wrap -->
				</td>
			</tr><!-- .tribe-week-grid-bgd -->
			
			<?php // our actual week grid columns, hours, and events ?>
			<tr class="tribe-week-events-row hfeed">
				<td class="tribe-week-grid-hours">
					<div>7am</div>
					<div>8am</div>
					<div>9am</div>
					<div>10am</div>
				</td><!-- .tribe-week-grid-hours -->
				<td class="tribe-week-grid-col tribe-week-today"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div class="tribe-week-grid-event-wrap hentry vevent">
							<div>
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div class="tribe-week-grid-event-wrap hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 90%; top: 28px; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-overlapping tribe-event-o-1 hentry vevent">
							<div style="height: 50px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 80%; top: 48px; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-overlapping tribe-event-o-2 hentry vevent">
							<div style="height: 50px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div style="width: 80%;" class="tribe-week-grid-event-wrap tribe-event-same-time hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 80%; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-same-time tribe-event-st-1 hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
			</tr><!-- .tribe-week-events-row -->
					
		</tbody>
		
	</table><!-- .tribe-events-grid -->
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
			$html = '<div id="tribe-events-calendar-header" class="clearfix">';
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
			$html = sprintf('<span class="tribe-events-week-nav"><span class="tribe-events-prev-week"><a href="%s">%s</a></span> %s <span class="tribe-events-next-week"><a href="%s">%s</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="" style="display: none" /></span></span>',
								tribe_get_last_week_permalink( $current_week ),
								__( 'Previous Week', 'tribe-events-calendar-pro' ),
								$dropdown,
								tribe_get_next_week_permalink( $current_week ),
								__( 'Next Week', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);

			// View Buttons
			ob_start();
			include_once(TribeEventsTemplates::getTemplateHierarchy( 'buttons', 'modules' ));
			$html .= ob_get_clean();

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_pagination');
		}
		public function after_header( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_template');		
		}
	}
	Tribe_Events_Week_Template::init();
}