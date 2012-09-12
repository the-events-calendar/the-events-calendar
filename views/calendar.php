<?php
/**
 * Grid View Template
 * This file loads the TEC month view, specifically the 
 * month view navigation. The actual rendering if the calendar happens in the 
 * table.php template.
 *
 * You can customize this view by putting a replacement file of the same name 
 * (calendar.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance();
?>	

<div id="tribe-events-content" class="grid">
	
    <!-- This title is here for ajax loading – do not remove if you want ajax switching between month views -->
    <title><?php wp_title(); ?></title>
      	
	<div id="tribe-events-calendar-header" class="clearfix">
		
		<?php // Month & Year Nav ?>
		<span class="tribe-events-month-nav">
		
			<span class="tribe-events-prev-month">
				<a href="<?php echo tribe_get_previous_month_link(); ?>"> &#x2190; <?php echo tribe_get_previous_month_text(); ?> </a>
			</span><!-- .tribe-events-prev-month -->

			<?php tribe_month_year_dropdowns( "tribe-events-" ); ?>
	
			<span class="tribe-events-next-month">
				<a href="<?php echo tribe_get_next_month_link(); ?>"> <?php echo tribe_get_next_month_text(); ?> &#x2192; </a>
               	<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" style="display: none" />
			</span><!-- .tribe-events-next-month -->
		
		</span><!-- .tribe-events-month-nav -->

		<?php // View Buttons ?>
		<span class="tribe-events-calendar-buttons"> 
			<a class="tribe-events-button-off" href="<?php echo tribe_get_listview_link(); ?>"><?php _e( 'Event List', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-on" href="<?php echo tribe_get_gridview_link(); ?>"><?php _e( 'Calendar', 'tribe-events-calendar' ); ?></a>
		</span><!-- .tribe-events-calendar-buttons -->
			
	</div><!-- #tribe-events-calendar-header -->
		
	<?php tribe_calendar_grid(); // See the views/table.php template for customization ?>
		
    <?php if( function_exists( 'tribe_get_ical_link' ) ): ?>
       	<a title="<?php esc_attr_e( 'iCal Import', 'tribe-events-calendar' ); ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e( 'iCal Import', 'tribe-events-calendar' ); ?></a>
    <?php endif; ?>
	<?php if ( tribe_get_option( 'donate-link', false ) == true ) { ?>
		<p class="tribe-promo-banner"><?php echo apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ); ?></p>
	<?php } ?>
		
</div><!-- #tribe-events-content -->
