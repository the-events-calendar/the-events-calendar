<?php
namespace Tribe\Events\Importer;

function get_image_url( $extension = 'jpg' ) {
	return plugins_url( 'tests/_data/csv-import-test-files/featured-image/images/featured-image.' . $extension, codecept_root_dir( 'the-events-calendar.php' ) );
}

function get_image_path( $extension = 'jpg' ) {
	return codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.' . $extension );
}
