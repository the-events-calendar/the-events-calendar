<div id="modern-tribe-info">

	<h2><?php _e( 'Additional Fields', 'tribe-events-calendar-pro' ); ?></h2>

	<p><?php _e( 'Use additional fields to add unique content fields to the event admin, which can be used by anyone who creates events. Upon publication they\'ll appear in the event metabox that accompanies your event listing, alongside the date/time/venue/organizer/etc. </p><p>Each new piece of data needs:', 'tribe-events-calendar-pro' ) . '</p>'; ?>
	<ul class="admin-list">
		<li style="list-style:inside;"><?php _e( 'Label (e.g. Meal Plans)', 'tribe-events-calendar-pro' ); ?></li>
		<li style="list-style:inside;"><?php _e( 'At least one option (e.g. Vegetarian, Kosher, Paleo)', 'tribe-events-calendar-pro' ); ?></li>
		<li style="list-style:inside;"><?php _e( 'Field type. These are:', 'tribe-events-calendar-pro' ); ?></li>
		<ul class="admin-list" style="margin-left:20px;">
			<li style="list-style:inside circle;"><?php _e( 'Text - for the user to input text', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php _e( 'URL - for the user to input a URL', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php _e( 'Checkbox - for multiple choice', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php _e( 'Radio Button - to select only one', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php _e( 'Dropdown Menu - for a dropdown menu', 'tribe-events-calendar-pro' ); ?></li>
		</ul>
	</ul>
</div>
<table class='wp-list-table widefat' id="additional-field-table" style=''>
	<thead>
	<tr>
		<th><?php _e( 'Field Label', 'tribe-events-calendar-pro' ); ?></th>
		<th><?php _e( 'Field Type', 'tribe-events-calendar-pro' ); ?></th>
		<th><?php _e( 'Options (one per line)', 'tribe-events-calendar-pro' ); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$total = count( $customFields );
	$count = 1;

	// Append an extra (blank) entry at the end of the list as a convenience for admins
	$customFields[] = array();

	foreach ( $customFields as $field ) : // Track our progress through the list of custom fields
		$last_row = ( $count ++ === $total );

		// Reuse the existing index (and maintain an underscore prefix - to differentiate
		// between existing fields and newly created ones (so we can maintain the relationship
		// between keys and values)
		if ( isset( $field['name'] ) && 0 === strpos( $field['name'], '_ecp_custom' ) ) {
			$index = esc_attr( substr( $field['name'], 11 ) );
		} // In all other cases, we'll leave things open for a new index to be applied
		else {
			$index = '';
		}
		?>
		<tr>
			<td>
				<input type="text" name="custom-field[<?php echo $index ?>]" data-persisted='<?php echo $count != sizeof( $field ) ? "yes" : "no" ?>' data-name-template='custom-field' data-count='<?php echo esc_attr( $count ) ?>' value="<?php echo isset( $field['label'] ) ? esc_attr( stripslashes( $field['label'] ) ) : ""; ?>" />
			</td>
			<td>
				<select name="custom-field-type[<?php echo $index ?>]" data-name-template='custom-field-type' data-count='<?php echo $count ?>'>
					<option value="text" <?php selected( isset( $field['type'] ) && $field['type'] == 'textarea' ) ?>><?php _e( 'Text', 'tribe-events-calendar-pro' ) ?></option>
					<option value="url" <?php selected( isset( $field['type'] ) && $field['type'] == 'url' ) ?>><?php _e( 'URL', 'tribe-events-calendar-pro' ) ?></option>
					<option value="radio" <?php selected( isset( $field['type'] ) && $field['type'] == 'radio' ) ?>><?php _e( 'Radio', 'tribe-events-calendar-pro' ) ?></option>
					<option value="checkbox" <?php selected( isset( $field['type'] ) && $field['type'] == 'checkbox' ) ?>><?php _e( 'Checkbox', 'tribe-events-calendar-pro' ) ?></option>
					<option value="dropdown" <?php selected( isset( $field['type'] ) && $field['type'] == 'dropdown' ) ?>><?php _e( 'Dropdown', 'tribe-events-calendar-pro' ) ?></option>
				</select>
			</td>
			<td>
				<textarea name="custom-field-options[<?php echo $index ?>]" style='display: <?php echo ( isset( $field['type'] ) && ( $field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'dropdown' ) ) ? "inline" : "none" ?>;' data-name-template='custom-field-options' data-count='<?php echo esc_attr( $count ) ?>' rows="3"><?php echo stripslashes( esc_textarea( isset( $field['values'] ) ? $field['values'] : '' ) ) ?></textarea>
			</td>
			<td>
				<?php if ( $last_row ): ?>
					<a name="add-field" href='#add-field' class='add-another-field'><?php _e( 'Add another', 'tribe-events-calendar-pro' ) ?></a>
				<?php else: ?>
					<a name="remove-field" href='#remove-field' class='remove-another-field'><?php _e( 'Remove', 'tribe-events-calendar-pro' ) ?></a>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<p><?php printf( __( 'Enter the field label as you want it to appear (this will be the label in the same way "Start Date," "Organizer," etc appear in the event details box on the frontend). Select whether the field will be a text field; URL field; radio buttons; checkboxes; or a dropdown. All of these with the exception of text and URL allow for multiple options to be included, which you can add — one per-line — in the right-hand column. If you feel flummoxed, we\'ve got you covered with a %s.', 'tribe-events-calendar-pro' ),
		'<a href="' . TribeEvents::$tribeUrl . 'pro-adding-custom-events-attributes/?utm_campaign=in-app&utm_medium=plugin-ecp&utm_source=settings">' . __( 'video tutorial that will walk you through the process', 'tribe-events-calendar-pro' ) . '</a>'
	); ?></p>
<fieldset>
	<legend class="tribe-field-label"><?php _e( 'Editor "Custom Fields" meta box', 'tribe-events-calendar-pro' ); ?></legend>
	<div class="tribe-field-wrap">
		<label><input type="radio" name="disable_metabox_custom_fields" id="disable_metabox_custom_fields" value="show" <?php checked( 'show', $disable_metabox_custom_fields ); ?> /> <?php _e( 'Show', 'tribe-events-calendar-pro' ); ?>
		</label><br />
		<label><input type="radio" name="disable_metabox_custom_fields" id="disable_metabox_custom_fields" value="hide" <?php checked( 'hide', $disable_metabox_custom_fields ); ?> /> <?php _e( 'Hide', 'tribe-events-calendar-pro' ); ?>
		</label>

		<p class="description"><?php _e( 'Enabling this option this will not remove custom field data or functionality, just the default meta box editor.', 'tribe-events-calendar-pro' ); ?></p>
	</div>
</fieldset>

<script>
	jQuery(document).ready(function ($) {
		if ($('#additional-field-table').size() > 0) {
			$('#additional-field-table').delegate('.remove-another-field', 'click', function () {
				var row = $(this).closest('tr'), firstInput = row.find('td:first input'), data = {
					action: 'remove_option',
					field : firstInput.data('count')
				}, persisted = firstInput.data('persisted')
				if (confirm('<?php echo esc_js( __('Are you sure you wish to remove this field and its data from all events? Once you click OK this cannot be undone.','tribe-events-calendar-pro') ); ?>')) {
					if (persisted == "yes") {
						jQuery.post(ajaxurl, data, function (response) {
							row.fadeOut('slow', function () {
								$(this).remove();
							});
						});
					} else {
						row.fadeOut('slow', function () {
							$(this).remove();
						});
					}
				}
			});

			$('#additional-field-table').delegate('.add-another-field', 'click', function () {
				var table = $(this).closest('table tbody'), lastRow = table.find('tr:last'), newRow = lastRow.clone();

				lastRow.find('td:last').html(lastRow.prev().find('td:last').html());
				newRow.find('input, select, textarea').each(function () {
					var input = $(this), number = parseInt(input.data('count')) + 1;
					input.attr('name', input.data('name-template') + '[]');
					input.val('');
					input.attr('data-count', number);
				});

				table.append(newRow);
			});

			$('#additional-field-table').delegate('select', 'change', function () {
				var fieldType = $(this).find("option:selected").val();
				if (fieldType == 'radio' || fieldType == 'dropdown' || fieldType == 'checkbox') {
					$(this).closest('tr').find('textarea').show();
				} else {
					$(this).closest('tr').find('textarea').hide();
				}
			});
		}
	});
</script>
