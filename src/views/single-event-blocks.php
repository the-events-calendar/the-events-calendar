<?php
/**
 * Single Event Template
 *
 * A single event complete template, divided in smaller template parts.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event-blocks.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7
 *
 */

$event_id = $this->get( 'post_id' );
?>

<div id="tribe-events-content" class="tribe-events-single tribe-blocks-editor">
	<?php $this->template( 'single-event/back-link' ); ?>
	<?php $this->template( 'single-event/notices' ); ?>
	<?php $this->template( 'single-event/title' ); ?>
	<?php $this->template( 'single-event/content' ); ?>
	<?php $this->template( 'single-event/comments' ); ?>
	<?php $this->template( 'single-event/footer' ); ?>
</div>