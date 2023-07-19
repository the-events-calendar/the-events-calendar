<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since   5.13.0
 *
 * @package TEC\Events\Legacy\Views\V1
 */

 namespace TEC\Events\SEO;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\SEO\No_Index;


/**
 * Class Provider
 *
 * @since   5.13.0

 * @package TEC\Events\Legacy\Views\V1
 */
class Provider extends Service_Provider {
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->hooks();
	}

	public function hooks() {
		add_action( 'get_header', [ $this, 'issue_noindex' ] );
	}

	public function issue_noindex() {
		return $this->container->make( No_Index::class )->issue_noindex();
	}
}
