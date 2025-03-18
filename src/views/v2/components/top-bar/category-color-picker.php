<?php
/**
 * View: Top Bar - Category Color Picker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/category-color-picker.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var string  $category_colors_enabled           Whether the category colors view should display.
 * @var array[] $category_colors_category_dropdown Array of categories with metadata.
 *                                                 Each category contains:
 *                                                 - 'slug' (string)      Category slug.
 *                                                 - 'name' (string)      Category name.
 *                                                 - 'priority' (int)     Priority for sorting.
 *                                                 - 'primary' (string)   Primary color in hex format.
 *                                                 - 'hidden' (bool)      Whether the category is hidden.
 *
 * @var bool    $category_colors_super_power       Whether the super power mode is enabled.
 * @var bool    $category_colors_show_reset_button Whether to show the reset button.
 */

if ( ! $category_colors_enabled ) {
	return;
}

?>
<div class="tec-category-color-picker"
	role="button"
	tabindex="0"
	aria-haspopup="listbox"
	aria-expanded="false"
	aria-label="<?php esc_attr_e( 'Select categories to highlight', 'the-events-calendar' ); ?>">

	<div class="tec-category-color-picker__colors">
		<?php foreach ( array_slice( $category_colors_category_dropdown, 0, 5 ) as $category ) : ?>
			<span class="tec-category-color-picker__color-circle"
				style="background-color: <?php echo esc_attr( $category['primary'] ); ?>;">
			</span>
		<?php endforeach; ?>
	</div>

	<span class="tec-category-color-picker__dropdown-icon">
		<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tec-category-color-picker__dropdown-icon-svg' ] ] ); ?>
	</span>
	<div class="tec-category-color-picker__dropdown" role="listbox" aria-label="<?php esc_attr_e( 'Category selection', 'the-events-calendar' ); ?>">
		<div class="tec-category-color-picker__dropdown-header">
			<span><?php esc_html_e( 'Highlight a category', 'the-events-calendar' ); ?></span>
			<button class="tec-category-color-picker__dropdown-close" aria-label="<?php esc_attr_e( 'Close category selection', 'the-events-calendar' ); ?>">✕</button>
		</div>
		<ul class="tec-category-color-picker__dropdown-list">
			<?php foreach ( $category_colors_category_dropdown as $category ) : ?>
				<li class="tec-category-color-picker__dropdown-item" role="option">
					<label>
						<?php if ( $category_colors_super_power ) { ?>
							<input type="checkbox"
								class="tec-category-color-picker__checkbox"
								data-category="<?php echo esc_attr( $category['slug'] ); ?>"
								aria-label="
								<?php
								echo /* translators: %s is the category name. */
								esc_attr( sprintf( __( 'Highlight events in %s', 'the-events-calendar' ), $category['name'] ) );
								?>
">
						<?php } ?>
						<span class="tec-category-color-picker__label"><?php echo esc_html( $category['name'] ); ?></span>
						<span class="tec-category-color-picker__color-dot" style="background-color: <?php echo esc_attr( $category['primary'] ); ?>;"></span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $category_colors_super_power && $category_colors_show_reset_button ) : ?>
			<div class="tec-category-color-picker__reset-wrapper">
				<button type="button" class="tec-category-color-picker__reset tribe-common-c-btn-border-small"
					aria-label="<?php esc_attr_e( 'Reset category selection', 'the-events-calendar' ); ?>">
					<?php esc_html_e( 'Reset', 'the-events-calendar' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
