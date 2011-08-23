<?php
/**
 * Grid view template
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance();
?>	
	<div id="tribe-events-content" class="grid">
		<div id='tribe-events-calendar-header' class="clearfix">
			<span class='tribe-events-month-nav'>
				<span class='tribe-events-prev-month'>
					<a href='<?php echo tribe_get_previous_month_link(); ?>'>
					&#x2190; <?php echo tribe_get_previous_month_text(); ?>
					</a>
				</span>

				<?php tribe_month_year_dropdowns( "tribe-events-" ); ?>
	
				<span class='tribe-events-next-month'>
					<a href='<?php echo tribe_get_next_month_link(); ?>'>				
					<?php echo tribe_get_next_month_text(); ?> &#x2192; 
					</a>
				</span>
			</span>

			<span class='tribe-events-calendar-buttons'> 
				<a class='tribe-events-button-off' href='<?php echo tribe_get_listview_link(); ?>'><?php _e('Event List', $tribe_ecp->pluginDomain)?></a>
				<a class='tribe-events-button-on' href='<?php echo tribe_get_gridview_link(); ?>'><?php _e('Calendar', $tribe_ecp->pluginDomain)?></a>
			</span>

		</div><!-- tribe-events-calendar-header -->
		<?php tribe_calendar_grid(); // See the views/table.php template for customization ?>
		<a title="<?php esc_attr_e('iCal Import', $tribe_ecp->pluginDomain) ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e('iCal Import', $tribe_ecp->pluginDomain) ?></a>
	</div>

<?php echo stripslashes(tribe_get_option('spEventsAfterHTML')); ?>