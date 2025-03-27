<?php
/**
 * View: List Single Event Categories
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/category.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var object|null $category_colors_priority_category The highest-priority category for the event, determined using the
 *                                                     `Category_Color_Priority_Category_Provider` class.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

if ( empty( $category_colors_priority_category ) ) {
	return;
}

$category = $category_colors_priority_category;
?>

<div class="tec-events-calendar-list__event-categories">
	<div class="tec-events-calendar-list__category tribe-events-calendar__category--<?php echo sanitize_html_class( $category->slug ); ?>">
		<span class="tec-events-calendar-list__category-icon"></span>
		<?php echo esc_html( $category->name ); ?>
	</div>
</div>
