<?php
/**
 * Single Event Meta (Organizer) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @package TribeEventsCalendar
 */

$phone = tribe_get_organizer_phone();
$email = tribe_get_organizer_email();
$website = tribe_get_organizer_website_link();
?>

<div class="tribe-events-meta-group tribe-events-meta-group-organizer">
	<h3 class="tribe-events-single-section-title"> <?php _e( tribe_get_organizer_label_singular(), 'tribe-events-calendar' ) ?> </h3>
	<dl>
		<?php do_action( 'tribe_events_single_meta_organizer_section_start' ) ?>

		<dd class="fn org"> <?php echo tribe_get_organizer() ?> </dd>

		<?php if ( ! empty( $phone ) ): ?>
			<dt> <?php _e( 'Phone:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="tel"> <?php echo $phone ?> </dd>
		<?php endif ?>

		<?php if ( ! empty( $email ) ): ?>
			<dt> <?php _e( 'Email:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="email"> <?php echo $email ?> </dd>
		<?php endif ?>

		<?php if ( ! empty( $website ) ): ?>
			<dt> <?php _e( 'Website:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="url"> <?php echo $website ?> </dd>
		<?php endif ?>

		<?php do_action( 'tribe_events_single_meta_organizer_section_end' ) ?>
	</dl>
</div>