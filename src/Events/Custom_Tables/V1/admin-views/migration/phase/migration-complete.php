<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports;
use TEC\Events\Custom_Tables\V1\Migration\Strings;

$strings = tribe( Strings::class );

/**
 * @var string $template_directory The absolute path to the Migration template root directory.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $strings->get( 'migration-completed-screenshot-url' ) ); ?>"
			 alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>"
		/>
	</div>

	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php esc_html_e( 'Migration complete!', 'the-events-calendar' ); ?>
		</h3>

		<p>
			<?php echo esc_html( $strings->get( 'migration-completed-site-upgraded' ) ); ?>
		</p>

		<p>
			<?php
			echo sprintf(
				esc_html__( 'Go ahead and %1$scheck out your events%2$s, %3$sview your calendar%2$s, or %4$sread more about the new features of Events Calendar PRO 6.0%2$s.', 'the-events-calendar' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . Tribe__Events__Main::POSTTYPE ) ) . '">',
				'</a>',
				'<a href="' . esc_url( tribe_events_get_url() ) . '">',
				'<a href="https://evnt.is/recurrence-2-0" target="_blank" rel="noopener">'
			);
			?>
		</p>
	</div>
</div>

<div class="tec-ct1-upgrade__row">
	<?php
	$datetime_heading = __( 'Migration Date/Time:', 'the-events-calendar' );
	$total_heading    = __( 'Total Events Migrated:', 'the-events-calendar' );
	ob_start();
	?>
	<a href="" class="tec-ct1-upgrade__link-danger"><?php esc_html_e( 'Reverse Migration', 'the-events-calendar' ); ?></a>
	<?php
	$heading_action = ob_get_clean(); 
	include __DIR__ . '/report.php';
	?>
</div>
