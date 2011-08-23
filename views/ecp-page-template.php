<?php
/**
 * Page Template
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>	
<?php get_header(); ?>
<?php echo stripslashes(tribe_get_option('spEventsBeforeHTML')); ?>
<h2 class="tribe-events-cal-title"><?php tribe_events_title(); ?></h2>
<?php include(tribe_get_current_template()); ?>
<?php echo stripslashes(tribe_get_option('spEventsAfterHTML')); ?>
<?php get_footer(); ?>