<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require 'header.php';
?>
<div id="modern-tribe-info">
	<h1><?php esc_html_e( 'Instructions', 'the-events-calendar' ); ?></h1>
	<p>
		<?php esc_html_e( 'The settings below will impact events imported from files and other websites. Be sure to save your changes before starting to import events.', 'the-events-calendar' ); ?>
	</p>
	<?php
	/**
	 * Hook to this action to print More information on the Instructions Box
	 */
	do_action( 'tribe_import_general_infobox' ); ?>
</div>

<div class="tribe-settings-form">
	<form method="POST">
		<?php
		/**
		 * Hook to this filter to print More fields on the Importer Settings page
		 */
		$fields = apply_filters( 'tribe_import_general_settings', array() );

		foreach ( $fields as $key => $field_args ) {
			if ( false === strpos( $key, 'imported_post_status' ) && false === strpos( $key, 'imported_encoding_status' ) ) {
				$value = Tribe__Settings_Manager::get_option( $key, null );
			} else {
				/**
				 * Regular Expression to match "suboption_name" given "option_name[suboption_name]"
				 */
				if ( preg_match( '/\[([^\]]+)\]/i', $key, $match ) ) {
					$type = end( $match );
				} else {
					$type = 'csv';
				}
				if ( strpos( $key, 'imported_encoding_status' ) === false ) {
					$value = Tribe__Events__Importer__Options::get_default_post_status( $type );
				} else {
					$value = Tribe__Events__Importer__Options::get_default_encoding_status( $type );
				}
			}

			new Tribe__Field( $key, $field_args, $value );
		}
		wp_nonce_field( 'tribe-import-general-settings', 'tribe-import-general-settings' );
		?>
		<div class="tribe-settings-form-wrap">
			<p>
				<input type="submit" name="tribe-events-importexport-general-settings-submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'the-events-calendar' ); ?>"/>
			</p>
		</div>
	</form>
</div>

<?php
require 'footer.php';
