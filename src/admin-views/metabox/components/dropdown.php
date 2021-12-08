<?php
/**
 * View: TEC Metabox Dropdown.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/metabox/components/dropdown.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 5.11.0
 *
 * @var string               $label    Label for the dropdown input.
 * @var string               $id       ID of the dropdown input.
 * @var string               $class    Class attribute for the dropdown input.
 * @var string               $name     Name attribute for the dropdown input.
 * @var string|int           $selected The selected option id.
 * @var array<string,string> $options  Associative array of options for the dropdown.
 * @var array<string,string> $attrs    Associative array of attributes of the dropdown.
 */
?>
<div
	class="tribe-events-status tribe-events-status-select"
>
	<label
		class="screen-reader-text tribe-events-status-label__text"
		for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<select
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="tribe-dropdown <?php echo esc_attr( $class ); ?>"
		value="<?php echo esc_attr( $selected ); ?>"
		<?php tribe_attributes( $attrs ) ?>
	>
		<?php foreach ( $options as $option ) : ?>
			<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $option['value'], $selected ); ?>><?php echo esc_html( $option['text'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
