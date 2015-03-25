<?php
/**
 * @var string[] $messages
 * @var string   $import_type
 * @var string[] $header
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$mapper = new Tribe__Events__Importer__Column_Mapper( $import_type );
if ( isset( $_POST['column_map'] ) ) {
	$mapper->set_defaults( $_POST['column_map'] );
} else {
	$mapper->set_defaults( get_option( 'tribe_events_import_column_mapping', array() ) );
}

require_once 'header.php';
?>

	<h3><?php echo sprintf( __( 'Column Mapping: %s', 'tribe-events-calendar' ), ucwords( $import_type ) ) ?></h3>

<?php if ( ! empty( $messages ) ): ?>
	<div class="error"><?php echo implode( '', $messages ); ?></div>
<?php endif; ?>
	<div class="form">
		<p><?php _e( 'Columns have been mapped based on your last import. Please ensure the selected fields match the columns in your CSV file.', 'tribe-events-calendar' ) ?></p>

		<form method="POST">
			<table class="">
				<thead>
				<th><?php _e( 'Column Headings', 'tribe-events-calendar' ); ?></th>
				<th><?php _e( 'Event Fields', 'tribe-events-calendar' ); ?></th>
				</thead>
				<?php foreach ( $header as $col => $title ): ?>
					<tr>
						<td><?php echo $title ?></td>
						<td><?php echo $mapper->make_select_box( $col ) ?></td>
					</tr>
				<?php endforeach ?>

				<tr>
					<td colspan="2">
						<?php submit_button( __( 'Perform Import', 'tribe-events-calendar' ) ); ?>
					</td>
				</tr>

			</table>

			<input type="hidden" name="import_type" value="<?php echo $import_type ?>" />
			<input type="hidden" name="ecp_import_action" value="import" />


		</form>
	</div>

<?php
/* This doesn't seem to do anything. Hiding it for now
/*<script>
	jQuery(document).ready(function() {
		jQuery('.tribe-events-imnporter-custom-field').css('visibility', 'hidden');
	});
	
	function tribeShowCf(field, col){
		
		if( jQuery('select[name="column_map['+col+']"]').val() == 'custom_field' ){
			jQuery('select[name="txt_'+col+'"]').css('visibility', 'visible');
		}else{
			jQuery('select[name="txt_'+col+'"]').css('visibility', 'hidden');
		}
	
	}
</script>/* */
?>
<?php
require_once 'footer.php';
?>