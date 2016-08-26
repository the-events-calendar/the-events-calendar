<?php
$origin_slug        = 'facebook';
$field              = (object) array();
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from Facebook on a set schedule. Single events can be added via a one-time import.', 'the-events-calendar' );
$field->source      = 'facebook_import_type';

$frequency              = (object) array();
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source      = 'facebook_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();

$fb_token = tribe_get_option( 'fb_token' );
$fb_token_expires = tribe_get_option( 'fb_token_expires' );
$fb_token_scopes = tribe_get_option( 'fb_token_scopes' );
$missing_fb_credentials = ! $fb_token || ! $fb_token_scopes || ! $fb_token_expires || $fb_token_expires <= time();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="facebook">
	<td colspan="2" class="<?php echo esc_attr( $missing_fb_credentials ? 'enter-credentials' : 'has-credentials' ); ?>">
	<?php if ( $missing_fb_credentials ) : ?>
		<div class="tribe-message tribe-credentials-prompt">
			<span class="dashicons dashicons-warning"></span>
			<?php wp_nonce_field( 'tribe-save-facebook-credentials' ); ?>
			<input id="tribe-has-facebook-credentials" type="hidden" value="0" />

			<div class="tribe-ea-facebook-login">
				<iframe id="facebook-login" src="<?php echo esc_url( Tribe__Events__Aggregator__Record__Facebook::get_iframe_url() ); ?>" width="80" height="30"></iframe>
				<div class="tribe-ea-status" data-error-message="<?php esc_attr_e( '@todo:error-fb-message', 'the-events-calendar' ); ?>"></div>
			</div>
		</div>
	<?php else: ?>
		<input id="tribe-has-facebook-credentials" type="hidden" value="1" />
	<?php endif; ?>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="facebook">
	<th scope="row" class="tribe-dependent" data-depends="#tribe-has-facebook-credentials" data-condition="1">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td class="tribe-dependent" data-depends="#tribe-has-facebook-credentials" data-condition="1">
		<?php if ( 'edit' === $aggregator_action ) : ?>
			<input type="hidden" name="aggregator[facebook][import_type]" id="tribe-ea-field-facebook_import_type" value="schedule" />
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<select
				name="aggregator[facebook][import_type]"
				id="tribe-ea-field-facebook_import_type"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
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
			name="aggregator[facebook][import_frequency]"
			id="tribe-ea-field-facebook_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition="schedule"
			data-prevent-clear
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( empty( $record->meta['frequency'] ) ? 'daily' : $record->meta['frequency'], $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
			<?php endforeach; ?>
		</select>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition-not="schedule"
			data-condition-empty
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition="schedule"
		></span>
	</td>
</tr>

<?php
$field              = (object) array();
$field->label       = __( 'URL:', 'the-events-calendar' );
$field->placeholder = __( 'facebook.com/example', 'the-events-calendar' );
$field->help        = __( 'Enter the url for a Facebook group, page, or individual. You can also enter the url of a single Facebook event.', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-facebook_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[facebook][source]"
			type="text"
			id="tribe-ea-field-facebook_source"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['source'] ) ? '' : $record->meta['source'] ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-facebook_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>
