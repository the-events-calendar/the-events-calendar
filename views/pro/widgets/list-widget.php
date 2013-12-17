<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/list-widget.php
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 
$widget_args = tribe_events_get_adv_list_widget_args(); 
extract($widget_args);
?>

<li class="<?php tribe_events_event_classes() ?>">
	<h4 class="entry-title summary">
		<a href="<?php echo tribe_get_event_link(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</h4>
	<div class="duration">
			<?php echo tribe_events_event_schedule_details(); ?>	
	</div>
	<?php if ( $cost && tribe_get_cost() != '' ) { ?>
		<span class="tribe-events-divider">|</span>
		<div class="tribe-events-event-cost">
			<?php echo tribe_get_cost( null, true ); ?>
		</div>
	<?php } ?>			
	<div class="vcard adr location">	

		<?php if ( $venue  && tribe_get_venue() != '') { ?>
			<span class="fn org tribe-venue"><?php echo tribe_get_venue_link(); ?></span> 
		<?php } ?>

		<?php if ( $address && tribe_get_address() != '' ) { ?>
			<span class="street-address"><?php echo tribe_get_address(); ?></span>
		<?php } ?>

		<?php if ( $city && tribe_get_city() != '' ) { ?>
			<span class="locality"><?php echo tribe_get_city(); ?></span>
		<?php } ?>

		<?php if ( $region && tribe_get_region() !='' ) { ?>
			<span class="region"><?php echo tribe_get_region(); ?></span>
		<?php	} ?>

		<?php if ( $zip && tribe_get_zip() != '' ) { ?>
			<span class="postal-code"><?php echo tribe_get_zip(); ?></span>
		<?php } ?>

		<?php if ( $country  && tribe_get_country() != '') { ?>
			<span class="country-name"><?php echo tribe_get_country(); ?></span>
		<?php } ?>

		<?php if ( $organizer && tribe_get_organizer() != '' ) { ?>
		<?php _e( 'Organizer:', 'tribe-events-calendar-pro' ); ?>
				<span class="tribe-organizer"><?php echo tribe_get_organizer_link(); ?></span>
		<?php } ?>

		<?php if ( $phone && tribe_get_phone() != '' ) { ?>
			<span class="tel"><?php echo tribe_get_phone(); ?></span>
		<?php } ?>
	</div>
</li>
