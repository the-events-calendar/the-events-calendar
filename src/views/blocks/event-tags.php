<?php
/**
 * Block: Event Tags
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-tags.php
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
<div class="tribe-events-single-section tribe-events-section-tags tribe-clearfix">
	<?php echo tribe_meta_event_tags( sprintf( esc_html__( '%s Tags:', 'the-events-calendar' ), tribe_get_event_label_singular() ), ', ', false ) ?>
</div>
