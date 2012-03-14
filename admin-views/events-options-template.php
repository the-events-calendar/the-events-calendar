<h3><?php _e('Template Settings', 'tribe-events-calendar'); ?></h3>
<table class="form-table">
	<tr>
		<th scope="row"><?php _e('Events Template', 'tribe-events-calendar' ); ?></th>
		<td><fieldset>
			<legend class="screen-reader-text"><?php _e('Events Template', 'tribe-events-calendar' ); ?></legend>
			<select class="chosen" name="tribeEventsTemplate">
				<option value=''><?php _e('Default Events Template', 'tribe-events-calendar' ); ?></option>
				<option value='default' <?php selected(tribe_get_option('tribeEventsTemplate', 'default') == 'default') ?>><?php _e('Default Page Template', 'tribe-events-calendar' ); ?></option>
				<?php page_template_dropdown(tribe_get_option('tribeEventsTemplate', 'default')); ?>
			</select>
			<div><?php _e('Choose a page template to control the look and feel of your calendar.', 'tribe-events-calendar');?> </div>
		</fieldset></td>
	</tr>		
	<tr>
		<th scope="row"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></th>
		<td><fieldset>
			<legend class="screen-reader-text"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></legend>
			<textarea style="width:100%; height:100px;" name="tribeEventsBeforeHTML"><?php echo  stripslashes(tribe_get_option('tribeEventsBeforeHTML'));?></textarea>
			<div><?php _e('Some themes may require that you add extra divs before the calendar list to help with styling.', 'tribe-events-calendar');?> <?php _e('This is displayed directly after the header.', 'tribe-events-calendar');?> <?php  _e('You may use (x)HTML.', 'tribe-events-calendar') ?></div>
		</fieldset></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Add HTML after calendar', 'tribe-events-calendar' ); ?></th>
		<td><fieldset>
			<legend class="screen-reader-text"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></legend>
			<textarea style="width:100%; height:100px;" name="tribeEventsAfterHTML"><?php echo stripslashes(tribe_get_option('tribeEventsAfterHTML'));?></textarea>
			<div><?php _e('Some themes may require that you add extra divs after the calendar list to help with styling.', 'tribe-events-calendar');?> <?php _e('This is displayed directly above the footer.', 'tribe-events-calendar');?> <?php _e('You may use (x)HTML.', 'tribe-events-calendar') ?></div>
		</fieldset></td>
	</tr>
</table>