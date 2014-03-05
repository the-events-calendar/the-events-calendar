<?php
/**
 * Events List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is needed.
 *
 * This view contains the filters required to create an effective events list widget view.
 *
 * You can recreate an ENTIRELY new events list widget view by doing a template override,
 * and placing a list-widget.php file in a tribe-events/widgets/ directory 
 * within your theme directory, which will override the /views/widgets/list-widget.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @return string
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
?>

<li class="tribe-events-list-widget-events <?php tribe_events_event_classes() ?>">
	
	<?php do_action( 'tribe_events_list_widget_before_the_event_title' ); ?>
	
	<h4 class="entry-title summary">
			<a href="<?php echo tribe_get_event_link(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</h4>
	
	<?php do_action( 'tribe_events_list_widget_after_the_event_title' ); ?>	
	<!-- Event Time -->
	
	<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>
	
	<div class="duration">
		<?php echo tribe_events_event_schedule_details(); ?>
	</div>
	
	<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>
	
	<!-- Event Title -->
</li>
