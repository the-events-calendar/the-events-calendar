<?php
/**
 * Admin View: Widget Fieldset Component.
 *
 * This component is different in that it calls other components!
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/fieldset.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var string              $label       Title for the fieldset.
 * @var string              $description Description for the fieldset.
 * @var string              $classes     Classes to add to the fieldset.
 * @var string              $dependency  Dependency attribute for the fieldset.
 * @var array<string,mixed> $children    Child elements for the fieldset.
 * @var string              $name        Name to pass through to child inputs.
 */

use Tribe__Utils__Array as Arr;

$fieldset_classes = array_merge( [ 'tribe-widget-form-control', 'tribe-widget-form-control--fieldset' ], Arr::list_to_array( $classes, ' ' ) );

?>
<fieldset
	<?php tribe_classes( $fieldset_classes ); ?>
	<?php echo esc_html( $dependency ); ?>
>
	<?php if ( ! empty( $label ) ) : ?>
		<legend class="tribe-widget-form-control--legend"><?php echo esc_html( $label ); ?></legend>
	<?php endif; ?>

	<?php
	foreach ( $children as $child_id => $child ) {
		// The provided name/value are passed through for radios.
		$this->section_handler(
			$child_id,
			$child,
			[
				'name'  => $name,
				'value' => $value,
			]
		);
	}
	?>
</fieldset>
