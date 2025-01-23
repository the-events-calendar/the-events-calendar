<div class="tec-settings-form__category-colors-checkboxes">
	<?php foreach ( $categories as $category ) :
		$is_checked = ! empty( $form_data['categories'] ) && in_array( $category->slug, $form_data['categories'], true );
		?>
		<label class="tec-settings-form__category-colors-checkbox">
			<input
				type="checkbox"
				name="tec_category_color_categories[]"
				value="<?php echo esc_attr( $category->slug ); ?>"
				<?php checked( $is_checked ); ?>
			/>
			<?php echo esc_html( $category->name ); ?>
		</label>
	<?php endforeach; ?>
</div>
