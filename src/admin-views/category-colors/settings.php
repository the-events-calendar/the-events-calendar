<div class="tec-settings-form__header-block">

	<h2 class="tec-settings-form__section-header">Category Colors</h2>
	<p class="tec-settings-form__category-colors-description">
		The settings below allow you to enable or disable front-end Event filters.
		Uncheck the box to hide the filter.
	</p>
	<div class="tec-settings-form__category-colors-checkboxes">
		<?php
		foreach ( $categories as $category ) {
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
			<?php
		}
		?>
	</div>
</div>
<fieldset>
	<label>Selected Categories</label>

	<?php
	foreach ( $categories as $category ) {
		// Retrieve existing values or set defaults.
		$colors = $form_data['blueprint'][ $category->slug ] ?? [
			'foreground' => '',
			'background' => '',
			'text-color' => '',
		];
		?>
		<fieldset class="tec-category-colors">
			<legend><?php echo esc_html( $category->name ); ?></legend>

			<label for="<?php echo esc_attr( "{$category->slug}_foreground" ); ?>">
				Foreground:
				<input
					type="text"
					id="<?php echo esc_attr( "{$category->slug}_foreground" ); ?>"
					name="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][foreground]"
					value="<?php echo esc_attr( $colors['foreground'] ); ?>"
					placeholder="#FFFFFF"
				/>
			</label>

			<label for="<?php echo esc_attr( "{$category->slug}_background" ); ?>">
				Background:
				<input
					type="text"
					id="<?php echo esc_attr( "{$category->slug}_background" ); ?>"
					name="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][background]"
					value="<?php echo esc_attr( $colors['background'] ); ?>"
					placeholder="#000000"
				/>
			</label>

			<label for="<?php echo esc_attr( "{$category->slug}_text_color" ); ?>">
				Text Color:
				<input
					type="text"
					id="<?php echo esc_attr( "{$category->slug}_text_color" ); ?>"
					name="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][text-color]"
					value="<?php echo esc_attr( $colors['text-color'] ); ?>"
					placeholder="#EFEFEF"
				/>
			</label>
		</fieldset>
		<hr/>
		<?php
	}
	?>

</fieldset>
