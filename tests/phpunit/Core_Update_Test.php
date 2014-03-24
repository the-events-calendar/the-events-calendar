<?php

/**
 * Class Core_Update_Test
 * @group updates
 */
class Core_Update_Test extends WP_UnitTestCase {
	/**
	 * @group ignore
	 */
	public function test_plugin_info() {
		include ABSPATH . WPINC . '/version.php'; // include an unmodified $wp_version

		if ( defined('WP_INSTALLING') )
			return false;

		// If running blog-side, bail unless we've not checked in the last 12 hours
		if ( !function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$dir = 'events-calendar';
		$rel_path = $dir.'/the-events-calendar.php';

		$plugins = array(
			$rel_path => array(
				'Name' => 'The Events Calendar',
				'PluginURI' => '',
				'Version' => '1.0',
				'Description' => 'The Events Calendar is a carefully crafted, extensible plugin that lets you easily share your events. Beautiful. Solid. Awesome.',
				'Author' => 'Modern Tribe, Inc.',
				'AuthorURI' => 'http://m.tri.be/1x',
				'DomainPath' => '',
				'Network' => false,
				'Title' => 'The Events Calendar',
				'AuthorName' => 'Modern Tribe, Inc.',
			),
		);
		$translations = wp_get_installed_translations( 'plugins' );

		$active = array(
			$rel_path,
		);
		$current = new stdClass;

		$new_option = new stdClass;
		$new_option->last_checked = time();

		// Update last_checked for current to prevent multiple blocking requests if request hangs
		$current->last_checked = time();

		$to_send = compact( 'plugins', 'active' );

		$locales = array( get_locale() );

		$options = array(
			'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
			'body' => array(
				'plugins'      => json_encode( $to_send ),
				'translations' => json_encode( $translations ),
				'locale'       => json_encode( $locales ),
			),
			'user-agent' => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . get_bloginfo( 'url' )
		);

		$url = $http_url = 'http://api.wordpress.org/plugins/update-check/1.1/';
		if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
			$url = set_url_scheme( $url, 'https' );

		$raw_response = wp_remote_post( $url, $options );

		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		$this->assertNotEmpty($response['plugins']);

		$plugin = reset($response['plugins']);

		$this->assertNotEmpty($plugin);
		$this->assertEquals('the-events-calendar', $plugin['slug']);

	}
}
 