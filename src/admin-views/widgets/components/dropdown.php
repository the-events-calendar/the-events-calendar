<?php
/**
 * Admin View: Widget Dropdown Component
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/dropdown.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version 5.3.0
 *
 * @var string $label      Label for the dropdown.
 * @var string $value      Value for the dropdown.
 * @var string $id         ID of the dropdown.
 * @var string $name       Name attribute for the dropdown.
 * @var string $dependency The dependency attributes for the control wrapper.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--dropdown"
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<label
		class="tribe-widget-form-control__label"
		for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<select
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="tribe-widget-form-control__input widefat"
	>
		<?php foreach ( $options as $option ) { ?>
			<option
				value="<?php echo esc_attr( $option['value'] ); ?>"
				<?php selected( $value, $option['value'] ); ?>
			>
				<?php echo esc_html( $option['text'] ); ?>
			</option>
		<?php } ?>
	</select>
</div>
