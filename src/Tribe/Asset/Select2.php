<?php


class Tribe__Events__Asset__Select2 extends Tribe__Events__Asset__Abstract_Asset {

	/**
	 * @var array
	 */
	protected $aliases = array(
		'select2' => array(
			'advanced-custom-fields-pro/acf.php' => 'select2',
		),
	);

	public function handle() {
		$css_path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'select2/select2.css', true );
		$path     = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'select2/select2.js', true );

		wp_enqueue_style( $this->prefix . '-select2-css', $css_path );

		// we know of other plugins loading a version of select2 compatible with our needs
		// let's not queue the script twice.
		if ( ! $this->has_script_alias( 'select2' ) ) {
			$script_handle = $this->prefix . '-select2';
			wp_enqueue_script( $script_handle, $path, 'jquery', '3.2' );
			Tribe__Events__Template_Factory::add_vendor_script( $script_handle );
		}
	}

}