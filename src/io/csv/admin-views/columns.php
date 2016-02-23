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

<h2><?php printf( esc_html__( 'Column Mapping: %s', 'the-events-calendar' ), ucwords( $import_type ) ) ?></h2>
<?php if ( ! empty( $messages ) ): ?>
	<div class="error"><?php echo implode( '', $messages ); ?></div>
<?php endif; ?>
	<div class="form">
		<p><?php esc_html_e( 'Columns have been mapped based on your last import. Please ensure the selected fields match the columns in your CSV file.', 'the-events-calendar' ) ?></p>

		<form method="POST" id="import">
			<table class="">
				<thead>
				<th><?php esc_html_e( 'Column Headings', 'the-events-calendar' ); ?></th>
				<th><?php esc_html_e( 'Event Fields', 'the-events-calendar' ); ?></th>
				</thead>
				<?php foreach ( $header as $col => $title ): ?>
					<tr>
						<td><?php echo $title ?></td>
						<td><?php echo $mapper->make_select_box( $col ) ?></td>
					</tr>
				<?php endforeach ?>

				<tr>
					<td colspan="2">
						<?php submit_button( esc_html__( 'Perform Import', 'the-events-calendar' ) ); ?>
					</td>
				</tr>

			</table>

			<input type="hidden" name="import_type" value="<?php echo $import_type ?>" />
			<input type="hidden" name="ecp_import_action" value="import" />
		</form>
	</div>
<?php
require_once 'footer.php';
