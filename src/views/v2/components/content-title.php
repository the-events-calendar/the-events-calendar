<?php

use \Tribe\Events\Views\V2\Template;

/**
 * View Component: Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/content-title.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since   6.2.0
 *
 * @var Template $this          Template Engine instance rendering.
 * @var string   $content_title The title to display.
 */
if ( empty( $content_title ) ) {
	return;
}

?>

<div class="tribe-events-header__content-title">
	<span class="tribe-events-header__content-title-text tribe-common-h7 tribe-common-h3--min-medium tribe-common-h--alt">
		<?php echo esc_html( $content_title ); ?>
	</span>
</div>
