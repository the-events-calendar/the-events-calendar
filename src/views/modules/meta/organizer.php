<?php
/**
 * Single Event Meta (Organizer) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @package TribeEventsCalendar
 */

$organizer_ids = tribe_get_organizer_ids();
$multiple = count( $organizer_ids ) > 1;

$phone = tribe_get_organizer_phone();
$email = tribe_get_organizer_email();
$website = tribe_get_organizer_website_link();
?>

<div class="tribe-events-meta-group tribe-events-meta-group-organizer">
	<h3 class="tribe-events-single-section-title"> <?php echo tribe_get_organizer_label( !$multiple ); ?> </h3>
	<dl>
		<?php do_action( 'tribe_events_single_meta_organizer_section_start' ) ?>

		<?php foreach ( $organizer_ids as $organizer ) { ?>
			<dd class="fn org"> <?php echo tribe_get_organizer( $organizer ) ?> </dd>
		<?php } ?>

		<?php if ( !$multiple ) { // only show organizer details if there is one ?>

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

		<?php } ?>

		<?php do_action( 'tribe_events_single_meta_organizer_section_end' ) ?>
	</dl>
</div>