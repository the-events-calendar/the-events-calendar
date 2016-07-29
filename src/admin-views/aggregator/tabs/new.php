<?php
$post_statuses = get_post_statuses( array() );
$category_dropdown = array();
$category_dropdown = wp_dropdown_categories( array(
	'echo'       => false,
	'name'       => 'aggregator[category]',
	'id'         => 'tribe-ea-field-category',
	'hide_empty' => false,
	'class'      => 'tribe-ea-field tribe-ea-dropdown tribe-ea-size-large',
	'orderby'    => 'post_title',
	'taxonomy'   => Tribe__Events__Main::TAXONOMY,
) );
$category_dropdown = preg_replace( '!\<select!', '<select data-hide-search', $category_dropdown );
?>
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

<?php
$default_settings = new stdClass;
$default_settings->help = esc_html__( 'Choose a status for the event(s) to be imported with and/or define an Event Category to automatically assign.', 'the-events-calendar' );
?>
<div class="tribe-ea-table-container tribe-preview-container">
	<div class="tribe-fetch-error-message"></div>
	<div class="spinner-container">
		<span class='spinner tribe-ea-active'></span>
	</div>
	<div class="data-container">
		<p class="tribe-preview-message">
			<?php esc_html_e( 'This is a preview of the type of content you will be getting in during the import based on what is on the calendar now.', 'the-events-calendar' ); ?>
		</p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'the-events-calendar' ); ?></label>
						<input type="checkbox">
					</th>
					<th scope="col" class="tribe-column-start-date column-primary"><?php esc_html_e( 'Start Date', 'the-events-calendar' ); ?></th>
					<th scope="col" class="tribe-column-end-date"><?php esc_html_e( 'End Date', 'the-events-calendar' ); ?></th>
					<th scope="col" class="tribe-column-event"><?php esc_html_e( 'Event', 'the-events-calendar' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'the-events-calendar' ); ?></label>
						<input type="checkbox">
					</th>
					<th scope="col"><?php esc_html_e( 'Start Date', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'End Date', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Event', 'the-events-calendar' ); ?></th>
				</tr>
			</tfoot>
		</table>
		<div class="tribe-default-settings">
			<label for="tribe-ea-field-post_status"><?php esc_html_e( 'Status:', 'the-events-calendar' ); ?></label>
			<select
				name="aggregator[post_status]"
				id="tribe-ea-field-post_status"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
				data-hide-search
			>
				<?php foreach ( $post_statuses as $slug => $post_status ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( 'draft', $slug ); ?>><?php echo esc_html( $post_status ); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="tribe-ea-field-category"><?php esc_html_e( 'Category:', 'the-events-calendar' ); ?></label>
			<?php echo $category_dropdown; ?>
			<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo $default_settings->help; ?>"></span>
		</div>
	</div>
</div>
<div class="tribe-finalize-container">
	<button type="submit" class="button button-primary tribe-finalize">
		<?php esc_html_e( 'Import', 'the-events-calendar' ); ?>
	</button>
	<p class="tribe-timezone-message">
		<?php esc_html_e( 'Events will be imported with the timezone defined by the source. You can make use of The Event Calendar\'s timezone settings to change how the actual time is displayed on your calendar.', 'the-events-calendar' ); ?>
	</p>
</div>
