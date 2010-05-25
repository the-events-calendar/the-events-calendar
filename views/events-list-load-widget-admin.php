<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:',$this->pluginDomain);?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show:',$this->pluginDomain);?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat">
	<?php for ($i=1; $i<=10; $i++)
	{?>
	<option <?php if ( $i == $instance['limit'] ) {echo 'selected="selected"';}?> > <?php echo $i;?> </option>
	<?php } ?>							
	</select>
</p>
	<label for="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>"><?php _e('Don\'t show the widget if there are no upcoming events:',$this->pluginDomain);?></label>
	<input id="<?php echo $this->get_field_id( 'no_upcoming_events' ); ?>" name="<?php echo $this->get_field_name( 'no_upcoming_events' ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" />
<p>

</p>

<p><?php _e( 'Display:', $this->pluginDomain ); ?><br/>

<?php $displayoptions = array (
	"start" => __('Start Date & Time', $this->pluginDomain),
	"end" => __("End Date & Time", $this->pluginDomain),
	"venue" => __("Venue", $this->pluginDomain),
	"address" => __("Address", $this->pluginDomain),
	"city" => __("City", $this->pluginDomain),
	"state" => __("State (US)", $this->pluginDomain),
	"province" => __("Province (Int)", $this->pluginDomain),
	"zip" => __("Postal Code", $this->pluginDomain),
	"country" => __("Country", $this->pluginDomain),
	"phone" => __("Phone", $this->pluginDomain),
	"cost" => __("Price", $this->pluginDomain),
);
	foreach ($displayoptions as $option => $label) { ?>
		<input class="checkbox" type="checkbox" <?php checked( $instance[$option], 'on' ); ?> id="<?php echo $this->get_field_id( $option ); ?>" name="<?php echo $this->get_field_name( $option ); ?>" style="margin-left:5px"/>
		<label for="<?php echo $this->get_field_id( $option ); ?>"><?php echo $label ?></label>
		<br/>
<?php } ?>
	</p>