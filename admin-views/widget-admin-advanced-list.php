<?php
/**
* Widget admin for the event list widget.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:','tribe-events-calendar-pro');?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show:','tribe-events-calendar-pro');?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat">
	<?php for ($i=1; $i<=10; $i++)
	{?>
	<option <?php if ( $i == $instance['limit'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
	<?php } ?>							
	</select>
</p>
	<label for="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>"><?php _e('Show widget only if there are upcoming events:','tribe-events-calendar-pro');?></label>
	<input id="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>" name="<?php echo $this->get_field_name( 'no_upcoming_events' ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" />
<p> 

</p>

<p><?php _e( 'Display:', 'tribe-events-calendar-pro' ); ?><br />

<?php $displayoptions = array (
					"start" => __('Start Date & Time', 'tribe-events-calendar-pro') .'<small><br/>'.__('(Widget will always show start date)', 'tribe-events-calendar-pro').'</small>',
					"end" => __("End Date & Time", 'tribe-events-calendar-pro'),
					"venue" => __("Venue", 'tribe-events-calendar-pro'),
					"address" => __("Address", 'tribe-events-calendar-pro'),
					"city" => __("City", 'tribe-events-calendar-pro'),
					"region" => __("State (US) Or Province (Int)", 'tribe-events-calendar-pro'),
					"zip" => __("Postal Code", 'tribe-events-calendar-pro'),
					"country" => __("Country", 'tribe-events-calendar-pro'),
					"phone" => __("Phone", 'tribe-events-calendar-pro'),
					"cost" => __("Price", 'tribe-events-calendar-pro'),
				);
	foreach ($displayoptions as $option => $label) { ?>
		<input class="checkbox" type="checkbox" value="1" <?php checked( $instance[$option], true ); ?> id="<?php echo $this->get_field_id( $option ); ?>" name="<?php echo $this->get_field_name( $option ); ?>" style="margin-left:5px"/>
		<label for="<?php echo $this->get_field_id( $option ); ?>"><?php echo $label ?></label>
		<br/>
<?php } ?>
	<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Category:','tribe-events-calendar-pro');?>
		<?php 

			echo wp_dropdown_categories( array(
				'show_option_none' => 'All Events',
				'hide_empty' => 0,
				'echo' => 0,
				'name' => $this->get_field_name( 'category' ),
				'id' => $this->get_field_id( 'category' ),
				'taxonomy' => TribeEvents::TAXONOMY,
            'selected' => $instance['category'],
            'hierarchical'=>1
			));
		?>
</p>
<p><small><em><?php printf( __('Want to modify the display of this widget? Try a %stemplate override%s.', 'tribe-events-calendar'), '<a href="http://tri.be/faq/what-are-template-overrides-and-how-do-i-do-them/">', '</a>' ); ?></em></small></p>
