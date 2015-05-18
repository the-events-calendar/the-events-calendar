<?php
// Don't load directly
if( !defined( 'ABSPATH' ) ){
	die( '-1' );
}

require( 'header.php' );

?>
	<div id="modern-tribe-info">
		<h2><?php _e( 'The Events Calendar: Import', 'tribe-events-calendar' ); ?></h2>

		<h3><?php _e( 'Instructions', 'tribe-events-calendar' ); ?></h3>
		<p>
			<?php printf( __( 'To import events, first select a %sDefault Import Event Status%s below to assign to your imported events.', 'tribe-events-calendar' ), '<strong>', '</strong>' ); ?>
		</p>
		<p>
			<?php _e( 'Once your setting is saved, move to the applicable Import tab to select import specific criteria.' );
			?>
		</p>
	</div>

	<div class="tribe-settings-form">
		<form method="POST">
			<div class="tribe-settings-form-wrap">
				<h3><?php _e( 'Import Settings', 'tribe-events-calendar' ); ?></h3>
				<p>
					<?php _e( 'Default imported event status:', 'tribe-events-calendar' ); ?>

					<?php $import_statuses = array(
						'publish' => __( 'Published', 'tribe-events-calendar' ),
						'pending' => __( 'Pending', 'tribe-events-calendar' ),
						'draft'   => __( 'Draft', 'tribe-events-calendar' ),
					); ?>
					<select name="imported_post_status">
						<?php
						foreach( $import_statuses as $key => $value ){
							echo '<option value="' . $key . '" ' . selected( $key, Tribe__Events__Main::getOption( 'imported_post_status', 'publish' ) ) . '>
							' . $value . '
						</option>';
						}
						?>
					</select>
				</p>


				<?php
				wp_nonce_field( 'tribe-import-general-settings', 'tribe-import-general-settings' );
				?>
				<p>
					<input type="submit" name="tribe-events-importexport-general-settings-submit" class="button-primary" value="Save Settings"/>
				</p>
			</div>
		</form>
	</div>

<?php


require( 'footer.php' );