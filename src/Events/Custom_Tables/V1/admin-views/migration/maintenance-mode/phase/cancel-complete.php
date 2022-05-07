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
			<?php echo esc_html( $text->get( 'migration-canceled' ) ); ?>
		</h3>
		<p>
			<button type="button"
					onclick="window.location.reload(true)"><?php echo esc_html( $text->get( 'migration-okay-button' ) ); ?></button>
		</p>
	</div>
</div>

