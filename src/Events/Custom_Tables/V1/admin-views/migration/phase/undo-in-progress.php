<?php

use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;

/**
 * @var $template_directory string The base migration template directory.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php esc_html_e( 'Reverse migration in progress', 'the-events-calendar' ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
					esc_html__( '
					We are reversing your site’s migration to the new system. During this time, %1$syou cannot create, edit, or manage your events%2$s. Your calendar will still be visible on your site but some frontend actions will be paused.', 'the-events-calendar' ),
					'<strong>',
					'</strong>'
			);
			?>
		</p>
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
