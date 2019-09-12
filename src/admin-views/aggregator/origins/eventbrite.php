<?php
$tab                = $this->tabs->get_active();
$service            = tribe( 'events-aggregator.service' );
$origin_slug        = 'eventbrite';
$field              = (object) array();
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from Eventbrite on a set schedule. Single events can be added via a one-time import.', 'the-events-calendar' );
$field->source      = 'eventbrite_import_type';


$frequency              = (object) array();
$frequency->placeholder = __( 'Import from Eventbrite', 'the-events-calendar' );
if ( ! empty( $service->api()->licenses['tribe-eventbrite'] ) ) {
	$frequency->placeholder = __( 'Import from your Eventbrite account', 'the-events-calendar' );
}
$frequency->help   = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source = 'eventbrite_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();

$missing_eventbrite_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_eb();
$data_depends = '#tribe-ea-field-origin';
$data_condition = 'eventbrite';

if ( $missing_eventbrite_credentials ) :
	$data_depends = '#tribe-has-eventbrite-credentials';
	$data_condition = '1';
	?>
	<tr class="tribe-dependent tribe-credential-row" data-depends="#tribe-ea-field-origin" data-condition="eventbrite">
		<td colspan="2" class="<?php echo esc_attr( $missing_eventbrite_credentials ? 'enter-credentials' : 'has-credentials' ); ?>">
			<input type="hidden" name="has-credentials" id="tribe-has-eventbrite-credentials" value="0">
			<div class="tribe-message tribe-credentials-prompt">
				<p>
					<span class="dashicons dashicons-warning"></span>
					<?php
					esc_html_e(
						'Please log in to enable event imports from Eventbrite.',
						'the-events-calendar'
					);
					?>
				</p>
				<a class="tribe-ea-eventbrite-button tribe-ea-login-button" href="<?php echo esc_url( Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url() ); ?>"><?php esc_html_e( 'Log into Eventbrite', 'the-events-calendar' ); ?></a>
			</div>
		</td>
	</tr>
<?php endif; ?>
<tr class="tribe-dependent" data-depends="<?php echo esc_attr( $data_depends ); ?>" data-condition="<?php echo esc_attr( $data_condition ); ?>">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input type="hidden" name="has-credentials" id="tribe-has-eventbrite-credentials" value="<?php echo absint( ! $missing_eventbrite_credentials ); ?>">
		<?php if ( 'edit' === $aggregator_action ) : ?>
			<input type="hidden" name="aggregator[eventbrite][import_type]" id="tribe-ea-field-eventbrite_import_type" value="schedule" />
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<select
				name="aggregator[eventbrite][import_type]"
				id="tribe-ea-field-eventbrite_import_type"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-import-type"
				placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
				data-hide-search
				data-prevent-clear
			>
				<option value=""></option>
				<option value="manual"><?php echo esc_html__( 'One-Time Import', 'the-events-calendar' ); ?></option>
				<option value="schedule"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></option>
			</select>
		<?php endif; ?>

		<select
			name="aggregator[eventbrite][import_frequency]"
			id="tribe-ea-field-eventbrite_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-eventbrite_import_type"
			data-condition="schedule"
			data-prevent-clear
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( empty( $record->meta['frequency'] ) ? 'daily' : $record->meta['frequency'], $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
			<?php endforeach; ?>
		</select>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-eventbrite_import_type"
			data-condition-not="schedule"
			data-condition-empty
			data-width-rule="all-triggers"
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-eventbrite_import_type"
			data-condition="schedule"
			data-width-rule="all-triggers"
		></span>
	</td>
</tr>

<?php
if ( 'edit' === $tab->get_slug() ) {
	$this->template( 'fields/schedule', array( 'record' => $record, 'origin' => $origin_slug, 'aggregator_action' => $aggregator_action ) );
}
?>

<?php
$field              = (object) array();
$field->label       = __( 'Import Source', 'the-events-calendar' );
$field->placeholder = __( 'Select Source', 'the-events-calendar' );

if ( ! class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {
	$field->help = esc_html__( 'Import events directly from a public Eventbrite.com URL. Please note that only Live events (i.e. published events) can be imported via URL.', 'the-events-calendar' );
} else {
	$field->help = esc_html__( 'Import events directly from your connected Eventbrite.com account or from a public Eventbrite.com URL.', 'the-events-calendar' );
}

$default_eb_source  = 'source_type_url';
if ( ! empty( $service->api()->licenses['tribe-eventbrite'] ) ) {
	$field->options[]  = array(
		'id'   => 'https://www.eventbrite.com/me',
		'text' => __( 'Import from your Eventbrite account', 'the-events-calendar' ),
	);
	$default_eb_source = 'https://www.eventbrite.com/me';
}
$field->options[] = array(
	'id'   => 'source_type_url',
	'text' => __( 'Import from Eventbrite URL', 'the-events-calendar' ),
);
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-eventbrite_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			type="hidden"
			name="aggregator[eventbrite][source_type]"
			id="tribe-ea-field-eventbrite_import_source"
			class="tribe-ea-field tribe-dropdown tribe-ea-size-xlarge"
			data-hide-search
			data-prevent-clear
			data-options="<?php echo esc_attr( json_encode( $field->options ) ); ?>"
			value="<?php echo esc_attr( $default_eb_source ); ?>"
		/>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>

</tr>

<?php
$field              = (object) array();
$field->label       = __( 'URL:', 'the-events-calendar' );
$field->placeholder = __( 'eventbrite.com/e/example-12345', 'the-events-calendar' );
$field->help        = __( 'Enter an Eventbrite event URL, e.g. https://www.eventbrite.com/e/example-12345', 'the-events-calendar' );
?>
<tr
	class="tribe-dependent eb-url-row"
	data-depends="#tribe-ea-field-eventbrite_import_source"
	data-condition="source_type_url"
>
	<th scope="row">
		<label for="tribe-ea-field-eventbrite_source_type_url" class="tribe-ea-hidden">
			<input
				name="aggregator[eventbrite][source_type]"
				type="radio"
				id="tribe-ea-field-eventbrite_source_type_url"
				value=""
				checked="checked"
			>

			<?php echo esc_html( $field->label ); ?>
		</label>
	</th>
	<td>
	<input
		name="aggregator[eventbrite][source]"
		type="text"
		id="tribe-ea-field-eventbrite_source"
		class="tribe-ea-field tribe-ea-size-xlarge"
		placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		value="<?php echo esc_attr( empty( $record->meta['source'] ) ? '' : $record->meta['source'] ); ?>"
		data-validation-match-regexp="<?php echo esc_attr( Tribe__Events__Aggregator__Record__Eventbrite::get_source_regexp() ); ?>"
		data-validation-error="<?php esc_attr_e( 'Invalid Eventbrite URL', 'the-events-calendar' ); ?>"
	>
</td>
</tr>

<?php include dirname( __FILE__ ) . '/refine.php'; ?>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-eventbrite_import_source" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>
