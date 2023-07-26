<?php
/**
 * Block: Event Tags
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-tags.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7.4
 *
 */

$event_id = $this->get( 'post_id' );
?>
<div class="tribe-events-single-section tribe-events-section-tags tribe-clearfix">
	<?php echo tribe_meta_event_archive_tags( esc_html__( 'Tags' ), ', ', false ); ?>
</div>
