<?php
	global $sp_ecp;
	get_header();
?>	
	<div id="tec-content" class="grid">
		<div id='tec-events-calendar-header' class="clearfix">
			<h2 class="tec-cal-title"><?php sp_events_title(); ?></h2>
			<span class='tec-month-nav'>
				<span class='tec-prev-month'>
					<a href='<?php echo sp_get_previous_month_link(); ?>'>
					&#x2190; <?php echo sp_get_previous_month_text(); ?>
					</a>
				</span>

				<?php sp_month_year_dropdowns( "tec-" ); ?>
	
				<span class='tec-next-month'>
					<a href='<?php echo sp_get_next_month_link(); ?>'>				
					<?php echo sp_get_next_month_text(); ?> &#x2192; 
					</a>
				</span>
			</span>

			<span class='tec-calendar-buttons'> 
				<a class='tec-button-off' href='<?php echo sp_get_listview_link(); ?>'><?php _e('Event List', $sp_ecp->pluginDomain)?></a>
				<a class='tec-button-on' href='<?php echo sp_get_gridview_link(); ?>'><?php _e('Calendar', $sp_ecp->pluginDomain)?></a>
			</span>

		</div><!-- tec-events-calendar-header -->
		<?php sp_calendar_grid(); // See the views/table.php template for customization ?>
		<a title="<?php esc_attr_e('iCal Import', $sp_ecp->pluginDomain) ?>" class="ical" href="<?php echo sp_get_ical_link(); ?>"><?php _e('iCal Import', $sp_ecp->pluginDomain) ?></a>
	</div>

<?php
	get_footer();