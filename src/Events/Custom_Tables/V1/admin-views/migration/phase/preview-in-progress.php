<?php

use \TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
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

		</div>
	</div>
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( 'completed-screenshot-url' ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'updated-views-screenshot-alt' ) ); ?>"/>
	</div>
</div>
