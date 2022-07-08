<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var Site_Report       $report Our migration site report.
 * @var string            $phase  The current migration phase.
 * @var String_Dictionary $text   The text dictionary.
 */

$remaining_events       = $report->total_events_remaining;
$total_previewed_events = $report->total_events_migrated;
$progress               = $report->progress_percent;

$progress_bar_value = round( $progress ) / 100;
$percent            = "$progress%";
if ( $phase === State::PHASE_PREVIEW_IN_PROGRESS ) {
	$progress_text  = sprintf(
		$text->get( 'preview-progress-bar-events-done' ),
		'<strong>',
		$total_previewed_events,
		'</strong>'
	);
	$remaining_text = sprintf(
		$text->get( 'preview-progress-bar-events-remaining' ),
		'<strong>',
		$remaining_events,
		'</strong>'
	);
} else {
	$progress_text  = sprintf(
		$text->get( 'migration-progress-bar-events-done' ),
		'<strong>',
		$total_previewed_events,
		'</strong>'
	);
	$remaining_text = sprintf(
		$text->get( 'migration-progress-bar-events-remaining' ),
		'<strong>',
		$remaining_events,
		'</strong>'
	);
}
?>
<div class="tribe-update-bar">
	<progress
		role="progressbar"
		aria-describedby="loading-zone"
		tabindex="-1"
		<?php if ( $progress == 0 ) : ?>
			indeterminate
		<?php else: ?>
			value="<?php echo esc_attr( $progress_bar_value ); ?>"
			aria-valuenow="<?php echo esc_attr( $percent ); ?>"
		<?php endif; ?>
	>
		<?php if ( $progress == 0 ) : ?>
			<?php esc_html_e( 'unknown', 'the-events-calendar' ); ?>
		<?php else: ?>
			<?php echo esc_attr( $percent ); ?>
		<?php endif; ?>
	</progress>

	<div class="tribe-update-bar__summary">
		<div class="tribe-update-bar__summary-progress-text">
			<?php echo $progress_text; ?>
		</div>
		<div class="tribe-update-bar__summary-remaining-text">
			<?php echo $remaining_text; ?>
		</div>
	</div>
</div>
