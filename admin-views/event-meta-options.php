<h3><?php _e('Additional Fields') ?></h3>
<p><?php _e('You can set up any additional custom fields that you would like to use for events here.') ?></p>
<table class='form-table' id="additional-field-table" style='width: 300px;'>
	<tr><th><?php _e('Field Label') ?></th><th><?php _e('Field Type') ?></th><th><?php _e('Options (one per line)') ?></th><th></th></tr>
	<?php foreach ( $customFields as $customField ): ?> 
		<tr>
			<td><input type="text" name="custom-field-<?php echo $count ?>" data-name-template='custom-field-' data-count='<?php echo $count ?>' value="<?php echo $customField['name'] ?>"/></td>
			<td>
				<select name="custom-field-type-<?php echo $count ?>" data-name-template='custom-field-type-' data-count='<?php echo $count ?>'>
					<option value="text" <?php selected($customField['type'] == 'text') ?>>Text</option>
					<option value="radio" <?php selected($customField['type'] == 'radio') ?>>Radio</option>
					<option value="checkbox" <?php selected($customField['type'] == 'checkbox') ?>>Checkbox</option>
					<option value="dropdown" <?php selected($customField['type'] == 'dropdown') ?>>Dropdown</option>
					<option value="textarea" <?php selected($customField['type'] == 'textarea') ?>>Text Area</option>
				</select>
			</td>
			<td><textarea name="custom-field-options-<?php echo $count ?>" data-name-template='custom-field-options-' data-count='<?php echo $count ?>'><?php echo $customField['values'] ?></textarea></td>
			<td>
				<?php if ($count == sizeof($customFields)): ?>
					<a name="add-field" href='#add-field' class='add-another-field'>Add another</a>
				<?php endif; ?>
			</td>
		</tr>
	<?php $count++; endforeach; ?>
</table>
<script>
	jQuery(document).ready(function($) {
		if($('#additional-field-table').size() > 0) {
			$('#additional-field-table').delegate('.add-another-field', 'click', function() {
				var table = $(this).closest('table tbody'), lastRow = table.find('tr:last'), newRow = lastRow.clone();
				
				lastRow.find('td:last').html('');
				newRow.find('input, select, textarea').each(function() {
					var input = $(this), number = parseInt(input.data('count')) + 1;
					input.attr('name', input.data('name-template') + number);
					input.val('');
					input.attr('data-count', number);
				});
				
				table.append(newRow);
			});
		}
	});
</script>