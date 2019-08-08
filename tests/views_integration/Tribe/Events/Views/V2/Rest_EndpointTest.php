<?php
namespace Tribe\Events\Views\V2;

class Rest_EndpointTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Rest_Endpoint();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Rest_Endpoint::class, $sut );
	}

	/**
	 * @test
	 */
	public function it_should_have_correct_url_for_available_rest_api() {
		$rest = $this->make_instance();

		$this->assertStringContainsString( 'tribe/views/v2/html', $rest->get_url() );
	}

	/**
	 * @test
	 */
	public function it_should_fallback_to_ajax_url_when_rest_not_available() {
		$rest = $this->make_instance();

		add_filter( 'tribe_events_views_v2_rest_endpoint_available', '__return_false' );

		$this->assertStringContainsString( 'admin-ajax.php', $rest->get_url() );
	}

	public function arguments_sanitize_data_provider() {
		return [
			'not_supported_param_should_be_excluded' => [
				[
					'not-support' => true,
					'url' => home_url(),
				],
				[
					'url' => home_url(),
				],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider arguments_sanitize_data_provider
	 */
	public function it_should_filter_and_sanitize_params( $input, $expected ) {
		$rest = $this->make_instance();
		$request = $rest->get_mocked_rest_request( $input );

		$this->assertEquals( $expected, $request->get_params() );
	}

}