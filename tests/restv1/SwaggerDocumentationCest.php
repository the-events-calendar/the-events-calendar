<?php


class SwaggerDocumentationCest extends BaseRestCest
{
	/**
	 * @test
	 * it should expose a Swagger documentation endpoint
	 */
	public function it_should_expose_a_swagger_documentation_endpoint(Restv1Tester $I) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
	}
}
