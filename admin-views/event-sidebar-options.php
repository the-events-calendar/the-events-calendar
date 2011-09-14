<?php
/**
* UI for option to hide from upcoming events list
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<?php global $post; ?>
<label class="selectit"><input value="yes" type="checkbox" <?php checked(tribe_get_event_meta($post->ID, '_EventHideFromUpcoming') == "yes") ?> name="EventHideFromUpcoming"><?php _e("Hide From Upcoming Events List", 'tribe-events-calendar'); ?></label>
