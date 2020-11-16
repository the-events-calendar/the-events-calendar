<?php
/**
 * Admin View: Widget Checkbox Component
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/checkbox.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var string $label Label for the checkbox.
 * @var string $value Value for the checkbox.
 * @var string $id    ID of the checkbox.
 * @var string $name  Name attribute for the checkbox.
 */

?>
<div
		class="tribe-widget-form-control tribe-widget-form-control--checkbox"
		<?php echo esc_html( $dependency ); ?>
>
	<input
			class="tribe-widget-form-control__input"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			type="checkbox"
			value="1"
			<?php checked( tribe_is_truthy( $value ), true ); ?>
	/>
	<label
			class="tribe-widget-form-control__label"
			for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
</div>
