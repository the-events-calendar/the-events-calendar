<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var Site_Report       $report             The report details.
 * @var String_Dictionary $text               Our text dictionary.
 * @var string            $phase              The current phase.
 */


$alert_classes = [
	'tec-ct1-upgrade__alert' => true,
	'tec-ct1-upgrade__alert--error' => (bool) $report->has_errors,
];
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php echo esc_html( $text->get( 'preview-complete' ) ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
				esc_html( $text->get( 'preview-complete-paragraph' ) ),
				'<a href="https://evnt.is/2n" rel="noopener" target="_blank">',
				'</a>'
			);
			?>
		</p>

		<p <?php tribe_classes( $alert_classes ); ?>>
			<i class="tec-ct1-upgrade__alert-icon">i</i>
			<span>
			<?php
			if ( $report->has_errors ) {
				echo esc_html( $text->get( 'migration-is-blocked' ) );
			} else {
				echo sprintf(
					esc_html( $text->get( 'preview-estimate' ) ),
					'<strong>',
					'</strong>',
					$report->estimated_time_in_minutes
				);
				if ( $addendum = $text->get( 'migration-prompt-plugin-state-addendum' ) ) {
					?>
					<strong><?php echo esc_html( $addendum ); ?></strong>
					<?php
				}
				?>
				<a href="<?php echo esc_url( $text->get( 'learn-more-button-url' ) ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( $text->get( 'learn-more-button' ) ); ?>.
				</a>
				<?php
			}
			?>
			</span>
		</p>
	</div>

	<div class="image-container">
		<img
			class="screenshot"
			src="<?php echo esc_url( $text->get( "$phase-screenshot-url" ) ); ?>"
			alt="<?php echo esc_attr( $text->get( 'preview-screenshot-alt' ) ); ?>"
		/>
	</div>
</div>

<div class="tec-ct1-upgrade__row">
	<?php
	$datetime_heading = $text->get( 'previewed-date-heading' );
	$total_heading    = $text->get( 'previewed-total-heading' );
	ob_start();
	?>
	<em title="<?php esc_attr( $text->get( 're-run-preview-button' ) ) ?>">
		<?php include $template_directory . '/icons/rerun.php'; ?>
	</em>
	<a
		class="tec-ct1-upgrade-start-migration-preview"
		href="#"><?php echo esc_html( $text->get( 're-run-preview-button' ) ); ?></a>
	<?php
	$heading_action = ob_get_clean();
	include_once __DIR__ . '/report.php';
	?>
</div>

<div class="tec-ct1-upgrade__row tec-ct1-action-container">
	<div class="content-container">
		<button
			class="tec-ct1-upgrade-start-migration"
			<?php if ( $report->has_errors ) : ?>
				disabled="disabled"
			<?php endif; ?>
			type="button"
		><?php echo esc_html( $text->get( 'start-migration-button' ) ); ?></button>
		<i>
			<?php
			if ( $report->estimated_time_in_minutes === 1 ) {
				$message = esc_html( $text->get( 'estimated-time-singular' ) );
			} else {
				$message = esc_html( $text->get( 'estimated-time-plural' ) );
			}

			echo sprintf( $message, $report->estimated_time_in_minutes );
			?>
		</i>
	</div>
</div>
