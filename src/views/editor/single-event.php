<?php
/**
 * Single Event Template
 *
 * A single event complete template, divided in smaller template parts.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/editor/single-event.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event_id = $this->get( 'post_id' );
?>

<div id="tribe-events-content" class="tribe-events-single tribe-blocks-editor">
	<?php $this->template( 'editor/parts/back-link' ); ?>
	<?php $this->template( 'editor/parts/notices' ); ?>
	<?php $this->template( 'editor/parts/title' ); ?>
	<?php $this->template( 'editor/parts/content' ); ?>
	<?php $this->template( 'editor/parts/comments' ); ?>
	<?php $this->template( 'editor/parts/footer' ); ?>
</div>