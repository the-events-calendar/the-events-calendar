<?php
/**
 * @var string $template_directory The absolute path to the Migration template root directory.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php
			include $template_directory . '/upgrade-logo.php';
			esc_html_e( 'Migration preview in progress', 'the-events-calendar' );
			?>
		</h3>

		<p><?php esc_html_e( 'We\'re scanning your existing events so you’ll know what to expect from the migration process. You can keep using your site and managing events. Check back later for a full preview report and the next steps for migration.', 'the-events-calendar' ); ?></p>
		<div class="tec-ct1-upgrade-update-bar-container">

		</div>
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
