<?php
/**
 * Class for managing technical support components
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsProSupport' ) ) {
	class TribeEventsProSupport {
		
		public static $support;

		/**
		 * Generate a support link based on the user's options. This link is serialized and base64 encoded. On the other end we can decode it and then unserialize it to create a ticket.
		 *
		 * @return void
		 * @author Peter Chester
		 */
		public static function supportLink() {
			$installinfo = TribeEvents::getOptions();
			$installinfo['site'] = get_bloginfo('url');
			$installinfo = serialize($installinfo);
			$installinfo = base64_encode($installinfo);
			return TribeEvents::$supportUrl.'?installinfo='.$installinfo;
		}

	}
}
?>