<?php

use \Tribe\Events\Views\V2\Template;

/**
 * View Component: Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/header-title.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version TBD
 * @since   TBD
 *
 * @var Template $this         Template Engine instance rendering.
 * @var string   $header_title The title to display.
 */
if ( empty( $header_title ) ) {
	return;
}

?>

<div class="tribe-events-header__title">
	<h1 class="tribe-events-header__title-text">
		<?php echo esc_html( $header_title ); ?>
	</h1>
</div>
