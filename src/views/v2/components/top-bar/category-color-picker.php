<?php
/**
 * View: Top Bar - Category Color Picker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/category-color-picker.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.14.2
 *
 * @since 6.14.2 Added the category class to the list item to make customization easier.
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

if ( empty( $category_colors_enabled ) ) {
	return;
}

// Get the base URL for category links.
$base_url      = home_url( '/' );
$events_slug   = tribe_get_option( 'eventsSlug', 'events' );
$category_slug = Tribe__Events__Main::instance()->get_category_slug();
?>
<div class="tec-events-category-color-filter"
	role="button"
	tabindex="0"
	aria-haspopup="listbox"
	aria-expanded="false"
	aria-label="<?php esc_attr_e( 'Select categories to highlight', 'the-events-calendar' ); ?>">

	<div class="tec-events-category-color-filter__colors" id="tec-category-color-legend"></div>

	<span class="tec-events-category-color-filter__dropdown-icon">
		<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tec-events-category-color-filter__dropdown-icon-svg' ] ] ); ?>
	</span>
	<div class="tec-events-category-color-filter__dropdown" role="listbox" aria-label="<?php esc_attr_e( 'Category selection', 'the-events-calendar' ); ?>">
		<div class="tec-events-category-color-filter__dropdown-header">
			<span><?php echo $category_colors_super_power ? esc_html__( 'Highlight a category', 'the-events-calendar' ) : esc_html__( 'Browse by category', 'the-events-calendar' ); ?></span>
			<button class="tec-events-category-color-filter__dropdown-close" aria-label="<?php esc_attr_e( 'Close category selection', 'the-events-calendar' ); ?>">✕</button>
		</div>
		<ul class="tec-events-category-color-filter__dropdown-list"
			role="listbox"
			aria-label="<?php esc_attr_e( 'Category selection', 'the-events-calendar' ); ?>"
		>
			<?php foreach ( $category_colors_category_dropdown as $category ) : ?>
				<?php $category_class = Tribe__Events__Main::TAXONOMY . '-' . $category['slug']; ?>
				<li
					<?php
					tec_classes(
						[
							$category_class,
							'tec-events-category-color-filter__dropdown-item',
						]
					);
					?>
					role="option">
					<label data-category="<?php echo esc_attr( $category['slug'] ); ?>" >
						<?php if ( $category_colors_super_power ) : ?>
							<input type="checkbox"
								class="tec-events-category-color-filter__checkbox"
								aria-label="
								<?php
								echo /* translators: %s is the category name. */
								esc_attr( sprintf( __( 'Highlight events in %s', 'the-events-calendar' ), $category['name'] ) );
								?>
">
							<span class="tec-events-category-color-filter__label" aria-hidden="true"><?php echo esc_html( $category['name'] ); ?></span>
						<?php else : ?>
							<?php
							// Build the category URL dynamically and escape it.
							$category_url = esc_url( $base_url . $events_slug . '/' . $category_slug . '/' . $category['slug'] . '/' );
							?>
							<a href="<?php echo esc_url( $category_url ); ?>"
								<?php
								tec_classes(
									[
										'tec-events-category-color-filter__label',
										'tec-events-category-color-filter__color-circle',
										$category_class,
									]
								);
								?>
								aria-label="<?php /* translators: %s is the category name. */ echo esc_attr( sprintf( __( 'View events in %s', 'the-events-calendar' ), $category['name'] ) ); ?>">
								<?php echo esc_html( $category['name'] ); ?>
							</a>
						<?php endif; ?>
						<span
							<?php
							tec_classes(
								[
									'tec-events-category-color-filter__color-dot',
									$category_class,
								]
							);
							?>
							aria-hidden="true"
						></span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $category_colors_super_power && $category_colors_show_reset_button ) : ?>
			<div class="tec-events-category-color-filter__reset-wrapper">
				<button type="button" class="tec-events-category-color-filter__reset tribe-common-c-btn-border-small"
					aria-label="<?php esc_attr_e( 'Reset category selection', 'the-events-calendar' ); ?>">
					<?php esc_html_e( 'Reset', 'the-events-calendar' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
