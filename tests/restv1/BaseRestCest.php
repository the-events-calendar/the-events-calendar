<?php

use Codeception\Configuration;

class BaseRestCest {

	/**
	 * @var string
	 */
	protected $rest_disable_option = 'rest-v1-disabled';
	/**
	 * @var string The site full URL to the homepage.
	 */
	protected $site_url;
	/**
	 * @var string
	 */
	protected $tec_option = 'tribe_events_calendar_options';

	/**
	 * @var string The site full URL to the REST API root.
	 */
	protected $rest_url;

	/**
	 * @var string
	 */
	protected $events_url;

	/**
	 * @var
	 */
	protected $venues_url;

	/**
	 * @var
	 */
	protected $organizers_url;

	/**
	 * @var string
	 */
	protected $categories_url;

	/**
	 * @var string
	 */
	protected $tags_url;

	/**
	 * @var string
	 */
	protected $documentation_url;

	public function _before( Restv1Tester $I ) {
		$config                  = Configuration::suiteSettings( 'restv1', Configuration::config() );
		$this->site_url          = $config['modules']['config']['WPBrowser']['url'];
		$this->rest_url          = $config['modules']['config']['REST']['url'];
		$this->events_url        = $this->rest_url . 'events';
		$this->venues_url        = $this->rest_url . 'venues';
		$this->organizers_url    = $this->rest_url . 'organizers';
		$this->categories_url    = $this->rest_url . 'categories';
		$this->tags_url          = $this->rest_url . 'tags';
		$this->documentation_url = $this->rest_url . 'doc';
		wp_cache_flush();
	}

	/**
	 * Asserts an anonymous visitor cannot open the event's single page.
	 *
	 * @param Restv1Tester $I        The tester.
	 * @param int          $event_id The event post ID.
	 */
	protected function dont_see_event_publicly( Restv1Tester $I, $event_id ) {
		$I->deleteHeader( 'X-WP-Nonce' );
		$I->resetCookie( LOGGED_IN_COOKIE );
		$I->sendGET( $this->site_url . '/?post_type=tribe_events&p=' . $event_id );
		$I->seeResponseCodeIs( 404 );
	}
}
