<?php
/**
* Recurrence dialogue box
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="recurring-dialog"  title="Saving Recurring Event" style="display: none;">
	<?php _e('Which events do you wish to update?','tribe-events-calendar'); ?><br/>
</div>
<div id="deletion-dialog"  title="Delete Recurring Event" style="display: none;" data-start="<?php echo (isset($recStart)) ?  $recStart : '' ?>" data-post="<?php echo (isset($recPost)) ?  $recPost : '' ?>">
	<?php _e('Select your desired action','tribe-events-calendar'); ?><br/>
</div>
