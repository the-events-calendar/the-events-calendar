<?php
$origin_slug        = 'meetup';
$field              = (object) array();
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from Meetup on a set schedule. Single events can be added via a one-time import.', 'the-events-calendar' );
$field->source      = 'meetup_import_type';

$frequency              = (object) array();
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source      = 'meetup_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();

$meetup_api_key = tribe_get_option( 'meetup_api_key' );
$missing_meetup_credentials = ! $meetup_api_key;

?>
<tr class="tribe-dependent tribe-credential-row" data-depends="#tribe-ea-field-origin" data-condition="meetup">
	<td colspan="2" class="<?php echo esc_attr( $missing_meetup_credentials ? 'enter-credentials' : 'has-credentials' ); ?>">
		<?php
		if ( $missing_meetup_credentials ) :
			?>
			<input type="hidden" name="has-credentials" id="tribe-has-meetup-credentials" value="0">
			<div class="tribe-message tribe-credentials-prompt">
				<span class="dashicons dashicons-warning"></span>
				<?php
				printf(
					esc_html__(
						'Enter your Meetup API key to import Meetup events. %1$sClick here to get your Meetup API key%2$s. You only need to do this once, it will be saved under %3$sEvents &gt; Settings &gt; APIs%4$s',
						'the-events-calendar'
					),
					'<a href="https://secure.meetup.com/meetup_api/key/">',
					'</a>',
					'<a href="' . esc_url( Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) ) ) . '">',
					'</a>'
				);
				?>
			</div>
			<div class="tribe-message tribe-credentials-success">
				<span class="dashicons dashicons-yes"></span>
				<?php
				printf(
					esc_html__(
						'Your Meetup API key has been saved to %1$sEvents &gt; Settings &gt; APIs%2$s',
						'the-events-calendar'
					),
					'<a href="' . esc_url( Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) ) ) . '">',
					'</a>'
				);
				?>
			</div>
			<div class="tribe-fieldset">
				<?php wp_nonce_field( 'tribe-save-meetup-credentials' ); ?>
				<input type="hidden" name="tribe_credentials_which" value="meetup">
				<label for="meetup_api_key"><?php esc_html_e( 'Meetup API Key:', 'the-events-calendar' ); ?></label>
				<input type="text" name="meetup_api_key" id="meetup_api_key" value="<?php echo esc_attr( $meetup_api_key ); ?>">
				<button type="button" class="button tribe-save"><?php esc_html_e( 'Save', 'the-events-calendar' ); ?></button>
			</div>
			<?php
		else:
			?>
			<input type="hidden" name="has-credentials" id="tribe-has-meetup-credentials" value="1">
			<?php
		endif;
		?>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="meetup">
	<th scope="row" class="tribe-dependent" data-depends="#tribe-has-meetup-credentials" data-condition="1">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td class="tribe-dependent" data-depends="#tribe-has-meetup-credentials" data-condition="1">

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

		<select
			name="aggregator[meetup][import_frequency]"
			id="tribe-ea-field-meetup_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-meetup_import_type"
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
			data-depends="#tribe-ea-field-meetup_import_type"
			data-condition-not="schedule"
			data-condition-empty
			data-width-rule="all-triggers"
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-meetup_import_type"
			data-condition="schedule"
			data-width-rule="all-triggers"
		></span>
	</td>
</tr>

<?php
$field              = (object) array();
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
