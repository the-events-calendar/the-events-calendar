<?php
/**
 * Template for adding category colors in the "Add New Category" form.
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

?>

<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>

<div class="tec_category_colors__wrap">
	<h2>Category Colors</h2>
		<div class="tec-events-category-colors__container">
<div class="tec-events-category-colors__grid">
		<div class="form-field">
			<label for="tec_events_category-color-primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec_events_category-color-primary"
				name="tec_events_category-color[primary]"
				value="<?php echo esc_attr( $category_colors['primary'] ); ?>"
				class="tec-admin-color-picker"
			>
		</div>
		<div class="form-field">
			<label for="tec_events_category-color-secondary"><?php esc_html_e( 'Secondary Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec_events_category-color-secondary"
				name="tec_events_category-color[secondary]"
				value="<?php echo esc_attr( $category_colors['secondary'] ); ?>"
				class="tec-admin-color-picker"
			>
		</div>
		<div class="form-field">
			<label for="tec_events_category-color-text"><?php esc_html_e( 'Text Color', 'the-events-calendar' ); ?></label>
			<input
				type="text"
				id="tec_events_category-color-text"
				name="tec_events_category-color[text]"
				value="<?php echo esc_attr( $category_colors['text'] ); ?>"
				class="tec-admin-color-picker"
			>
		</div>
		<div class="form-field  tec-events-category-colors__preview">
			<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
			<div class="tec-events-category-colors__preview-box">
				<span class="tec-events-category-colors__preview-box-text" data-default-text="Example"></span>
			</div>
			<p>Select a primary color of your choice and a recommended background and font color will be generated. You can further customize your color choices afterwards. <a href="#">Learn more about color selection and accessibility</a>.</p>
		</div>
</div></div>
	<div class="form-field tec-events-category-colors__priority">
		<label for="tec_events_category-color-priority">
			<?php esc_html_e( 'Priority', 'the-events-calendar' ); ?>
		</label>
		<input
			type="number"
			id="tec_events_category-color-priority"
			name="tec_events_category-color[priority]"
			value="<?php echo esc_attr( $category_colors['priority'] ); ?>"
			min="0"
		>
		<p class="tec-category-colors__description">
			<?php esc_html_e( 'This is used to determine which category color is assigned to an event if the event has more than one category.', 'the-events-calendar' ); ?>
		</p>
	</div>

	<div class="form-field tec-events-category-colors__legend">
		<label class="tec-category-colors__checkbox-label">
			<input
				type="checkbox"
				id="tec-events-category-colors__hide-legend"
				name="tec_events_category-color[hide_from_legend]"
				value="1"
				<?php checked( ! empty( $category_colors['hide_from_legend'] ) ); ?>
			>
			<?php esc_html_e( 'Hide category from legend', 'the-events-calendar' ); ?>
		</label>
		<p class="tec-category-colors__description">
			<?php esc_html_e( 'Do not show this category if legend shows on event listing views.', 'the-events-calendar' ); ?>
		</p>
	</div>


</div>
