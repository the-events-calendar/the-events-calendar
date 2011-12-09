<?php
/**
* Widget admin for the related events widget.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','tribe-events-calendar-pro'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Count:','tribe-events-calendar-pro');?>
<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo $instance['count']; ?>" >
<?php for ($i=1; $i<=5; $i++)
	{ ?>
	<option <?php if ( $i == $instance['count'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
<?php } ?>
</select>
</p>
<p><label for="<?php echo $this->get_field_id('thumbnails'); ?>"><?php _e('Display Thumbnails?','tribe-events-calendar-pro'); ?></label>
<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['thumbnails'], true ); ?> id="<?php echo $this->get_field_id( 'thumbnails' ); ?>" name="<?php echo $this->get_field_name('thumbnails'); ?>" />
</p>
<p><label for="<?php echo $this->get_field_id('start_date'); ?>"><?php _e('Show Start Date?','tribe-events-calendar-pro'); ?></label>
<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['start_date'], true ); ?> id="<?php echo $this->get_field_id( 'start_date' ); ?>" name="<?php echo $this->get_field_name('start_date'); ?>" />
</p>