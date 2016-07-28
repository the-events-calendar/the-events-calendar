<table class="form-table">
	<tbody>

		<?php
		$field = (object) array();
		$field->source = 'origins';
		$field->label = esc_html__( 'Import Origin:', 'the-events-calendar' );
		$field->placeholder = esc_attr__( 'Select Origin', 'the-events-calendar' );
		$field->help = esc_attr__( 'Specify the type of data you wish to import', 'the-events-calendar' );
		$field->options = Tribe__Events__Aggregator::instance()->api( 'origins' )->get();
		?>
		<tr>
			<th scope="row">
				<label for="tribe-ea-field-origin"><?php echo $field->label; ?></label>
			</th>
			<td>
				<input
					name="aggregator[origin]"
					type="hidden"
					id="tribe-ea-field-origin"
					class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium"
					placeholder="<?php echo $field->placeholder; ?>"
					data-hide-search
					data-options="<?php echo esc_attr( json_encode( $field->options ) ); ?>">
				<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo $field->help; ?>"></span>
			</td>
		</tr>

		<?php
		$this->template( 'origins/csv' );
		$this->template( 'origins/ics' );
		$this->template( 'origins/ical' );
		$this->template( 'origins/facebook' );
		$this->template( 'origins/meetup' );
		?>

	</tbody>
</table>

<div class="tribe-ea-table-container tribe-preview-container">
	<div class="tribe-fetch-error-message"></div>
	<div class="spinner-container">
		<span class='spinner tribe-ea-active'></span>
	</div>
	<script type="text/template" id="tmpl-preview">
		<p class="tribe-preview-message">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<# if ( data.display_checkboxes ) { #>
						<th scope="col" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'the-events-calendar' ); ?></label>
							<input type="checkbox">
						</th>
					<# } #>
					<th scope="col" class="tribe-column-start-date column-primary"><?php esc_html_e( 'Start Date', 'the-events-calendar' ); ?></th>
					<th scope="col" class="tribe-column-end-date"><?php esc_html_e( 'End Date', 'the-events-calendar' ); ?></th>
					<th scope="col" class="tribe-column-event"><?php esc_html_e( 'Event', 'the-events-calendar' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<# _.each( data.events, function( event ) { #>
					<tr>
						<# if ( data.display_checkboxes ) { #>
							<th scope="row" class="check-column"><input type="checkbox"></th>
						<# } #>
						<td>{{ event.start_date }}</td>
						<td>{{ event.end_date }}</td>
						<td>{{ event.title }}</td>
					</tr>
				<# }); #>
			</tbody>
			<tfoot>
				<tr>
					<# if ( data.display_checkboxes ) { #>
						<th scope="col" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'the-events-calendar' ); ?></label>
							<input type="checkbox">
						</th>
					<# } #>
					<th scope="col"><?php esc_html_e( 'Start Date', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'End Date', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Event', 'the-events-calendar' ); ?></th>
				</tr>
			</tfoot>
		</table>
	</script>
</div>
