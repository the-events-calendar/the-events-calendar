<?php
$has_license_key = tribe( 'events-aggregator.main' )->is_service_active();
$hide_upsell     = defined( 'TRIBE_HIDE_UPSELL' );

if ( 'edit' === $aggregator_action ) {
	$default_post_status = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'post_status', true );
	$default_category = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'category', true );
}

// Set up the generic default post statuses and category.
$default_post_status = empty( $default_post_status ) ? tribe_get_option( 'tribe_aggregator_default_post_status', 'draft' ) : $default_post_status;
$default_category    = empty( $default_category ) ? tribe_get_option( 'tribe_aggregator_default_category', '' ) : $default_category;
$post_statuses       = get_post_statuses();

// Ensure the "(do not override)" status is set up for Eventbrite imports, and "Published" is removed.
$do_not_override_status   = [ 'do_not_override' => esc_html__( '(do not override)', 'the-events-calendar' ) ];
$eventbrite_post_statuses = $do_not_override_status + $post_statuses;
unset( $eventbrite_post_statuses['publish'] );

$category_placeholder = esc_attr__( 'No Additional Categories', 'the-events-calendar' );

$category_dropdown = wp_dropdown_categories( [
	'echo'       => false,
	'name'       => 'aggregator[category]',
	'id'         => 'tribe-ea-field-category',
	'hide_empty' => false,
	'class'      => 'tribe-ea-field tribe-ea-dropdown tribe-ea-size-large',
	'orderby'    => 'post_title',
	'taxonomy'   => Tribe__Events__Main::TAXONOMY,
] );
$category_dropdown = preg_replace( '!\<select!', '<select data-hide-search', $category_dropdown );
$category_dropdown = preg_replace( '!\<select!', sprintf( '<select placeholder="%s"', $category_placeholder ), $category_dropdown );
$category_dropdown = preg_replace( '!(\<select[^\>]*\>)!', '$1<option value="">' . $category_placeholder . '</option>', $category_dropdown );
$category_dropdown = preg_replace( '!(value="' . $default_category . '")!', '$1 selected', $category_dropdown );

