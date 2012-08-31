<?php
/**
*  If 'Default Events Template' is selected in Settings -> The Events Calendar -> Theme Settings -> Events Template, 
*  then this file loads the page template for all ECP views except for the individual 
*  event view.  Generally, this setting should only be used if you want to manually 
*  specify all the shell HTML of your ECP pages in this template file.  Use one of the other Theme 
*  Settings -> Events Template to automatically integrate views into your 
*  theme.
*
* You can customize this view by putting a replacement file of the same name (ecp-page-template.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>	
<?php get_header(); ?>
<?php tribe_events_before_html(); ?>
<h2 class="tribe-events-cal-title"><?php tribe_events_title(); ?></h2>
<?php include(tribe_get_current_template()); ?>
<?php tribe_events_after_html(); ?>
<?php get_footer(); ?>