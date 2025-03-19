<?php
/**
 * Template for displaying a category color preview in the admin table.
 *
 * @since TBD
 *
 * @var string $primary   The primary color hex value.
 * @var string $secondary The secondary color hex value.
 * @var string $text      The text color hex value.
 * @var string $priority  The priority of the category.
 * @var string $hide_from_legend    Whether the category is hidden.
 */

?>
<span class="tec-events-taxonomy-table__category-color-preview"
	style="background-color: <?php echo esc_attr( $secondary ); ?>; border: 3px solid <?php echo esc_attr( $primary ); ?>;"
	data-primary="<?php echo esc_attr( $primary ); ?>"
	data-secondary="<?php echo esc_attr( $secondary ); ?>"
	data-text="<?php echo esc_attr( $text ); ?>"
	data-priority="<?php echo esc_attr( $priority ); ?>"
	data-hidden="<?php echo esc_attr( $hide_from_legend ); ?>">
</span>
