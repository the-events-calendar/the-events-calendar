<?php
/**
 * @var string $template_directory The absolute path to the Migration template root directory.
 * @var \TEC\Events\Custom_Tables\V1\Migration\Strings $text The text dictionary.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php
			include $template_directory . '/upgrade-logo.php';
			esc_html( $text->get( 'migration-preview-in-progress' ) );
			?>
		</h3>

		<p><?php esc_html( $text->get( 'migration-preview-scanning-events' ) ); ?></p>
		<div class="tec-ct1-upgrade-update-bar-container">

		</div>
	</div>
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>"
			 alt="<?php esc_attr( $text->get( 'updated-views-screenshot-alt' ) ); ?>"/>
	</div>
</div>
