<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerArchiveSearchCest extends BaseRestCest {
	/**
	 * It should allow searching organizers by title
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_title( Tester $I ) {
		$I->haveOrganizerInDatabase( [ 'post_title' => 'Organizer foo' ] );
		$I->haveOrganizerInDatabase( [ 'post_title' => 'Organizer baz' ] );
		$I->haveOrganizerInDatabase( [ 'post_title' => 'Organizer bar' ] );

		$I->sendGET( $this->organizers_url, [
			'search' => 'woo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->organizers_url, [
			'search' => 'Organizer',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->organizers );

		$I->sendGET( $this->organizers_url, [
			'search' => 'Organizer foo',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->organizers );
	}

	/**
	 * It should allow searching organizers by description
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_description( Tester $I ) {
		$I->haveOrganizerInDatabase( [ 'post_content' => 'Content foo' ] );
		$I->haveOrganizerInDatabase( [ 'post_content' => 'Content baz' ] );
		$I->haveOrganizerInDatabase( [ 'post_content' => 'Content bar' ] );

		$I->sendGET( $this->organizers_url, [
			'search' => 'woo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->organizers_url, [
			'search' => 'Content',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->organizers );

		$I->sendGET( $this->organizers_url, [
			'search' => 'Content foo',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->organizers );
	}

	/**
	 * It should allow searching organizers by excerpt
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_excerpt( Tester $I ) {
		$I->haveOrganizerInDatabase( [ 'post_excerpt' => 'Excerpt foo' ] );
		$I->haveOrganizerInDatabase( [ 'post_excerpt' => 'Excerpt baz' ] );
		$I->haveOrganizerInDatabase( [ 'post_excerpt' => 'Excerpt bar' ] );

		$I->sendGET( $this->organizers_url, [
			'search' => 'woo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->organizers_url, [
			'search' => 'Excerpt',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->organizers );

		$I->sendGET( $this->organizers_url, [
			'search' => 'Excerpt foo',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->organizers );
	}

	/**
	 * It should allow searching organizers by custom fields
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_custom_fields( Tester $I ) {
		$I->haveOrganizerInDatabase( [
			                         'post_title'   => 'Organizer Foo',
			                         'post_content' => 'lorem dolor',
			                         'post_excerpt' => 'sed nunqua',
			                         'meta_input'   => [
				                         '_OrganizerPhone'       => '111111111',
				                         '_OrganizerEmail'          => 'one@common.com',
				                         '_OrganizerWebsite'      => 'http://one.com',
			                         ],
		                         ] );
		$I->haveOrganizerInDatabase( [
			                         'post_title'   => 'Organizer Bar',
			                         'post_content' => 'dolor sit',
			                         'post_excerpt' => 'altera via',
			                         'meta_input'   => [
				                         '_OrganizerPhone'       => '111112222',
				                         '_OrganizerEmail'          => 'two@common.com',
				                         '_OrganizerWebsite'      => 'http://two.com',
			                         ],
		                         ] );
		$I->haveOrganizerInDatabase( [
			                         'post_title'   => 'Organizer Baz',
			                         'post_content' => 'sit nunqua',
			                         'post_excerpt' => 'Caesar docet',
			                         'meta_input'   => [
				                         '_OrganizerPhone'       => '222223333',
				                         '_OrganizerEmail'          => 'three@three.com',
				                         '_OrganizerWebsite'      => 'http://three.com',
			                         ],
		                         ] );

		$I->sendGET( $this->organizers_url, [
			'search' => 'woo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->organizers_url, [
			'search' => 'Organizer',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->organizers );

		$searches = [
			'1111'            => 2,
			'111111111'       => 1,
			'222223333'       => 1,
			'three@three.com' => 1,
			'common.com'      => 2,
			'http://one.com'  => 1,
			'@common.com'     => 2,
		];
		foreach ( $searches as $s => $expected ) {
			$I->sendGET( $this->organizers_url, [
				'search' => $s,
			] );

			$I->seeResponseCodeIs( 200 );
			$I->seeResponseIsJson();
			$response = json_decode( $I->grabResponse() );
			$I->assertCount( $expected, $response->organizers );
		}
	}
}
