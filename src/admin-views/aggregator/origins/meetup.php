<?php
$tab                = $this->tabs->get_active();
$origin_slug        = 'meetup';
$field              = (object) [];
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __(
	'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from Meetup on a set schedule. Single events can be added via a one-time import.',
	'the-events-calendar'
);
$field->source      = 'meetup_import_type';

$frequency              = (object) [];
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __(
	'Select how often you would like events to be automatically imported.',
	'the-events-calendar'
);
$frequency->source      = 'meetup_import_frequency';

$cron        = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();

$missing_meetup_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_meetup();
$data_depends               = '#tribe-ea-field-origin';
$data_condition             = 'meetup';

if ( $missing_meetup_credentials ) :
	$data_depends = '#tribe-has-meetup-credentials';
	$data_condition = '1';
	$meetup_auth_url = Tribe__Events__Aggregator__Record__Meetup::get_auth_url();
	?>
	<tr class="tribe-dependent tribe-credential-row" data-depends="#tribe-ea-field-origin" data-condition="meetup">
		<td colspan="2" class="<?php echo esc_attr( $missing_meetup_credentials ? 'enter-credentials' : 'has-credentials' ); ?>">
			<input type="hidden" name="has-credentials" id="tribe-has-meetup-credentials" value="0">
			<div class="tribe-message tribe-credentials-prompt">
				<p>
					<span class="dashicons dashicons-warning"></span>
					<?php
					esc_html_e(
						'Please log in to enable event imports from Meetup.',
						'the-events-calendar'
					);
					?>
				</p>
				<a class="tribe-ea-meetup-button tribe-ea-login-button" href="<?php echo esc_url( $meetup_auth_url ); ?>">
					<?php esc_html_e( 'Log into Meetup', 'the-events-calendar' ); ?>
				</a>
			</div>
		</td>
	</tr>
<?php endif; ?>

<tr class="tribe-dependent" data-depends="<?php echo esc_attr( $data_depends ); ?>"
    data-condition="<?php echo esc_attr( $data_condition ); ?>">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>

		<?php if ( 'edit' === $aggregator_action ) : ?>
			<input type="hidden" name="aggregator[meetup][import_type]" id="tribe-ea-field-meetup_import_type" value="schedule" />
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<select
				name="aggregator[meetup][import_type]"
				id="tribe-ea-field-meetup_import_type"
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

		<span
			data-depends="#tribe-ea-field-meetup_import_type"
			data-condition="schedule"
		>
			<select
				name="aggregator[meetup][import_frequency]"
				id="tribe-ea-field-meetup_import_frequency"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
				placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
				data-hide-search
				data-prevent-clear
			>
				<?php foreach ( $frequencies as $frequency_object ) : ?>
					<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( empty( $record->meta['frequency'] ) ? 'daily' : $record->meta['frequency'], $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
				<?php endforeach; ?>
			</select>
			<span
				class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
				data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
				data-width-rule="all-triggers"
			></span>
		</span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-meetup_import_type"
			data-condition-not="schedule"
			data-condition-empty
			data-width-rule="all-triggers"
		></span>
	</td>
</tr>

<?php
if ( 'edit' === $tab->get_slug() ) {
	$this->template(
		'fields/schedule',
		[ 'record' => $record, 'origin' => $origin_slug, 'aggregator_action' => $aggregator_action ]
	);
}
?>

<?php
$field              = (object) [];
$field->label       = __( 'URL:', 'the-events-calendar' );
$field->placeholder = __( 'meetup.com/example', 'the-events-calendar' );
$field->help        = __( 'Enter the url for a Meetup group, page, or individual. You can also enter the url of a single Meetup event.', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-meetup_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[meetup][source]"
			type="text"
			id="tribe-ea-field-meetup_source"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['source'] ) ? '' : $record->meta['source'] ); ?>"
			data-validation-match-regexp="<?php echo esc_attr( Tribe__Events__Aggregator__Record__Meetup::get_source_regexp() ); ?>"
			data-validation-error="<?php esc_attr_e( 'Invalid Meetup URL', 'the-events-calendar' ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-meetup_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>
