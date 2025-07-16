<?php
/**
 * Template for displaying a category color preview in the admin table.
 *
 * @since 6.14.0
 *
 * @var string $primary          The primary color hex value (border).
 * @var string $secondary        The secondary color hex value (background).
 * @var string $text             The text color hex value.
 * @var string $priority         The priority of the category.
 * @var string $hide_from_legend Whether the category is hidden.
 * @var string $category_class   The category class identifier (e.g. tribe_events_cat-category-1).
 */

?>
<span <?php tribe_classes( 'tec-events-taxonomy-table__category-color-preview', $category_class ); ?>
	data-primary="<?php echo esc_attr( $primary ); ?>"
	data-secondary="<?php echo esc_attr( $secondary ); ?>"
	data-text="<?php echo esc_attr( $text ); ?>"
	data-priority="<?php echo esc_attr( $priority ); ?>"
	data-hidden="<?php echo esc_attr( $hide_from_legend ); ?>">
</span>
