<dl class="tribe-aggregator-import-details">
	<dt><?php esc_html_e( 'Origin:', 'the-events-calendar' ); ?></dt>
	<dd><span class="tribe-value"><?php echo esc_html( $origin ); ?></span></dd>
	<?php if ( ! empty( $source ) ) : ?>
		<dt><?php esc_html_e( 'Source:', 'the-events-calendar' ); ?></dt>
		<dd><span class="tribe-value"><?php echo esc_html( $source ); ?></span></dd>
	<?php endif; ?>
	<?php if ( ! empty( $last_import ) ) : ?>
		<dt><?php esc_html_e( 'Last Import:', 'the-events-calendar' ); ?></dt>
		<dd>
			<span class="tribe-value"><?php echo esc_html( $last_import ); ?></span>
			<span
				class="dashicons dashicons-editor-help tribe-sticky-tooltip"
				title="<?php esc_attr_e( 'The last time this event was imported and/or updated via import.', 'the-events-calendar' ); ?>"
			></span>
		</dd>
	<?php endif; ?>
</dl>
<p>
	<?php
	switch ( $import_setting ) {
		case 'overwrite':
			$message = __(
				'If this event is re-imported, event fields will be overwritten with any changes from the source.',
				'the-events-calendar'
			);
			break;
		case 'preserve_changes':
			$message = __(
				'If this event is re-imported, event fields that have not been changed locally will be overwritten with any changes from the source.',
				'the-events-calendar'
			);
			break;
		case 'retain':
		default:
			$message = __(
				'This event will not be re-imported and changes made locally will be preserved.',
				'the-events-calendar'
			);
			break;
	}

	echo esc_html( $message );
	?>
	<a href="<?php echo esc_url( $settings_link ); ?>"><?php echo esc_html__( 'Change Event Update Authority', 'the-events-calendar' ); ?></a>
</p>
