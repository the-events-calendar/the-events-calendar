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
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Number of events to show:','tribe-events-calendar-pro');?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat">
	<?php for ($i=1; $i<=10; $i++)
	{?>
	<option <?php if ( $i == $instance['limit'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
	<?php } ?>
	</select>
</p>

<p><?php _e( 'Display:', 'tribe-events-calendar-pro' ); ?><br />

<?php $displayoptions = array (
					"venue" => __("Venue", 'tribe-events-calendar-pro'),
					"organizer" => __("Organizer", 'tribe-events-calendar-pro'),
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
	<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:', 'tribe-events-calendar-pro' );?></label>
		<?php

			echo wp_dropdown_categories( array(
				'show_option_none' => __( 'All Events', 'tribe-events-calendar-pro' ),
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
<p><label for="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>"><?php _e('Hide this widget if there are no upcoming events:','tribe-events-calendar-pro');?></label>
<input id="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>" name="<?php echo $this->get_field_name( 'no_upcoming_events' ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" /></p>


