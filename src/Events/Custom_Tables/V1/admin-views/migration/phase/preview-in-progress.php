<?php

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 * @var string            $phase              The current phase.
 */

?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php
			include $template_directory . '/upgrade-logo.php';
			echo esc_html( $text->get( 'preview-in-progress' ) );
			?>
		</h3>

		<p><?php echo esc_html( $text->get( 'preview-scanning-events' ) ); ?></p>
		<div class="tec-ct1-upgrade-update-bar-container">
			<p><?php echo esc_html( $text->get( 'loading-message' ) ); ?></p>
		</div>
		<div>
			<a
				class="tec-ct1-upgrade-start-migration-preview"
				href="#"
			><?php echo esc_html( $text->get( 'retry-preview-button' ) ); ?></a>
		</div>
	</div>
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( "$phase-screenshot-url" ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'preview-screenshot-alt' ) ); ?>"/>
	</div>
</div>
