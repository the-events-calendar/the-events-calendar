<?php
/**
 * Block: Event Category
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-category.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */
?>
<div class="tribe-events-single-section tribe-events-section-category tribe-clearfix">
	<?php
	echo tribe_get_event_categories(
		$this->get( 'post_id' ),
		[
			'before'       => '',
			'sep'          => ', ',
			'after'        => '',
			'label'        => null, // An appropriate plural/singular label will be provided
			'label_before' => '<dt class="tribe-events-event-categories-label">',
			'label_after'  => '</dt>',
			'wrap_before'  => '<dd class="tribe-events-event-categories">',
			'wrap_after'   => '</dd>',
		]
	);
	?>
</div>
