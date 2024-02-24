<?php

namespace TEC\Events\Custom_Tables\V1\Integrations\ACF;

use Tribe\Events\Views\V2\Query\Event_Query_Controller;
use Tribe__Events__Main as TEC;
use TEC\Events\Custom_Tables\V1\Integrations\ACF\Controller as ACF_Controller;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();
		tribe()->register( ACF_Controller::class );
	}

	public function tearDown(): void {
		tribe( ACF_Controller::class )->unregister();
		parent::tearDown();
	}

	/**
	 * It should not handle Event queries by default
	 *
	 * @test
	 */
	public function should_not_handle_event_queries_by_default(): void {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );

		$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );
	}

	/**
	 * It should handle Event queries while rendering a non supported field
	 *
	 * The Controller will hook early to redirect queries and late to stop handling them.
	 * Due to this particular arrangement, the test method phases are labeled.
	 *
	 * @test
	 */
	public function should_handle_event_queries_while_rendering_a_non_supported_field(): void {
		// Assert
		$assert = function () {
			// Create a query for Events the modifier should apply to.
			$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );

			$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );
		};

		// Arrange
		$between_priority = ( ACF_Controller::LATE_PRIORITY - ACF_Controller::EARLY_PRIORITY ) / 2;
		add_action( 'acf/render_field/type=some_type', $assert, $between_priority );

		// Act
		do_action( 'acf/render_field/type=some_type' );
	}

	public function supported_field_types(): \Generator {
		foreach ( ACF_Controller::get_supported_field_types() as $field_type ) {
			yield $field_type => [ $field_type ];
		}
	}

	/**
	 * It should handle Event queries while rendering a supported field
	 *
	 * @test
	 * @dataProvider supported_field_types
	 */
	public function should_handle_event_queries_while_rendering_a_supported_field( string $field_type ): void {
		// Assert
		$assert = function () {
			// Create a query for Events the modifier should apply to.
			$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );

			$this->assertTrue( tribe( Query_Modifier::class )->applies_to( $query ) );
		};

		// Arrange
		$between_priority = ( ACF_Controller::LATE_PRIORITY - ACF_Controller::EARLY_PRIORITY ) / 2;
		add_action( "acf/render_field/type=$field_type", $assert, $between_priority );

		// Act
		do_action( "acf/render_field/type=$field_type" );
	}

	/**
	 * It should not handle queries for multiple post types including Event
	 *
	 * @test
	 * @dataProvider supported_field_types
	 */
	public function should_not_handle_queries_for_multiple_post_types_including_event( string $field_type ): void {
		// Assert
		$assert = function () {
			// Create a query for Events the modifier should apply to.
			$query = new \WP_Query( [ 'post_type' => [ TEC::POSTTYPE, 'post' ] ] );

			$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );
		};

		// Arrange
		$between_priority = ( ACF_Controller::LATE_PRIORITY - ACF_Controller::EARLY_PRIORITY ) / 2;
		add_action( "acf/render_field/type=$field_type", $assert, $between_priority );

		// Act
		do_action( "acf/render_field/type=$field_type" );
	}

	/**
	 * It should not redirect AJAX queries not for Event
	 *
	 * @test
	 */
	public function should_not_redirect_ajax_queries_not_for_event(): void {
		// Assert
		$assert = function ( $args ) {
			$this->assertEquals( [], $args );
			$query = new \WP_Query( [ 'post_type' => 'post' ] );
			$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );

			return $args;
		};

		// Arrange
		add_filter( 'acf/fields/post_object/query', $assert, ACF_Controller::AJAX_QUERY_PRIORITY + 10 );

		// Act
		$filtered = apply_filters( 'acf/fields/post_object/query', [], [
			'type' => ACF_Controller::get_supported_field_types()[0],
		] );

		// Assert more.
		$this->assertEquals( [], $filtered );
	}

	/**
	 * It should not redirect AJAX queries for Event when field type not supported
	 *
	 * @test
	 */
	public function should_not_redirect_ajax_queries_for_event_when_field_type_not_supported(): void {
		// Assert
		$assert = function ( $args ) {
			$this->assertEquals( [], $args );
			$query = new \WP_Query( [ 'post_type' => 'post' ] );
			$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );

			return $args;
		};

		// Arrange
		add_filter( 'acf/fields/post_object/query', $assert, ACF_Controller::AJAX_QUERY_PRIORITY + 10 );

		// Act
		$filtered = apply_filters( 'acf/fields/post_object/query', [], [
			'type' => 'not_supported',
		] );

		// Assert more.
		$this->assertEquals( [], $filtered );
	}

	/**
	 * It should redirect AJAX queries for Events for supported field types
	 *
	 * @test
	 * @dataProvider supported_field_types
	 */
	public function should_redirect_ajax_queries_for_events_for_supported_field_types( string $field_type ): void {
		// Assert
		$assert = function ( $args ) {
			$this->assertEquals( [], $args );
			$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
			$this->assertTrue( tribe( Query_Modifier::class )->applies_to( $query ) );

			return $args;
		};

		// Arrange
		add_filter( 'acf/fields/post_object/query', $assert, ACF_Controller::AJAX_QUERY_PRIORITY + 10 );

		// Act
		$filtered = apply_filters( 'acf/fields/post_object/query', [], [
			'type' => $field_type,
		] );

		// Assert more.
		$this->assertEquals( [], $filtered );
	}

	/**
	 * It should not redirect AJAX queries for Events and other post types
	 *
	 * @test
	 * @dataProvider supported_field_types
	 */
	public function should_not_redirect_ajax_queries_for_events_and_other_post_types( string $field_type ): void {
		// Assert
		$assert = function ( $args ) {
			$this->assertEquals( [], $args );
			$query = new \WP_Query( [ 'post_type' => [ TEC::POSTTYPE, 'post' ] ] );
			$this->assertFalse( tribe( Query_Modifier::class )->applies_to( $query ) );

			return $args;
		};

		// Arrange
		add_filter( 'acf/fields/post_object/query', $assert, ACF_Controller::AJAX_QUERY_PRIORITY + 10 );

		// Act
		$filtered = apply_filters( 'acf/fields/post_object/query', [], [
			'type' => $field_type,
		] );

		// Assert more.
		$this->assertEquals( [], $filtered );
	}
}
