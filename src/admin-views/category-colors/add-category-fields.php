<?php
/**
 * Template for adding category colors in the "Add New Category" form.
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

?>

<div class="tec_category_colors_wrap">
	<h2>Category Colors</h2>
	<div class="">
		<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>

		<div class="form-field">
			<label for="tec-events-category-primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec-events-category-primary"
				name="tec_events_category-color[primary]"
				value="<?php echo esc_attr( $category_colors['primary'] ?? '' ); ?>"
				class="tec-category-colors__input wp-color-picker"
			>
		</div>
		<div class="form-field">
			<label for="tec-events-category-secondary"><?php esc_html_e( 'Background Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec-events-category-secondary"
				name="tec_events_category-color[secondary]"
				value="<?php echo esc_attr( $category_colors['secondary'] ?? '' ); ?>"
				class="tec-category-colors__input wp-color-picker"
			>
		</div>
		<div class="form-field">
			<label for="tec-events-category-text"><?php esc_html_e( 'Font Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec-events-category-text"
				name="tec_events_category-color[text]"
				value="<?php echo esc_attr( $category_colors['text'] ?? '' ); ?>"
				class="tec-category-colors__input wp-color-picker"
			>
		</div>
		<div class="form-field">
			<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
			<div class="tec-category-colors__preview">
				<span class="tec-category-colors__preview-text" data-default-text="Example"></span>
			</div>
			<p>Select a primary color of your choice and a recommended background and font color will be generated. You can further customize your color choices afterwards. <a href="#">Learn more about color selection and accessibility</a>.</p>
		</div>
		<div class="form-field">
			<label for="tec-events-category-priority"><?php esc_html_e( 'Category Priority', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec-events-category-priority"
				name="tec_events_category-color[priority]"
				value="<?php echo esc_attr( $category_colors['priority'] ?? '' ); ?>"
			>
			<p>This is used to determine which category color is assigned to an event if the event has more than one category.</p>
		</div>
	</div>
</div>
