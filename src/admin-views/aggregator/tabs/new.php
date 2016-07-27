<table class="form-table">
	<tbody>

		<?php
		$field = (object) array();
		$field->source = 'origins';
		$field->label = esc_html__( 'Import Origin', 'the-events-calendar' );
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

		<tr class="tribe-preview-container">
			<td colspan="2">
				<div class="tribe-ea-table-container">
					<div class="spinner-container">
						<span class='spinner tribe-ea-active'></span>
					</div>
				</div>
			</td>
		</tr>

	</tbody>
</table>
