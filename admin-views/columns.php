<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();

require_once 'header.php';
?>

<h3><?php echo sprintf( __( 'Column Mapping: %s', 'tribe-events-calendar-pro' ), ucwords($import_type) )  ?></h3>

<?php if ( $error_message != '' ): ?>
    <p><?php _e( 'There was an error:', 'tribe-events-calendar-pro' ) ?> <?php echo $error_message ?></p>
    <p><a href="#" onClick="history.go(-1);return false;"><?php _e( 'Go back', 'tribe-events-calendar-pro' ) ?></a></p>
<?php else: ?>
    <div class="form">
        <p><?php _e( 'Please choose the fields that best match the columns in your CSV file.', 'tribe-events-calendar-pro' ) ?>
        </p>
	<form method="POST">
	    <table class="">
	        <?php foreach ( $csv->titles as $col => $title): ?>
		    <tr>
			<td><?php echo $title ?></td>
			<td><?php echo $importer_instance->generateColumnSelects( $col, $title, $import_type ) ?></td>
		    </tr>
		<?php endforeach ?>
	    
	    <tr><td colspan="2">
	    <p style="text-align: center;"><input type="submit" class="button-primary" style="" value="<?php _e( 'Perform Import', 'tribe-events-calendar-pro' ) ?>" /></p>
	    </td></tr>
	    
	    </table>
	    
		<input type="hidden" name="import_type" value="<?php echo $import_type ?>" />
		<input type="hidden" name="ecp_import_action" value="import" />
		
	    
	</form>
    </div>

<script>
	jQuery(document).ready(function() {
		jQuery('.tribe-events-imnporter-custom-field').css('visibility', 'hidden');
	});
	
	function tribeShowCf(field, col){
		
		if( jQuery('select[name="col_'+col+'"]').val() == 'custom_field' ){
			jQuery('select[name="txt_'+col+'"]').css('visibility', 'visible');
		}else{
			jQuery('select[name="txt_'+col+'"]').css('visibility', 'hidden');
		}
	
	}
</script>

<?php endif;

require_once 'footer.php';
?>