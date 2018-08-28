<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Tribe_Select2 extends Tribe__Events__Asset__Abstract_Asset {

	/**
	 * @var array
	 */
	protected $aliases = array(
		'tribe-select2' => array(
			'advanced-custom-fields-pro/acf.php' => 'select2',
		),
	);

	public function handle() {
		wp_enqueue_style( 'tribe-select2-css' );

		// we know of other plugins loading a version of select2 compatible with our needs
		// let's not queue the script twice.
		if ( ! $this->has_script_alias( 'tribe-select2' ) ) {
			$path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'select2/select2.js', true );
			wp_enqueue_script( 'tribe-select2', $path, 'jquery', $this->filter_js_version() );
			Tribe__Events__Template_Factory::add_vendor_script( 'tribe-select2' );
		}
	}

}