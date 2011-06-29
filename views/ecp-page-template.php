<?php
	global $sp_ecp;
	get_header();
	echo stripslashes(sp_get_option('spEventsBeforeHTML'));
?>	
	<h2 class="tec-cal-title"><?php sp_events_title(); ?></h2>
   <?php include(tribe_get_current_template()) ?>
<?php
	echo stripslashes(sp_get_option('spEventsAfterHTML'));
	get_footer();
?>