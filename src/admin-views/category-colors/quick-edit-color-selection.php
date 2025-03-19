<?php
/**
 * Template for Quick Edit category colors.
 *
 * @var array  $category_colors An associative array of meta keys and their values.
 * @var object $taxonomy        The taxonomy object.
 */

?>

<fieldset class="inline-edit-col-right tec-category-colors__quick-edit">
	<div class="inline-edit-col tec_category_colors__wrap">
		<?php wp_nonce_field( 'save_category_colors', 'tec_category_colors_nonce' ); ?>

		<h4><?php esc_html_e( 'Category Colors', 'the-events-calendar' ); ?></h4>

		<div class="tec-events-category-colors__container">
			<div class="tec-events-category-colors__grid">
				<!-- Primary Color -->
				<div class="tec-events-category-colors__group">
					<label for="tec-events-category-colors-quick-edit__primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
					<input
						type="text"
						id="tec-events-category-colors-quick-edit__primary"
						name="tec_events_category-color[primary]"
						value=""
						class="tec-events-category-colors__input wp-color-picker"
					>
				</div>

				<!-- Background Color -->
				<div class="tec-events-category-colors__group">
					<label for="tec-events-category-colors-quick-edit__background"><?php esc_html_e( 'Background Color', 'the-events-calendar' ); ?></label>
					<input
						type="text"
						id="tec-events-category-colors-quick-edit__background"
						name="tec_events_category-color[secondary]"
						value=""
						class="tec-events-category-colors__input wp-color-picker"
					>
				</div>

				<!-- Font Color -->
				<div class="tec-events-category-colors__group">
					<label for="tec-events-category-colors-quick-edit__text"><?php esc_html_e( 'Font Color', 'the-events-calendar' ); ?></label>
					<input
						type="text"
						id="tec-events-category-colors-quick-edit__text"
						name="tec_events_category-color[text]"
						value=""
						class="tec-events-category-colors__input wp-color-picker"
					>
				</div>

				<!-- Preview -->
				<div class="tec-events-category-colors__preview">
					<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
					<div class="tec-events-category-colors__preview-box">
						<span class="tec-events-category-colors__preview-box-text" data-default-text="<?php esc_attr_e( 'Example', 'the-events-calendar' ); ?>"></span>
					</div>
					<p>
						<?php esc_html_e( 'Select a primary color of your choice and a recommended background and font color will be generated. You can further customize your color choices afterwards.', 'the-events-calendar' ); ?>
						<a href="#"><?php esc_html_e( 'Learn more about color selection and accessibility', 'the-events-calendar' ); ?></a>
					</p>
				</div>
			</div>
		</div>

		<!-- Category Priority -->
		<div class="tec-events-category-colors__priority">
			<label for="tec-events-category-colors-quick-edit__priority" class="tec-category-colors__label">
				<?php esc_html_e( 'Category Priority', 'the-events-calendar' ); ?>
			</label>
			<input
				type="number"
				id="tec-events-category-colors-quick-edit__priority"
				name="tec_events_category-color[priority]"
				value=""
				min="0"
				class="tec-category-colors__input"
			>
			<p class="tec-category-colors__description">
				<?php esc_html_e( 'This is used to determine which category color is assigned to an event if the event has more than one category.', 'the-events-calendar' ); ?>
			</p>
		</div>


		<!-- Category Legend -->
		<div class="tec-events-category-colors__legend">
			<label for="tec-events-category-colors-quick-edit__hide-legend"><?php esc_html_e( 'Category legend', 'the-events-calendar' ); ?></label>
			<label class="tec-category-colors__checkbox-label">
				<input type="checkbox" id="tec-events-category-colors-quick-edit__hide-legend" name="tec_events_category-color[hide_from_legend]">
				<?php esc_html_e( 'Hide category from legend', 'the-events-calendar' ); ?>
			</label>
			<p><?php esc_html_e( 'Do not show this category if legend shows on event listing views.', 'the-events-calendar' ); ?></p>
		</div>

	</div>
</fieldset>
