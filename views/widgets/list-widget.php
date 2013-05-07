<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * You can customize this view by putting a replacement file of the same name
 * (/widgets/list-widget.php) in the tribe-events/pro/widgets/ directory of your theme.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @return string
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>

<li>
	<p class="entry-title summary">
			<a href="<?php echo tribe_get_event_link(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</p>
	<div class="duration">
			<?php echo tribe_events_event_schedule_details(); ?>	
	</div>
	<div class="vcard adr location">	
		<?php if ( $venue  && tribe_get_venue() != '') { ?>
			<span class="fn org tribe-venue"><?php echo tribe_get_venue(); ?></span> 
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
				<span class="tribe-organizer"><?php echo tribe_get_organizer(); ?></span>
		<?php } ?>

		<?php if ( $phone && tribe_get_phone() != '' ) { ?>
			<span class="tel"><?php echo tribe_get_phone(); ?></span>
		<?php } ?>

		<?php if ( $cost && tribe_get_cost() != '' ) { ?>
			<span class="tribe-events-event-cost">
				<?php _e( 'Price:', 'tribe-events-calendar-pro' ); ?>
				<?php echo tribe_get_cost( null, true ); ?>
			</span>
		<?php } ?>
	</div>
</li>
