<?php
/**
* Widget admin for the event countdown widget.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'tribe-events-calendar-pro'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'event_ID' ); ?>"><?php _e('Event:','tribe-events-calendar-pro');?>
<select class="widefat" id="<?php echo $this->get_field_id('event_ID'); ?>" name="<?php echo $this->get_field_name('event_ID'); ?>" value="<?php echo $instance['event_ID']; ?>" >
<?php 
foreach ($events as $event )
	{ ?>
	<option value="<?php echo $event->ID; ?>" <?php echo ( $event->ID == $instance['event_ID'] ) ? 'selected="selected"' : ''?>> <?php echo $event->post_title ?> </option>
<?php } ?>
</select>
</p>
<p><label for="<?php echo $this->get_field_id('show_seconds'); ?>"><?php _e('Show seconds?','tribe-events-calendar-pro'); ?></label>
<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_seconds'], true ); ?> id="<?php echo $this->get_field_id( 'show_seconds' ); ?>" name="<?php echo $this->get_field_name('show_seconds'); ?>" />
</p>
<p><label for="<?php echo $this->get_field_id('complete'); ?>"><?php _e('Countdown Completed Text:', 'tribe-events-calendar-pro'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('complete'); ?>" name="<?php echo $this->get_field_name('complete'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['complete'])); ?>" /></p>