<?php

namespace Tribe\Events\Views\V2;

class UrlTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Url::class, $sut );
	}

	/**
	 * @return Url
	 */
	private function make_instance() {
		return new Url();
	}

	/**
	 * It should return default if view slug not found in URL
	 *
	 * @test
	 */
	public function should_return_default_if_view_slug_not_found_in_url() {
		$url = new Url( 'http://example.com' );
		$this->assertEquals( 'default', $url->get_view_slug() );
	}

	/**
	 * It should correctly read the view slug from the url if provided
	 *
	 * @test
	 * @dataProvider test_urls
	 */
	public function should_correctly_read_the_view_slug_from_the_url_if_provided( $test_url ) {
		$url = new Url( $test_url );
		$this->assertEquals( 'test', $url->get_view_slug() );
	}

	public function test_urls() {
		return [
			'w_slash'             => [ trailingslashit( home_url() ) . '/?view=test' ],
			'wo_slash'            => [ trailingslashit( home_url() ) . '?view=test' ],
			'w_other_args_first'  => [ trailingslashit( home_url() ) . '?view=test&foo=bar' ],
			'w_other_args_last'   => [ trailingslashit( home_url() ) . '?foo=bar&view=test' ],
			'w_other_args_middle' => [ trailingslashit( home_url() ) . '?foo=bar&view=test&bar=baz' ],
		];
	}
}