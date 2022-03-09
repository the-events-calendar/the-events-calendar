<?php

/**
 * @var string $template_directory The absolute path to the Migration template root directory.
 * @var \TEC\Events\Custom_Tables\V1\Migration\Strings $text The text dictionary.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<span>
			<?php echo esc_html( $text->get( 'preview-prompt-get-ready' ) ); ?>
		</span>

		<h3>
			<?php include $template_directory . '/upgrade-logo.php';; ?>
			<?php echo esc_html( $text->get( 'preview-prompt-upgrade-cta' ) ); ?>
		</h3>

		<p>
			<?php echo esc_html( $text->get( 'preview-prompt-features' ) ); ?>
		</p>

		<p>
			<strong>
				<?php echo esc_html( $text->get( 'preview-prompt-ready' ) ); ?>
			</strong>
			<?php echo esc_html( $text->get( 'preview-prompt-scan-events' ) ); ?>
		</p>

		<button class="tec-ct1-upgrade-start-migration-preview"
				type="button">
			<?php echo esc_html( $text->get( 'start-migration-preview-button' ) ); ?>
		</button>
		<a href="http://evnt.is/recurrence-2-0" target="_blank" rel="noopener">
			<?php echo esc_html( $text->get( 'learn-more-button' ) ); ?>
		</a>
	</div>
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>"
			 alt="<?php esc_attr( $text->get( 'updated-views-screenshot-alt' ) ); ?>"/>
	</div>
</div>
