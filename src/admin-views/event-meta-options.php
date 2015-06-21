<div id="modern-tribe-info">

	<h2><?php esc_html_e( 'Additional Fields', 'tribe-events-calendar-pro' ); ?></h2>

	<p><?php _e( 'Use additional fields to add unique content fields to the event admin, which can be used by anyone who creates events. Upon publication they\'ll appear in the event metabox that accompanies your event listing, alongside the date/time/venue/organizer/etc. </p><p>Each new piece of data needs:', 'tribe-events-calendar-pro' ) . '</p>'; ?>
	<ul class="admin-list">
		<li style="list-style:inside;"><?php esc_html_e( 'Label (e.g. Meal Plans)', 'tribe-events-calendar-pro' ); ?></li>
		<li style="list-style:inside;"><?php esc_html_e( 'At least one option (e.g. Vegetarian, Kosher, Paleo)', 'tribe-events-calendar-pro' ); ?></li>
		<li style="list-style:inside;"><?php esc_html_e( 'Field type. These are:', 'tribe-events-calendar-pro' ); ?></li>
		<ul class="admin-list" style="margin-left:20px;">
			<li style="list-style:inside circle;"><?php esc_html_e( 'Text - for the user to input text', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php esc_html_e( 'Text Area - identical to a text field but providing a larger area for the user to input text', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php esc_html_e( 'URL - for the user to input a URL', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php esc_html_e( 'Checkbox - for multiple choice', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php esc_html_e( 'Radio Button - to select only one', 'tribe-events-calendar-pro' ); ?></li>
			<li style="list-style:inside circle;"><?php esc_html_e( 'Dropdown Menu - for a dropdown menu', 'tribe-events-calendar-pro' ); ?></li>
		</ul>
	</ul>
</div>
<table class='wp-list-table widefat' id="additional-field-table" style=''>
	<thead>
	<tr>
		<th><?php esc_html_e( 'Field Label', 'tribe-events-calendar-pro' ); ?></th>
		<th><?php esc_html_e( 'Field Type', 'tribe-events-calendar-pro' ); ?></th>
		<th><?php esc_html_e( 'Options (one per line)', 'tribe-events-calendar-pro' ); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $customFields as $field ) : // Track our progress through the list of custom fields
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
				<input type="text" name="custom-field[<?php echo esc_attr( $index ); ?>]" data-persisted='<?php echo $count != count( $field ) ? 'yes' : 'no' ?>' data-name-template='custom-field' data-count='<?php echo esc_attr( $count ) ?>' value="<?php echo isset( $field['label'] ) ? esc_attr( stripslashes( $field['label'] ) ) : ''; ?>" />
			</td>
			<td>
				<select name="custom-field-type[<?php echo esc_attr( $index ); ?>]" data-name-template='custom-field-type' data-count='<?php echo esc_attr( $count ); ?>'>
					<option value="text" <?php selected( isset( $field['type'] ) && $field['type'] == 'text' ) ?>><?php esc_html_e( 'Text', 'tribe-events-calendar-pro' ) ?></option>
					<option value="textarea" <?php selected( isset( $field['type'] ) && $field['type'] == 'textarea' ) ?>><?php esc_html_e( 'Text Area', 'tribe-events-calendar-pro' ) ?></option>
					<option value="url" <?php selected( isset( $field['type'] ) && $field['type'] == 'url' ) ?>><?php esc_html_e( 'URL', 'tribe-events-calendar-pro' ) ?></option>
					<option value="radio" <?php selected( isset( $field['type'] ) && $field['type'] == 'radio' ) ?>><?php esc_html_e( 'Radio', 'tribe-events-calendar-pro' ) ?></option>
					<option value="checkbox" <?php selected( isset( $field['type'] ) && $field['type'] == 'checkbox' ) ?>><?php esc_html_e( 'Checkbox', 'tribe-events-calendar-pro' ) ?></option>
					<option value="dropdown" <?php selected( isset( $field['type'] ) && $field['type'] == 'dropdown' ) ?>><?php esc_html_e( 'Dropdown', 'tribe-events-calendar-pro' ) ?></option>
				</select>
			</td>
			<td>
				<textarea name="custom-field-options[<?php echo esc_attr( $index ); ?>]" style='display: <?php echo ( isset( $field['type'] ) && ( $field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'dropdown' ) ) ? 'inline' : 'none' ?>;' data-name-template='custom-field-options' data-count='<?php echo esc_attr( $count ); ?>' rows="3"><?php echo stripslashes( esc_textarea( isset( $field['values'] ) ? $field['values'] : '' ) ) ?></textarea>
			</td>
			<td class="add-remove-actions"></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<p><?php printf( __( 'Enter the field label as you want it to appear (this will be the label in the same way "Start Date," "Organizer," etc appear in the event details box on the frontend). Select whether the field will be a text field; URL field; radio buttons; checkboxes; or a dropdown. All of these with the exception of text and URL allow for multiple options to be included, which you can add — one per-line — in the right-hand column. If you feel flummoxed, we\'ve got you covered with a %s.', 'tribe-events-calendar-pro' ),
		'<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'knowledgebase/pro-additional-fields/?utm_campaign=in-app&utm_medium=plugin-ecp&utm_source=settings' ) . '">' . __( 'tutorial that will walk you through the process', 'tribe-events-calendar-pro' ) . '</a>'
	); ?></p>
<fieldset>
	<legend class="tribe-field-label"><?php esc_html_e( 'Editor "Custom Fields" meta box', 'tribe-events-calendar-pro' ); ?></legend>
	<div class="tribe-field-wrap">
		<label><input type="radio" name="disable_metabox_custom_fields" id="disable_metabox_custom_fields" value="show" <?php checked( 'show', $disable_metabox_custom_fields ); ?> /> <?php esc_html_e( 'Show', 'tribe-events-calendar-pro' ); ?>
		</label><br />
		<label><input type="radio" name="disable_metabox_custom_fields" id="disable_metabox_custom_fields" value="hide" <?php checked( 'hide', $disable_metabox_custom_fields ); ?> /> <?php esc_html_e( 'Hide', 'tribe-events-calendar-pro' ); ?>
		</label>

		<p class="description"><?php esc_html_e( 'Enabling this option this will not remove custom field data or functionality, just the default meta box editor.', 'tribe-events-calendar-pro' ); ?></p>
	</div>
</fieldset>

<script>
	jQuery(document).ready(function ($) {
		var fields_tbl  = $( "#additional-field-table" );
		var tbl_body    = fields_tbl.find( "tbody" );
		var add_new_tpl = "<a name='add-field' href='#add-field' class='add-another-field'><?php echo esc_js( $add_another ) ?></a>";
		var remove_tpl  = "<a name='remove-field' href='#remove-field' class='remove-another-field'><?php echo esc_js( $remove_field ) ?></a>";

		/**
		 * Ensures the correct action link is present for each row in the table.
		 */
		function refresh_add_remove_links() {
			var rows     = tbl_body.find("tr");
			var num_rows = rows.length;
			var count    = 0;

			// Insert the remove link for every row but the final one (which should contain the add new link)
			$.each( rows, function( index, object ) {
				if ( ++count == num_rows ) $( object ).find( ".add-remove-actions" ).html( add_new_tpl );
				else $( object ).find( ".add-remove-actions" ).html( remove_tpl );
			} );
		}

		// Set up the add/remove links as soon as the page is ready
		refresh_add_remove_links();

		if (fields_tbl.size() > 0) {
			fields_tbl.delegate('.remove-another-field', 'click', function () {
				var row = $(this).closest('tr'), firstInput = row.find('td:first input'), data = {
					action: 'remove_option',
					field : firstInput.data('count')
				}, persisted = firstInput.data('persisted')
				if ( confirm( '<?php echo esc_js( __( 'Are you sure you wish to remove this field and its data from all events? Once you click OK this cannot be undone.', 'tribe-events-calendar-pro' ) ); ?>' ) ) {
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
				var lastRow = tbl_body.find('tr:last'), newRow = lastRow.clone();

				lastRow.find('td:last').html(lastRow.prev().find('td:last').html());
				newRow.find('input, select, textarea').each(function () {
					var input = $(this), number = parseInt(input.data('count')) + 1;
					input.attr('name', input.data('name-template') + '[]');
					input.val('');
					input.attr('data-count', number);
				});

				tbl_body.append(newRow);
				refresh_add_remove_links()
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
