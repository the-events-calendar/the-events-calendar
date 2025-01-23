<fieldset>
<?php
foreach ( $selected_categories as $category ) {
	// Retrieve existing values or set defaults.
	$colors = $form_data['blueprint'][ $category->slug ] ?? [
		'foreground' => '',
		'background' => '',
		'text-color' => '',
	];
	?>
	<fieldset class="tec-category-colors">
		<legend><?php echo esc_html( $category->name ); ?></legend>

		<label for="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][foreground]">
			Foreground:
			<input
				type="text"
				id="<?php echo esc_attr( "{$category->slug}_foreground" ); ?>"
				name="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][foreground]"
				value="<?php echo esc_attr( $colors['foreground'] ); ?>"
				placeholder="#FFFFFF"
			/>
		</label>

		<label for="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][background]">
			Background:
			<input
				type="text"
				id="<?php echo esc_attr( "{$category->slug}_background" ); ?>"
				name="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][background]"
				value="<?php echo esc_attr( $colors['background'] ); ?>"
				placeholder="#000000"
			/>
		</label>

		<label for="tec_category_colors_blueprint[<?php echo esc_attr( $category->slug ); ?>][text-color]">
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
