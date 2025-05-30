<?php
/**
 * Template for adding category colors in the "Add Category" form.
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

?>

<div class="tec-events-category-colors__wrap">
	<h2><?php esc_html_e( 'Category Colors', 'the-events-calendar' ); ?></h2>
	<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>
	<div class="tec-events-category-colors__container">
		<div class="tec-events-category-colors__grid">
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
				<input
					type="text"
					id="tec-events-category-colors-primary"
					name="tec_events_category-color[primary]"
					value="<?php echo esc_attr( $category_colors['primary'] ?? '' ); ?>"
					class="tec-events-category-colors__input wp-color-picker"
					placeholder="<?php esc_attr_e( 'Select color', 'the-events-calendar' ); ?>"
				>
			</div>
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-background"><?php esc_html_e( 'Background Color', 'the-events-calendar' ); ?></label>
				<input
					type="text"
					id="tec-events-category-colors-background"
					name="tec_events_category-color[secondary]"
					value="<?php echo esc_attr( $category_colors['secondary'] ?? '' ); ?>"
					class="tec-events-category-colors__input wp-color-picker"
					placeholder="<?php esc_attr_e( 'Select color', 'the-events-calendar' ); ?>"
				>
			</div>
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-text"><?php esc_html_e( 'Text Color', 'the-events-calendar' ); ?></label>
				<input
					type="text"
					id="tec-events-category-colors-text"
					name="tec_events_category-color[text]"
					value="<?php echo esc_attr( $category_colors['text'] ?? '' ); ?>"
					class="tec-events-category-colors__input wp-color-picker"
					placeholder="<?php esc_attr_e( 'Select color', 'the-events-calendar' ); ?>"
				>
			</div>
			<div class="tec-events-category-colors__field--preview">
				<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
				<div class="tec-events-category-colors__preview-box">
					<span class="tec-events-category-colors__preview-text" data-default-text="<?php esc_attr_e( 'Example', 'the-events-calendar' ); ?>"></span>
				</div>
				<p class="tec-events-category-colors__description">
					<?php esc_html_e( 'Select a primary color of your choice. You can further customize your color choices afterwards.', 'the-events-calendar' ); ?>
					<a href="#"><?php esc_html_e( 'Learn more about color selection and accessibility', 'the-events-calendar' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</div>

<div class="tec-events-category-colors__field">
	<label for="tec-events-category-colors-priority">
		<?php esc_html_e( 'Category Priority', 'the-events-calendar' ); ?>
	</label>
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
</div>

<div class="tec-events-category-colors__field">
	<label class="tec-events-category-colors__checkbox-label">
		<input
			type="checkbox"
			id="tec-events-category-colors-hide-legend"
			name="tec_events_category-color[hide_from_legend]"
			value="1"
			<?php checked( ! empty( $category_colors['hide_from_legend'] ) ); ?>
			class="tec-events-category-colors__hide-legend"
		>
		<?php esc_html_e( 'Hide category from legend', 'the-events-calendar' ); ?>
	</label>
	<p class="tec-events-category-colors__description">
		<?php esc_html_e( 'Do not show this category if legend shows on event listing views.', 'the-events-calendar' ); ?>
	</p>
</div>
