<?php

namespace Tribe\Events\Views\V2\Template;

use Tribe\Events\Views\V2\Assets as Event_Assets;
use Tribe\Events\Views\V2\Template_Bootstrap;

/**
 * Class Full_Site_Editor
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Full_Site_Editor {

	/**
	 * Renders the IFrame for any block related to the archive of events.
	 *
	 * @since TBD
	 *
	 */
	public function render_iframe() {
		define( 'IFRAME_REQUEST', true );
		tribe_asset_enqueue_group( Event_Assets::$group_key );
		tribe_asset_enqueue( 'tec-events-iframe-content-resizer' );

		// Use iFrame Header -- WP Method.
		iframe_header();
		?>
		<style>
			body.iframe {
				background: transparent;
			}
		</style>

		<?php

		echo tribe( Template_Bootstrap::class )->get_view_html();

		// Use iFrame Footer -- WP Method.
		iframe_footer();

		// We need nothing else here.
		exit;
	}
}