<?php
/**
* Recurrence dialogue box
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="recurring-dialog"  title="Saving Recurring Event" style="display: none;">
	<p><?php _e('Would you like to change only this instance of the event, or all events in this series?','tribe-events-calendar'); ?></p>
	<ul>
		<li><strong>Only This Event:</strong> <em>All other events in the series will remain the same.</em></li><br/>
		<li><strong>All Events:</strong> <em>All events in the series will be changed. Any changes made to other events will be kept.</em></li>
	</ul>
</div>
<div id="deletion-dialog"  title="Delete Recurring Event" style="display: none;" data-start="<?php echo (isset($recStart)) ?  $recStart : '' ?>" data-post="<?php echo (isset($recPost)) ?  $recPost : '' ?>">
	<p><?php _e('Would you like to delete only this instance of the event, or all events in this series?','tribe-events-calendar'); ?></p>
	<ul>
		<li><strong>Only This Event:</strong> <em>All other events in the series will not be deleted.</em></li><br/>
		<li><strong>All Events:</strong> <em>All events in the series will be deleted.</em></li>
	</ul>
</div>
