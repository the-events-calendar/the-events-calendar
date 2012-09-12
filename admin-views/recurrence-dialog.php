<?php
/**
* Recurrence dialogue box
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="recurring-dialog"  title="Saving Recurring Event" style="display: none;">
	<p><?php _e('Would you like to change only this instance of the event, or all future events in this series?','tribe-events-calendar'); ?></p>
	<ul>
		<li><strong><?php _e('Only This Event:','tribe-events-calendar'); ?></strong> <em><?php _e('All other future events in the series will remain the same.','tribe-events-calendar'); ?></em></li><br/>
		<li><strong><?php _e('All Events:','tribe-events-calendar'); ?></strong> <em><?php _e('All future events in the series will be changed. Any changes made to other events will be kept.','tribe-events-calendar'); ?></em></li>
	</ul>
</div>

<div id="deletion-dialog"  title="Delete Recurring Event" style="display: none;" data-start="<?php echo (isset($recStart)) ?  $recStart : '' ?>" data-post="<?php echo (isset($recPost)) ?  $recPost : '' ?>">
	<p><?php _e('Would you like to delete only this instance of the event, or all future events in this series?','tribe-events-calendar'); ?></p>
	<ul>
		<li><strong><?php _e('Only This Event:','tribe-events-calendar'); ?></strong> <em><?php _e('All other future events in the series will not be deleted.','tribe-events-calendar'); ?></em></li><br/>
		<li><strong><?php _e('All Events:','tribe-events-calendar'); ?></strong> <em><?php _e('All future events in the series will be deleted.','tribe-events-calendar'); ?></em></li>
	</ul>
</div>
