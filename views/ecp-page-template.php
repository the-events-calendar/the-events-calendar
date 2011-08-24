<?php
/**
 * Page Template
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>	
<?php get_header(); ?>
<?php tribe_events_before_html() ?>
<h2 class="tribe-events-cal-title"><?php tribe_events_title(); ?></h2>
<?php include(tribe_get_current_template()); ?>
<?php tribe_events_after_html() ?>
<?php get_footer(); ?>
