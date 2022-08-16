<?php

use TEC\Events\Custom_Tables\V1\Migration\CSV_Report\File_Download;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var Site_Report       $report             The report details.
 * @var String_Dictionary $text               Our text dictionary.
 * @var string            $phase              The current phase.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php
			include $template_directory . '/upgrade-logo.php';
			?>
			<?php echo esc_html( $text->get( 'migration-failure-complete' ) ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
					esc_html( $text->get( 'migration-failure-complete-paragraph' ) ),
					'<a href="https://evnt.is/2n" rel="noopener" target="_blank">',
					'</a>'
			);
			?>
		</p>

		<p class="tec-ct1-upgrade__alert tec-ct1-upgrade__alert--error">
			<i class="tec-ct1-upgrade__alert-icon">i</i>
			<?php
			echo sprintf(
					esc_html( $text->get( 'migration-failure-complete-alert' ) ),
					'<a class="tec-ct1-upgrade-start-migration-preview" href="#">',
					'</a>'
			);
			?>
		</p>
	</div>

	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( "$phase-screenshot-url" ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'preview-screenshot-alt' ) ); ?>"/>
	</div>
</div>

<div class="tec-ct1-upgrade__row">
	<div class="tec-ct1-upgrade__report">
		<header class="tec-ct1-upgrade__report-header">
			<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--timestamp">
				<?php echo $text->get( 'migration-failure-complete-date-heading' ); ?>
				<strong><?php echo esc_html( $report->date_completed ); ?></strong>
			</div>
			<div class="tec-ct1-action-container tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
				<em title="<?php esc_attr( $text->get( 're-run-preview-button' ) ) ?>">
					<?php include $template_directory . '/icons/rerun.php'; ?>
				</em>
				<a class="tec-ct1-upgrade-start-migration-preview"
				   href="#"><?php echo esc_html( $text->get( 're-run-preview-button' ) ); ?></a>
			</div>
		</header>
		<div class="tec-ct1-upgrade__report-body">
			<div class="tec-ct1-upgrade__report-body-content">
				<?php include( $template_directory . '/partials/failure-event-loop.php' ); ?>
			</div>
			<footer class="tec-ct1-upgrade__report-body-footer">
				<a
					href="<?php echo $text->get( 'learn-more-button-url' ); ?>"
					target="_blank"
					rel="noopener"
				><?php echo esc_html( $text->get( 'migration-prompt-learn-about-report-button' ) ); ?></a>
				|
				<a href="<?php echo File_Download::get_download_url() ?>"><?php echo $text->get( 'migration-download-report-button' ); ?></a>
			</footer>
		</div>
	</div>

</div>

<div class="tec-ct1-upgrade__row tec-ct1-action-container">
	<div class="content-container">
		<button class="tec-ct1-upgrade-start-migration-preview"
				type="button">
			<?php echo esc_html( $text->get( 'start-migration-preview-button' ) ); ?>
		</button>
	</div>
</div>
