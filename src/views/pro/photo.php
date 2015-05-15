<?php
/**
 * Photo View Template
 * The wrapper template for photo view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/photo.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php do_action( 'tribe_events_before_template' ); ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part( 'modules/bar' ); ?>

<!-- Main Events Content -->
<?php tribe_get_template_part( 'pro/photo/content' ) ?>

<?php do_action( 'tribe_events_after_template' ) ?>
