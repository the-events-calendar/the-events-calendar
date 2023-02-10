<?php

namespace Tribe\Events\Integrations;

use Tribe\Events\Admin\Notice\Install_Event_Tickets;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use tad\WP\Snapshots\WPHtmlOutputDriver;


class Install_Tickets_NoticeTest extends HtmlPartialTestCase {
	protected $partial_path = 'notice/install-event-tickets';

	/**
	 * @test
	 */
	public function it_should_render_notice_install() {
		$et_notice = tribe( Install_Event_Tickets::class );
		$html      = $et_notice->notice_install();
		$driver    = new WPHtmlOutputDriver( home_url(), 'http://tec.dev' );
		$driver->setTimeDependentAttributes( [ 'data-nonce' ] );

		$this->assertMatchesSnapshot( $html, $driver );
	}

	/**
	 * @test
	 */
	public function it_should_render_notice_activate() {
		$et_notice = tribe( Install_Event_Tickets::class );
		$html      = $et_notice->notice_activate();
		$driver    = new WPHtmlOutputDriver( home_url(), 'http://tec.dev' );
		$driver->setTimeDependentAttributes( [ 'data-nonce' ] );

		$this->assertMatchesSnapshot( $html, $driver );
	}
}
