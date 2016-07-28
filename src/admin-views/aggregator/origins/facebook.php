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

$fb_api_key = tribe_get_option( 'fb_api_key' );
$fb_api_secret = tribe_get_option( 'fb_api_secret' );
$missing_fb_credentials = ! $fb_api_key || ! $fb_api_secret;

?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="facebook">
	<td colspan="2" class="<?php echo esc_attr( $missing_fb_credentials ? 'enter-credentials' : 'has-credentials' ); ?>">
		<?php
		if ( $missing_fb_credentials ) :
			?>
			<input type="hidden" name="has-credentials" id="tribe-has-facebook-credentials" value="0">
			<div class="tribe-message tribe-credentials-prompt">
				<span class="dashicons dashicons-warning"></span>
				<?php
				printf(
					esc_html__(
						'Enter your Facebook Application information to use Facebook import. You only need to do this once, it will be saved under %1$sEvents &gt; Settings%2$s',
						'the-events-calendar'
					),
					'<a href="' . esc_url( admin_url( 'edit.php?post_type=tribe_events&page=aggregator' ) ) . '">',
					'</a>'
				);
				?>
			</div>
			<div class="tribe-message tribe-credentials-success">
				<span class="dashicons dashicons-yes"></span>
				<?php
				printf(
					esc_html__(
						'Your Facebook Application information has been saved to %1$sEvents &gt; Settings%2$s',
						'the-events-calendar'
					),
					'<a href="' . esc_url( admin_url( 'edit.php?post_type=tribe_events&page=aggregator' ) ) . '">',
					'</a>'
				);
				?>
			</div>
			<div class="tribe-fieldset">
				<?php wp_nonce_field( 'tribe-save-credentials' ); ?>
				<label for="facebook_app_id"><?php esc_html_e( 'App ID:', 'the-events-calendar' ); ?></label>
				<input type="text" name="fb_api_key" id="facebook_api_key" value="<?php echo esc_attr( $fb_api_key ); ?>">
				<label for="facebook_app_secret"><?php esc_html_e( 'App Secret:', 'the-events-calendar' ); ?></label>
				<input type="text" name="fb_api_secret" id="facebook_api_secret" value="<?php echo esc_attr( $fb_api_secret ); ?>">
				<button type="button" class="button tribe-save"><?php esc_html_e( 'Save', 'the-events-calendar' ); ?></button>
			</div>
			<?php
		else:
			?>
			<input type="hidden" name="has-credentials" id="tribe-has-facebook-credentials" value="1">
			<?php
		endif;
		?>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="facebook">
	<th scope="row" class="tribe-dependent" data-depends="#tribe-has-facebook-credentials" data-condition="1">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td class="tribe-dependent" data-depends="#tribe-has-facebook-credentials" data-condition="1">
		<select
			name="aggregator[facebook][import_type]"
			id="tribe-ea-field-facebook_import_type"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			data-hide-search
		>
			<option value=""></option>
			<option value="manual">One-Time Import</option>
			<option value="schedule">Scheduled Import</option>
		</select>
		<select
			name="aggregator[facebook][import_frequency]"
			id="tribe-ea-field-facebook_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition="schedule"
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( 'daily', $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
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
$field->help        = __( 'Enter the url for a Facebook group, page, or individual. Or, enter the url of a single Facebook event.', 'the-events-calendar' );
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
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-facebook_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php
			esc_html_e( 'Preview', 'the-events-calendar' );
			?>
		</button>
	</td>
</tr>
