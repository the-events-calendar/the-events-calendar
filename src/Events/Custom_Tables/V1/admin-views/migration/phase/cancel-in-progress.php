<?php

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 * @var string            $phase              The current phase.
 */
$is_cancel = $phase === State::PHASE_CANCEL_IN_PROGRESS;
// Which copy do we show?
$title_key     = $is_cancel ? 'cancel-migration-in-progress' : 'reverse-migration-in-progress';
$paragraph_key = $is_cancel ? 'cancel-migration-in-progress-paragraph' : 'reverse-migration-in-progress-paragraph';
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php echo esc_html( $text->get( $title_key ) ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
					esc_html( $text->get( $paragraph_key ) ),
					'<strong>',
					'</strong>'
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
