<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Display -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.23
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Allows filtering the classes for the main element.
 *
 * @since 5.8.0
 *
 * @param array<string> $classes An (unindexed) array of classes to apply.
 */
$classes = apply_filters( 'tribe_default_events_template_classes', [ 'tribe-events-pg-template' ] );

/**
 * Set this to an empty string in case it is not defined.
 * Specifically for the two hooks below.
 *
 * @since 5.9.0
 */
$eventDisplay = isset( $eventDisplay ) ? $eventDisplay : '';


get_header();
/**
 * Provides an action that allows for the injection of HTML at the top of the template after the header.
 *
 * @since 5.8.0
 *
 * @param string $eventDisplay The string representation (slug) of the displayed view - "month".
 */
do_action( 'tribe_default_events_template_after_header', $eventDisplay );
?>
<main
	id="tribe-events-pg-template"
	<?php tribe_classes( $classes ); ?>
>
	<?php tribe_events_before_html(); ?>
	<?php tribe_get_view(); ?>
	<?php tribe_events_after_html(); ?>
</main> <!-- #tribe-events-pg-template -->
<?php

/**
 * Provides an action that allows for the injections of HTML at the bottom of the template before the footer.
 *
 * @since 5.8.0
 *
 * @param string $eventDisplay The string representation (slug) of the displayed view - "month".
 */
do_action( 'tribe_default_events_template_before_footer', $eventDisplay );

get_footer();
