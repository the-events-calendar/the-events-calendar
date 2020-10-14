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
 * @var string $label Label for the checkbox.
 * @var string $value Value for the checkbox.
 * @var string $id    ID of the checkbox.
 * @var string $name  Name attribute for the checkbox.
 *
 * @version TBD
 */

?>
<p
		class="tribe-filter-bar-c-dropdown tribe-common-form-control-dropdown"
>
	<label
			class="tribe-common-form-control-dropdown__label"
			for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<select
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			class="tribe-common-form-control-dropdown__input widefat"
	>
		<?php foreach ( $options as $option ) { ?>
			<option
					value="<?php echo $option['value']; ?>"
					<?php esc_html( selected( $option['value'], $value ) ); ?>
			>
				<?php echo esc_html( $option['text'] ); ?>
			</option>
		<?php } ?>
	</select>
</p>
