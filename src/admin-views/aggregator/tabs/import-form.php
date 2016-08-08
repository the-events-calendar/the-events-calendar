<?php
$has_license_key = ! empty( Tribe__Events__Aggregator__Service::instance()->api()->key );

if ( 'edit' === $aggregator_action ) {
	$default_post_status = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'post_status', true );
	$default_category = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'category', true );
}

$default_post_status = empty( $default_post_status ) ? tribe_get_option( 'tribe_aggregator_default_post_status', 'draft' ) : $default_post_status;
$default_category = empty( $default_category ) ? tribe_get_option( 'tribe_aggregator_default_category', '' ) : $default_category;

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
	'selected'   => $default_category,
) );
$category_dropdown = preg_replace( '!\<select!', '<select data-hide-search', $category_dropdown );
$category_dropdown = preg_replace( '!(\<select[^\>]*\>)!', '$1<option value=""></option>', $category_dropdown );

wp_nonce_field( 'tribe-aggregator-save-import', 'tribe_aggregator_nonce' );
?>
<input type="hidden" name="aggregator[action]" id="tribe-action" value="<?php echo esc_attr( $aggregator_action ); ?>">
<input type="hidden" name="aggregator[import_id]" id="tribe-import_id" value="<?php echo esc_attr( empty( $record->meta['import_id'] ) ? '' : $record->meta['import_id'] ); ?>">
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
				<select
					name="aggregator[origin]"
					id="tribe-ea-field-origin"
					class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium"
					placeholder="<?php echo $field->placeholder; ?>"
					data-hide-search
				>
					<option value=""></option>
					<?php
					foreach ( $field->options as $option ) {
						?>
						<option value="<?php echo esc_attr( $option->id ); ?>" <?php selected( $option->id, empty( $record->meta['origin'] ) ? '' : $record->meta['origin'] ); ?>><?php esc_html_e( $option->text ); ?></option>
						<?php
					}
					?>
				</select>
				<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
			</td>
		</tr>

		<?php
		$this->template( 'origins/csv', array( 'record' => $record ) );
		$this->template( 'origins/ics', array( 'record' => $record ) );
		$this->template( 'origins/ical', array( 'record' => $record ) );
		$this->template( 'origins/facebook', array( 'record' => $record ) );
		$this->template( 'origins/meetup', array( 'record' => $record ) );
		?>

	</tbody>
</table>

<?php
$default_settings = new stdClass;
$default_settings->help = esc_html__( 'Choose a status for the event(s) to be imported with and/or define an Event Category to automatically assign.', 'the-events-calendar' );
$csv_help = esc_html__( 'Select the Event Field that best matches your CSV file column. The contents of that column will then be mapped to the specified event field when the event is created.', 'the-events-calendar' );
?>
<div class="tribe-ea-table-container tribe-preview-container">
	<div class="tribe-fetch-error-message"></div>
	<div class="spinner-container">
		<span class='spinner tribe-ea-active'></span>
	</div>
	<div class="data-container">
		<p class="tribe-preview-message" id="tribe-remove-preview-message">
			<?php esc_html_e( 'This is a preview of the type of content you will be getting in during the import based on what is on the calendar now.', 'the-events-calendar' ); ?>
		</p>
		<p class="tribe-preview-message" id="tribe-csv-preview-message">
			<?php esc_html_e( 'Column Mapping:', 'the-events-calendar' ); ?>
			<span class="tribe-csv-filename"></span>
			<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $csv_help ); ?>"></span>
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
	</div>
	<div class="tribe-default-settings">
		<label for="tribe-ea-field-post_status"><?php esc_html_e( 'Status:', 'the-events-calendar' ); ?></label>
		<select
			name="aggregator[post_status]"
			id="tribe-ea-field-post_status"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			data-hide-search
		>
			<option value=""></option>
			<?php foreach ( $post_statuses as $slug => $post_status ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $default_post_status, $slug ); ?>><?php echo esc_html( $post_status ); ?></option>
			<?php endforeach; ?>
		</select>
		<label for="tribe-ea-field-category"><?php esc_html_e( 'Category:', 'the-events-calendar' ); ?></label>
		<?php echo $category_dropdown; ?>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo $default_settings->help; ?>"></span>
	</div>
	<textarea style="display:none;" name="aggregator[selected_rows]" id="tribe-selected-rows"></textarea>
</div>
<div class="tribe-finalize-container">
	<button type="submit" class="button button-primary tribe-finalize">
		<?php esc_html_e( 'Import', 'the-events-calendar' ); ?>
	</button>
	<button type="cancel" class="button tribe-cancel">
		<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
	</button>
	<p class="tribe-timezone-message">
		<?php esc_html_e( 'Events will be imported with the timezone defined by the source. You can make use of The Event Calendar\'s timezone settings to change how the actual time is displayed on your calendar.', 'the-events-calendar' ); ?>
	</p>
</div>
<script id="tribe-csv-column-map-events" type="text/html">
	<?php
	$event_mapper = new Tribe__Events__Importer__Column_Mapper( 'events' );
	echo $event_mapper->make_select_box( '' );
	?>
</script>
<script id="tribe-csv-column-map-organizers" type="text/html">
	<?php
	$organizers_mapper = new Tribe__Events__Importer__Column_Mapper( 'organizers' );
	echo $organizers_mapper->make_select_box( '' );
	?>
</script>
<script id="tribe-csv-column-map-events" type="text/html">
	<?php
	$venues_mapper = new Tribe__Events__Importer__Column_Mapper( 'venues' );
	echo $venues_mapper->make_select_box( '' );
	?>
</script>