wp_nonce_field( 'tribe-aggregator-save-import', 'tribe_aggregator_nonce' );
?>
<input type="hidden" name="aggregator[action]" id="tribe-action" value="<?php echo esc_attr( $aggregator_action ); ?>">
<input type="hidden" name="aggregator[import_id]" id="tribe-import_id" value="<?php echo esc_attr( empty( $record->meta['import_id'] ) ? '' : $record->meta['import_id'] ); ?>">
<table class="form-table">
	<tbody>

		<?php
		$field                 = (object) [];
		$field->source         = 'origins';
		$field->label          = esc_html__( 'Import Origin:', 'the-events-calendar' );
		$field->placeholder    = esc_attr__( 'Select Origin', 'the-events-calendar' );
		$field->help           = esc_attr__( 'Choose where you are importing from.', 'the-events-calendar' );
		$field->options        = tribe( 'events-aggregator.main' )->api( 'origins' )->get();
		$field->upsell_options = [];
		foreach ( $field->options as $key => $option ) {

			$option->disabled = isset( $option->disabled ) ? $option->disabled : null;
			$option->upsell   = isset( $option->upsell ) ? $option->upsell : false;

			$option->is_selected = false;

			if (
				// Used on the EA Authorization
				tribe_get_request_var( 'ea-auth', false ) === $option->id
				// Used to Select a given origin when the page loads
				|| tribe_get_request_var( 'ea-origin', false ) === $option->id
			) {
				$option->is_selected = true;
			}

			// If this is an upsell option we move it to that optgroup
			if ( $option->disabled && $option->upsell && ! $has_license_key ) {
				$field->upsell_options[] = $option;
				unset( $field->options[ $key ] );
			}
		}
		?>
		<tr>
			<th scope="row">
				<label for="tribe-ea-field-origin"><?php echo $field->label; ?></label>
			</th>
			<td>
				<?php if ( 'edit' === $aggregator_action ) : ?>
					<input
						type="hidden"
						name="aggregator[origin]"
						id="tribe-ea-field-origin"
						class="tribe-ea-field"
						value="<?php echo esc_attr( $record->meta['origin'] ); ?>"
					>
					<strong class="tribe-ea-field-readonly"><?php esc_html_e( tribe( 'events-aggregator.main' )->api( 'origins' )->get_name( $record->meta['origin'] ) ); ?></strong>
				<?php else: ?>
					<select
						name="aggregator[origin]"
						id="tribe-ea-field-origin"
						class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
						placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
						data-hide-search
						data-prevent-clear
					>
						<option></option>
						<?php foreach ( $field->options as $option ) : ?>
							<option
								value="<?php echo esc_attr( $option->id ); ?>"
								<?php disabled( $option->disabled ); ?>
								<?php selected( $option->is_selected ); ?>
								<?php if ( isset( $option->subtitle ) ) : ?>
									data-subtitle="<?php echo esc_attr( $option->subtitle ); ?>"
								<?php endif; ?>
							><?php echo esc_html( $option->name ); ?></option>
						<?php endforeach; ?>
						<?php if ( ! $hide_upsell && ! empty( $field->upsell_options ) ) : ?>
							<optgroup label="<?php esc_attr_e( 'Add more sources', 'the-events-calendar' ); ?>">
							<option
								value="redirect"
								data-subtitle="<?php esc_attr_e( 'Access more event sources and automatic imports!', 'the-events-calendar' ); ?>"
							><?php esc_html_e( 'Buy Event Aggregator', 'the-events-calendar' ); ?></option>
							<?php foreach ( $field->upsell_options as $option ) : ?>
								<option
									value="<?php echo esc_attr( $option->id ); ?>"
									<?php disabled( $option->disabled ); ?>
									<?php selected( $option->is_selected ); ?>
									<?php if ( isset( $option->subtitle ) ) : ?>
										data-subtitle="<?php echo esc_attr( $option->subtitle ); ?>"
									<?php endif; ?>
								><?php echo esc_html( $option->name ); ?></option>
							<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>
				<?php endif; ?>
				<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
			</td>
		</tr>

		<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition-not-empty>
			<th scope="row">
				<label for="tribe-ea-field-import_name"><?php echo esc_html__( 'Import Name:', 'the-events-calendar' ); ?></label>
			</th>
			<td>
				<div class="tribe-refine">
					<input
						name="aggregator[import_name]"
						type="text"
						id="tribe-ea-field-import_name"
						class="tribe-ea-field tribe-ea-size-large"
						placeholder="<?php echo esc_attr__( 'Optional', 'the-events-calendar' ); ?>"
						value="<?php echo esc_attr( empty( $record->meta['import_name'] ) ? '' : $record->meta['import_name'] ); ?>"
					>
				</div>
			</td>
		</tr>

		<?php
		$form_args = [
			'record'            => $record,
			'aggregator_action' => $aggregator_action,
		];

		if ( 'edit' === $aggregator_action ) {
			$this->template( 'origins/' . $record->meta['origin'], $form_args );
		} else {
			$this->template( 'origins/limit', $form_args );
			$this->template( 'origins/csv', $form_args );
			$this->template( 'origins/ics', $form_args );
			$this->template( 'origins/ical', $form_args );
			$this->template( 'origins/gcal', $form_args );
			$this->template( 'origins/meetup', $form_args );
			$this->template( 'origins/eventbrite', $form_args );
			$this->template( 'origins/url', $form_args );
		}

		/**
		 * Fires below the origin template output.
		 *
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains one <th> and one <td>.
		 *
		 * @since 4.6.24
		 *
		 * @param string $aggregator_action Aggregator action (new or edit).
		 * @param array  $form_args         Form arguments.
		 */
		do_action( 'tribe_events_aggregator_import_form', $aggregator_action, $form_args );
		?>

	</tbody>
</table>

