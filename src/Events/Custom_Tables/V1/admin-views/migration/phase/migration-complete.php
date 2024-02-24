<?php

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 * @var State             $state              The migration state.
 * @var string            $phase              The current phase.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( "$phase-screenshot-url" ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'preview-screenshot-alt' ) ); ?>"/>
	</div>

	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php echo esc_html( $text->get( 'migration-complete' ) ); ?>
		</h3>
		<p>
			<?php
			echo sprintf(
					esc_html( $text->get( 'migration-complete-paragraph' ) ),
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
	$datetime_heading = $text->get( 'migration-date-heading' );
	$total_heading    = $text->get( 'migration-total-heading' );
	$heading_action   = '';
	if ( $state->should_allow_reverse_migration() ) {
		ob_start();
		?>
		<a href="#"
		   class="tec-ct1-upgrade-revert-migration tec-ct1-upgrade__link-danger"><?php echo esc_html( $text->get( 'reverse-migration-button' ) ); ?></a>
		<?php
		$heading_action = ob_get_clean();
	}
	include __DIR__ . '/report.php';
	?>
</div>
