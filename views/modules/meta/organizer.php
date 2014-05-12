<?php
/**
 * Single Event Meta (Organizer) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @package TribeEventsCalendar
 * @since 3.6
 */
?>

<div class="tribe-events-meta-group tribe-events-meta-group-organizer">
	<h3 class="tribe-events-single-section-title"> <?php _e('Organizer', 'tribe-events-calendar' ) ?> </h3>
	<dl>
		<?php do_action( 'tribe_events_single_meta_organizer_section_start' ) ?>

		<dd class="fn org"> <?php echo tribe_get_organizer() ?> </dd>

		<dt> <?php _e( 'Phone:', 'tribe-events-calendar' ) ?> </dt>
		<dd class="tel"> <?php echo tribe_get_organizer_phone() ?> </dd>

		<dt> <?php _e( 'Email:', 'tribe-events-calendar' ) ?> </dt>
		<dd class="email"> <?php echo tribe_get_organizer_email() ?> </dd>

		<dt> <?php _e( 'Website:', 'tribe-events-calendar' ) ?> </dt>
		<dd class="url"> <?php echo tribe_get_organizer_website_link() ?> </dd>

		<?php do_action( 'tribe_events_single_meta_organizer_section_end' ) ?>
	</dl>
</div>