<?php
$default_settings = new stdClass;
$default_settings->help = esc_html__( 'Choose a status for the event(s) to be imported with and/or define an Event Category to automatically assign. An assigned category will be added to the event in addition to any Event Categories from the import source.', 'the-events-calendar' );
$default_settings->help_scheduled = $default_settings->help . ' ' . esc_html__( 'These settings will also apply to events imported in the future via this scheduled import.', 'the-events-calendar' );

$csv_help = esc_html__( 'Select the Event Field that best matches your CSV file column. The contents of that column will then be mapped to the specified event field when the event is created.', 'the-events-calendar' );

$scheduled_save_help = esc_html__( 'When you save this scheduled import, the events above will begin importing.', 'the-events-calendar' );
?>
<div class="tribe-ea-table-container tribe-preview-container">
	<div class="tribe-fetch-warning-message"></div>
	<div class="tribe-fetch-error-message"></div>
	<div class="spinner-container">
		<span class="spinner tribe-ea-active"></span>
		<span class="spinner-message"></span>
	</div>
	<div class="data-container">
		<p class="tribe-preview-message" id="tribe-remove-preview-message">
			<?php esc_html_e( 'This is a preview of the type of content you will be getting in during the import based on what is on the calendar now.', 'the-events-calendar' ); ?>
		</p>
		<p class="tribe-preview-message" id="tribe-csv-preview-message">
			<?php esc_html_e( 'Column Mapping:', 'the-events-calendar' ); ?>
			<span class="tribe-csv-filename"></span>
			<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $csv_help ); ?>" data-width-rule="all-triggers"></span>
		</p>
		<p class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="csv">
			<?php
			esc_html_e(
				'The following preview does not necessarily contain all of the data from your CSV file. The data displayed below is meant as a guide to help you map your CSV file\'s columns to the appropriate Event fields.',
				'the-events-calendar'
			);
			?>
		</p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'the-events-calendar' ); ?></label>
						<input type="checkbox">
					</th>
					<th scope="col" class="tribe-column-start-date column-primary"><?php esc_html_e( 'Start Date', 'the-events-calendar' ); ?></th>
					<th scope="col" class="tribe-column-start-time column-primary"><?php esc_html_e( 'Start Time', 'the-events-calendar' ); ?></th>
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
					<th scope="col"><?php esc_html_e( 'Start Time', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'End Date', 'the-events-calendar' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Event', 'the-events-calendar' ); ?></th>
				</tr>
			</tfoot>
		</table>
	</div>

	<div class="tribe-default-settings">

		<div class="tribe-dependent tribe-ea-field-wrapper-post_status" data-depends="#tribe-ea-field-origin" data-condition="eventbrite">

			<label for="tribe-ea-field-post_status"><?php esc_html_e( 'Status:', 'the-events-calendar' ); ?></label>
			<select
				name="aggregator[post_status]"
				id="tribe-ea-field-post_status"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-ea-field-post_status"
				data-hide-search
			>
				<option value=""><?php esc_html__( '(do not override)', 'the-events-calendar' ); ?></option>
				<?php foreach ( $eventbrite_post_statuses as $slug => $post_status ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $default_post_status, $slug ); ?>><?php echo esc_html( $post_status ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="tribe-dependent tribe-ea-field-wrapper-post_status" data-depends="#tribe-ea-field-origin" data-condition-not="eventbrite">

			<label for="tribe-ea-field-post_status"><?php esc_html_e( 'Status:', 'the-events-calendar' ); ?></label>
			<select
				name="aggregator[post_status]"
				id="tribe-ea-field-post_status"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-ea-field-post_status"
				data-hide-search
			>
				<option value=""></option>
				<?php foreach ( $post_statuses as $slug => $post_status ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $default_post_status, $slug ); ?>><?php echo esc_html( $post_status ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<label for="tribe-ea-field-category"><?php esc_html_e( 'Category:', 'the-events-calendar' ); ?></label>
		<?php echo $category_dropdown; ?>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-bumpdown-manual"
			data-bumpdown="<?php echo esc_attr( $default_settings->help ); ?>"
			data-width-rule="all-triggers"
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-bumpdown-scheduled"
			data-bumpdown="<?php echo esc_attr( $default_settings->help_scheduled ); ?>"
			data-width-rule="all-triggers"
		></span>
	</div>

	<?php
	/**
	 * Allows output of custom import preview options.
	 *
	 * @since 5.1.0
	 */
	do_action( 'tribe_events_aggregator_import_form_preview_options' );
	?>

	<textarea style="display:none;" name="aggregator[selected_rows]" id="tribe-selected-rows"></textarea>
</div>
<div class="tribe-finalize-container">
	<button type="submit" class="button button-primary tribe-finalize">
		<?php esc_html_e( 'Import', 'the-events-calendar' ); ?>
	</button>
	<button type="button" class="button tribe-cancel">
		<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
	</button>
	<span
		class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
		data-bumpdown="<?php echo esc_attr( $scheduled_save_help ); ?>"
		data-depends="#tribe-ea-field-ical_import_type"
		data-condition="schedule"
		data-width-rule="all-triggers"
	></span>
	<span
		class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
		data-bumpdown="<?php echo esc_attr( $scheduled_save_help ); ?>"
		data-depends="#tribe-ea-field-gcal_import_type"
		data-condition="schedule"
		data-width-rule="all-triggers"
	></span>
	<span
		class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
		data-bumpdown="<?php echo esc_attr( $scheduled_save_help ); ?>"
		data-depends="#tribe-ea-field-meetup_import_type"
		data-condition="schedule"
		data-width-rule="all-triggers"
	></span>

	<div class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="eventbrite">
		<p class="tribe-timezone-message">
			<?php echo sprintf(
				'%1$s %2$s%3$s%4$s %5$s',
					esc_html__( 'Events will be imported with the same timezone as defined on eventbrite.com. You can make use of The Events Calendar\'s', 'the-events-calendar' ),
				'<a href="' . esc_url( Tribe__Settings::instance()->get_url() ) . '#tribe-field-tribe_events_timezone_mode">',
					esc_html__( 'timezone settings', 'the-events-calendar' ),
					'</a>',
					esc_html__( 'to change how the actual time is displayed on your calendar.', 'the-events-calendar' )
				);
			?>
		</p>
	</div>
	<div class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition-not="eventbrite">
		<p class="tribe-limits-message">
			<?php echo sprintf( esc_html__( 'The number of events available in the preview may be limited by your %1$sImport Settings.%2$s', 'the-events-calendar' ),
				'<a href="' . esc_url( admin_url( '/edit.php?post_type=tribe_events&page=tribe-common&tab=imports#tribe-field-tribe_aggregator_default_import_limit_type' ) ) . '#tribe-field-tribe_events_timezone_mode">',
				'</a>' ); ?>
		</p>
	</div>
	<div class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition-not="eventbrite">
		<p class="tribe-timezone-message">
			<?php echo sprintf(
				'%1$s %2$s%3$s%4$s',
					esc_html__( 'Events will be imported with the time zone defined by the source. If no time zone is specified, events will be assigned your site\'s default time zone ( see', 'the-events-calendar' ),
				'<a href="' . esc_url( Tribe__Settings::instance()->get_url() ) . '#tribe-field-tribe_events_timezone_mode">',
					esc_html__( 'Settings > General', 'the-events-calendar' ),
					'</a> ).'
				);
			?>
		</p>
	</div>
	<?php echo Tribe__Events__Aggregator__Tabs__New::instance()->maybe_display_eventbrite_upsell(); ?>
</div>
<?php
echo Tribe__Events__Aggregator__Tabs__New::instance()->maybe_display_aggregator_upsell();

$csv_record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( 'csv' );
$post_types = $csv_record->get_import_post_types();
foreach ( $post_types as $post_type ) :
	$type = str_replace( 'tribe_', '', $post_type->name );
	?>
	<script id="tribe-csv-column-map-<?php echo esc_attr( $type ); ?>" type="text/html">
		<?php
		$mapper = new Tribe__Events__Importer__Column_Mapper( $type );
		echo $mapper->make_select_box( '' );
		?>
	</script>
	<?php
endforeach;
