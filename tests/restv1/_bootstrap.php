<?php
// Here you can initialize variables that will be available to your tests

use Codeception\Util\Autoload;

Autoload::addNamespace( '\\', __DIR__ );

$wp_root    = getenv( 'WP_ROOT_FOLDER' );
$mu_plugins = $wp_root . '/wp-content/mu-plugins';

if ( ! is_dir( $mu_plugins ) && ! mkdir( $mu_plugins ) && ! is_dir( $mu_plugins ) ) {
	throw new \RuntimeException( 'Could not create mu-plugins directory.' );
}

if ( ! file_exists( $mu_plugins . '/restv1-wp-verify-nonce.php' ) ) {
	if ( ! copy(
		codecept_data_dir( 'mu-plugins/restv1-wp-verify-nonce.php' ),
		$mu_plugins . '/restv1-wp-verify-nonce.php'
	) ) {
		throw new \RuntimeException( 'Could not copy restv1-wp-verify-nonce.php mu-plugin in place.' );
	}
	codecept_debug('restv1-wp-verify-nonce.php mu-plugin set up');
}
