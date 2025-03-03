<?php
/**
 * Template for editing category colors in the "Edit Category" form.
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

?>

<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>

<tr class="form-field tec_category_colors_wrap">
	<th scope="row">
		<label for="tec-events-category-primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<input
			type="text"
			id="tec-events-category-primary"
			name="tec_events_category-color[primary]"
			value="<?php echo esc_attr( $category_colors['primary'] ?? '' ); ?>"
			class="tec-category-colors__input wp-color-picker"
		>
	</td>
</tr>

<tr class="form-field tec_category_colors_wrap">
	<th scope="row">
		<label for="tec-events-category-secondary"><?php esc_html_e( 'Background Color', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<input
			type="text"
			id="tec-events-category-secondary"
			name="tec_events_category-color[secondary]"
			value="<?php echo esc_attr( $category_colors['secondary'] ?? '' ); ?>"
			class="tec-category-colors__input wp-color-picker"
		>
	</td>
</tr>

<tr class="form-field tec_category_colors_wrap">
	<th scope="row">
		<label for="tec-events-category-text"><?php esc_html_e( 'Font Color', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<input
			type="text"
			id="tec-events-category-text"
			name="tec_events_category-color[text]"
			value="<?php echo esc_attr( $category_colors['text'] ?? '' ); ?>"
			class="tec-category-colors__input wp-color-picker"
		>
	</td>
</tr>

<tr class="form-field tec_category_colors_wrap">
	<th scope="row">
		<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<div class="tec-category-colors__preview">
			<span class="tec-category-colors__preview-text" data-default-text="Example"></span>
		</div>
		<p>
			<?php esc_html_e( 'Select a primary color of your choice and a recommended background and font color will be generated. You can further customize your color choices afterwards.', 'the-events-calendar' ); ?>
			<a href="#"><?php esc_html_e( 'Learn more about color selection and accessibility', 'the-events-calendar' ); ?></a>
		</p>
	</td>
</tr>

<tr class="form-field tec_category_colors_wrap">
	<th scope="row">
		<label for="tec-events-category-priority"><?php esc_html_e( 'Category Priority', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<input
			type="number"
			id="tec-events-category-priority"
			name="tec_events_category-color[priority]"
			value="<?php echo esc_attr( $category_colors['priority'] ?? '' ); ?>"
			min="0"
		>
		<p><?php esc_html_e( 'This is used to determine which category color is assigned to an event if the event has more than one category.', 'the-events-calendar' ); ?></p>
	</td>
</tr>
