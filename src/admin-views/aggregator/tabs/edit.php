<?php
$record = new stdClass;

if ( ! empty( $_GET['id'] ) ) {
	$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( (int) $_GET['id'] );
}

$aggregator_action = 'edit';
?>
<input type="hidden" name="aggregator[post_id]" id="tribe-post_id" value="<?php echo esc_attr( $record->post->ID ); ?>">
<?php

include dirname( __FILE__ ) . '/import-form.php';
