<?php
/**
 * Admin View: List Widget
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @var Widget_Abstract $widget_obj   An instance with the widget abstract.
 * @var array<array>    $admin_fields An array of admin fields to display in the widget form.
 *
 * @version TBD
 */

use \Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Utils__Array as Arr;

if ( empty( $admin_fields ) ) {
	return;
}

foreach ( $admin_fields as $field_id => $field ) {
	// Handle a section start. May contain a section title.
	if ( false !== stripos( $field_id, 'section_start' ) ) {
		$section_classes = [ 'tribe-events-widget-admin-form__input-section' ];
		if ( ! empty( $field['classes'] ) ) {
			$classes = $field['classes'];

			if ( ! is_array( $classes ) ) {
				$classes = explode( ' ', $classes );
			}

			$section_classes = array_merge( $section_classes, $classes );
		}

		?>
		<div <?php tribe_classes( $section_classes ); ?>>
			<?php
			if ( ! empty( $field['title'] ) ) {
				// Note: the actual widget title/handle is an <h3>.
				?>
				<h4><?php echo esc_html( $field['title'] ); ?></h4>
				<?php
			}

			continue;
	}

	// Handle a section end.
	if ( false !== stripos( $field_id, 'section_end' ) ) {
		?>
		</div>
		<?php
		continue;
	}

	$data = [
		'for'     => $widget_obj->get_field_id( $field_id ),
		'id'      => $widget_obj->get_field_id( $field_id ),
		'name'    => $widget_obj->get_field_name( $field_id ),
		'label'   => Arr::get( $field, 'label', '' ),
		'options' => Arr::get( $field, 'options', [] ),
		'value'   => isset( ${$field_id} ) ? ${$field_id} : null,
	];

	$data = apply_filters( "tribe_events_view_v2_list_widget_admin_form_{$field['type']}_data", $data, $this, $field_id, $field, $widget_obj );


	switch ( $field['type'] ) {
		case 'checkbox':
			$this->template( 'widgets/components/checkbox', $data );
			break;
		case 'radio':
			$this->template( 'widgets/components/radio', $data );
			break;
		case 'dropdown':
			$this->template( 'widgets/components/dropdown', $data );
			break;
		case 'text':
			$this->template( 'widgets/components/text', $data );
			break;
		default:
			do_action( "tribe_events_view_v2_list_widget_admin_form_{$field['type']}_input", $data, $widget_obj, $field );
			break;
	}
}
