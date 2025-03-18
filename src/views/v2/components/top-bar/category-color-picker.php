<?php

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;

$super_power_mode       = tribe_get_option( 'category-color-legend-superpowers', false );
$show_hidden_categories = tribe_get_option( 'category-color-show-hidden-categories', false );
$show_reset_button      = tribe_get_option( 'category-color-reset-button', false );

// Fetch all categories with colors.
$categories = get_terms(
	[
		'taxonomy'   => Tribe__Events__Main::TAXONOMY,
		'hide_empty' => false,
	]
);

// Retrieve category colors, priorities, and visibility.
$meta_instance   = tribe( Event_Category_Meta::class );
$category_colors = array_filter(
	array_map(
		fn( $category ) => [
			'slug'     => $category->slug,
			'name'     => $category->name,
			'priority' => is_numeric( $priority = $meta_instance->set_term( $category->term_id )->get( Meta_Keys::get_key( 'priority' ) ) ) ? (int) $priority : -1,
			'primary'  => $meta_instance->set_term( $category->term_id )->get( Meta_Keys::get_key( 'primary' ) ),
			'hidden'   => (bool) $meta_instance->set_term( $category->term_id )->get( Meta_Keys::get_key( 'hidden' ) ),
		],
		$categories
	),
	fn( $category ) => ! empty( $category['primary'] ) && ( $show_hidden_categories || ! $category['hidden'] )
);

// Sort by priority (highest first).
usort( $category_colors, fn( $a, $b ) => $b['priority'] <=> $a['priority'] );

?>
<div class="tec-category-color-picker"
	role="button"
	tabindex="0"
	aria-haspopup="listbox"
	aria-expanded="false"
	aria-label="<?php esc_attr_e( 'Select categories to highlight', 'the-events-calendar' ); ?>">

	<div class="tec-category-color-picker__colors">
		<?php foreach ( array_slice( $category_colors, 0, 5 ) as $category ) : ?>
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
			<?php foreach ( $category_colors as $category ) : ?>
				<li class="tec-category-color-picker__dropdown-item" role="option">
					<label>
						<?php if ( $super_power_mode ) { ?>
							<input type="checkbox"
								class="tec-category-color-picker__checkbox"
								data-category="<?php echo esc_attr( $category['slug'] ); ?>"
								aria-label="<?php echo /* translators: %s is the category name. */
								esc_attr( sprintf( __( 'Highlight events in %s', 'the-events-calendar' ), $category['name'] ) ); ?>">
						<?php } ?>
						<span class="tec-category-color-picker__label"><?php echo esc_html( $category['name'] ); ?></span>
						<span class="tec-category-color-picker__color-dot" style="background-color: <?php echo esc_attr( $category['primary'] ); ?>;"></span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $super_power_mode && $show_reset_button ) : ?>
			<div class="tec-category-color-picker__reset-wrapper">
				<button type="button" class="tec-category-color-picker__reset tribe-common-c-btn-border-small"
					aria-label="<?php esc_attr_e( 'Reset category selection', 'the-events-calendar' ); ?>">
					<?php esc_html_e( 'Reset', 'the-events-calendar' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
