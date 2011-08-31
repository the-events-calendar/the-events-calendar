<?php
/**
* Widget admin for the event list widget.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:',TribeEvents::PLUGIN_DOMAIN);?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show:',TribeEvents::PLUGIN_DOMAIN);?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat">
	<?php for ($i=1; $i<=10; $i++)
	{?>
	<option <?php if ( $i == $instance['limit'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
	<?php } ?>							
	</select>
</p>
	<label for="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>"><?php _e('Show widget only if there are upcoming events:',TribeEvents::PLUGIN_DOMAIN);?></label>
	<input id="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>" name="<?php echo $this->get_field_name( 'no_upcoming_events' ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" />
<p> 

</p>

<p><?php _e( 'Display:', TribeEvents::PLUGIN_DOMAIN ); ?><br />

<?php $displayoptions = array (
					"start" => __('Start Date & Time', TribeEvents::PLUGIN_DOMAIN) .'<small><br/>'.__('(Widget will always show start date)', TribeEvents::PLUGIN_DOMAIN).'</small>',
					"end" => __("End Date & Time", TribeEvents::PLUGIN_DOMAIN),
					"venue" => __("Venue", TribeEvents::PLUGIN_DOMAIN),
					"address" => __("Address", TribeEvents::PLUGIN_DOMAIN),
					"city" => __("City", TribeEvents::PLUGIN_DOMAIN),
					"region" => __("State (US) Or Province (Int)", TribeEvents::PLUGIN_DOMAIN),
					"zip" => __("Postal Code", TribeEvents::PLUGIN_DOMAIN),
					"country" => __("Country", TribeEvents::PLUGIN_DOMAIN),
					"phone" => __("Phone", TribeEvents::PLUGIN_DOMAIN),
					"cost" => __("Price", TribeEvents::PLUGIN_DOMAIN),
				);
	foreach ($displayoptions as $option => $label) { ?>
		<input class="checkbox" type="checkbox" <?php checked( $instance[$option], true ); ?> id="<?php echo $this->get_field_id( $option ); ?>" name="<?php echo $this->get_field_name( $option ); ?>" style="margin-left:5px"/>
		<label for="<?php echo $this->get_field_id( $option ); ?>"><?php echo $label ?></label>
		<br/>
<?php } ?>
	<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Category:',TribeEvents::PLUGIN_DOMAIN);?>
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
<p><small><em><?php _e('If you wish to customize the widget display yourself, see the file views/events-list-load-widget-display.php inside the Events Premium plugin.', TribeEvents::PLUGIN_DOMAIN);?></em></small></p>
