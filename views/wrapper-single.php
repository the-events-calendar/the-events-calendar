<?php
/**
 * Single Page Wrapper Template
 * This file loads the single page wrapper template for the single page specific views (single-event.php,
 * single-venue.php, etc).
 *
 * If 'Default Events Template' is selected in Events -> Settings -> Template -> Events Template, 
 * then this file loads the single page wrapper template for all the single page views. Generally,
 * this setting should only be used if you want to manually specify all the wrapper markup of
 * your views in this template file. You can also select one of the other Events Template 
 * Settings to automatically integrate views into your theme.
 *
 * You can recreate an ENTIRELY new single page wrapper template by doing a template override,
 * and placing a wrapper-single.php file in a tribe-events/ directory within your theme
 * directory, which will override the /views/wrapper-single.php. 
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

?>

<?php get_header(); ?>
<div id="tribe-events-pg-template">
	<?php tribe_events_before_html(); ?>
	<?php tribe_get_view(); ?>
	<?php tribe_events_after_html(); ?>
</div> <!-- #tribe-events-pg-template -->
<?php get_footer(); ?>
