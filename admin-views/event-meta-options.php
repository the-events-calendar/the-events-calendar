<h3><?php _e('Additional Fields') ?></h3>
<p><?php _e('You can set up any additional custom fields that you would like to use for events here.') ?></p>
<table class='wp-list-table widefat' id="additional-field-table" style=''>
	<thead><tr><th><?php _e('Field Label') ?></th><th><?php _e('Field Type') ?></th><th><?php _e('Options (one per line)') ?></th><th></th></tr></thead>
	<tbody>
   <?php $customFields[] = array() ?>
	<?php foreach ( $customFields as $customField ): ?> 
		<tr>
         <td><input type="text" name="custom-field-<?php echo esc_attr($count) ?>" data-persisted='<?php echo $count != sizeof($customFields) ? "yes" : "no" ?>' data-name-template='custom-field-' data-count='<?php echo esc_attr($count) ?>' value="<?php echo esc_attr($customField['label']) ?>"/></td>
			<td>
				<select name="custom-field-type-<?php echo $count ?>" data-name-template='custom-field-type-' data-count='<?php echo $count ?>'>
					<option value="text" <?php selected($customField['type'] == 'textarea') ?>>Text</option>
					<option value="radio" <?php selected($customField['type'] == 'radio') ?>>Radio</option>
					<option value="checkbox" <?php selected($customField['type'] == 'checkbox') ?>>Checkbox</option>
					<option value="dropdown" <?php selected($customField['type'] == 'dropdown') ?>>Dropdown</option>
					<!--<option value="textarea" <?php selected($customField['type'] == 'textarea') ?>>Text Area</option>-->
				</select>
			</td>
			<td><textarea style='display: <?php echo $customField['type'] == 'radio' || $customField['type'] == 'checkbox' || $customField['type'] == 'dropdown' ? "inline" : "none" ?>;' name="custom-field-options-<?php echo $count ?>" data-name-template='custom-field-options-' data-count='<?php echo esc_attr($count) ?>' rows="3"><?php echo esc_textarea($customField['values']) ?></textarea></td>
			<td>
				<?php if ($count == sizeof($customFields)): ?>
					<a name="add-field" href='#add-field' class='add-another-field'>Add another</a>
            <?php else: ?>
               <a name="remove-field" href='#remove-field' class='remove-another-field'>Remove</a>
				<?php endif; ?>
			</td>
		</tr>
	<?php $count++; endforeach; ?>
	</tbody>
</table>
<script>
	jQuery(document).ready(function($) {
		if($('#additional-field-table').size() > 0) {
         $('#additional-field-table').delegate('.remove-another-field', 'click', function() {
            var row = $(this).closest('tr'), firstInput=row.find('td:first input'), data = { action: 'remove_option', field: firstInput.data('count') }, persisted = firstInput.data('persisted')
            if(confirm('Are you sure you wish to remove this field and its data from all events? Once you click OK this cannot be undone.')) {
               if(persisted == "yes") {
                  jQuery.post(ajaxurl, data, function(response) {
                     row.fadeOut('slow', function() {
                        $(this).remove();
                     });
                  });
               } else {
                  row.fadeOut('slow', function() {
                     $(this).remove();
                  });
               }
            }
         });

			$('#additional-field-table').delegate('.add-another-field', 'click', function() {
				var table = $(this).closest('table tbody'), lastRow = table.find('tr:last'), newRow = lastRow.clone();
				
				lastRow.find('td:last').html(lastRow.prev().find('td:last').html());
				newRow.find('input, select, textarea').each(function() {
					var input = $(this), number = parseInt(input.data('count')) + 1;
					input.attr('name', input.data('name-template') + number);
					input.val('');
					input.attr('data-count', number);
				});
				
				table.append(newRow);
			});
			
			$('#additional-field-table').delegate('select', 'change', function() {
				var fieldType = $(this).find("option:selected").val();
				if( fieldType == 'radio' || fieldType == 'dropdown' || fieldType == 'checkbox' ) {
					$(this).closest('tr').find('textarea').show();
				} else {
					$(this).closest('tr').find('textarea').hide();
				}
			});
		}
	});
</script>
