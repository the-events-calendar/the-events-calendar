<?php
/**
 * Template for editing category colors in the "Edit Category" form.
 *
 * @version 6.14.0
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>

<tr class="form-field tec-events-category-colors__wrap">
	<th scope="row"><?php esc_html_e( 'Category Colors', 'the-events-calendar' ); ?></th>
	<td class="form-wrap">
		<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>
		<div class="tec-events-category-colors__container">
			<div class="tec-events-category-colors__grid">
				<?php
				// Primary Color field.
				$this->template( '/partials/primary-color', [ 'value' => $category_colors['primary'] ?? '' ] );
				// Background Color field.
				$this->template( '/partials/background-color', [ 'value' => $category_colors['secondary'] ?? '' ] );
				// Text Color field.
				$this->template( '/partials/text-color', [ 'value' => $category_colors['text'] ?? '' ] );
				$this->template( '/partials/preview-area' );
				?>
			</div>
		</div>
	</td>
</tr>
<tr>
	<th scope="row">
		<label for="tec-events-category-colors-priority">
			<?php esc_html_e( 'Category Priority', 'the-events-calendar' ); ?>
		</label>
	</th>
	<td class="form-wrap">
		<input
			type="number"
			id="tec-events-category-colors-priority"
			name="tec_events_category-color[priority]"
			value="<?php echo esc_attr( $category_colors['priority'] ?? '' ); ?>"
			min="0"
			class="tec-events-category-colors__input"
		>
		<p class="tec-events-category-colors__description">
			<?php esc_html_e( 'This is used to determine which category color is assigned to an event if the event has more than one category.', 'the-events-calendar' ); ?>
		</p>
	</td>
</tr>

<tr>
	<th scope="row">
		<label for="tec-events-category-colors-hide-legend">
			<?php esc_html_e( 'Hide category from legend', 'the-events-calendar' ); ?>
		</label>
	</th>
	<td class="form-wrap">
		<label class="tec-events-category-colors__checkbox-label">
			<input
				type="checkbox"
				id="tec-events-category-colors-hide-legend"
				name="tec_events_category-color[hide_from_legend]"
				value="1"
				<?php checked( ! empty( $category_colors['hide_from_legend'] ) ); ?>
			>
			<?php esc_html_e( 'Hide category from legend', 'the-events-calendar' ); ?>
		</label>
		<p class="tec-events-category-colors__description">
			<?php esc_html_e( 'Do not show this category if legend shows on event listing views.', 'the-events-calendar' ); ?>
		</p>
	</td>
</tr>
