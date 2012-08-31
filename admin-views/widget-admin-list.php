<?php
/**
* Widget admin for the event list widget.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:','tribe-events-calendar');?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show:','tribe-events-calendar');?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat">
	<?php for ($i=1; $i<=10; $i++)
	{?>
	<option <?php if ( $i == $instance['limit'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
	<?php } ?>							
	</select>
</p>
	<label for="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>"><?php _e('Show widget only if there are upcoming events:','tribe-events-calendar');?></label>
	<input id="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>" name="<?php echo $this->get_field_name( 'no_upcoming_events' ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" />
<p> 

</p>

<p><small><em><?php printf( __('Want to modify the display of this widget? Try a %stemplate override%s.', 'tribe-events-calendar'), '<a href="http://tri.be/faq/what-are-template-overrides-and-how-do-i-do-them/">', '</a>' ); ?></em></small></p>
