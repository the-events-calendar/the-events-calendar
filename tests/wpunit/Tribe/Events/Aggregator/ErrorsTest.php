<?php

namespace Tribe\Events\Aggregator;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Aggregator__Errors as Errors;

/**
 * Event Aggregator error comments stay out of comment queries unless the query asks for their type.
 */
class ErrorsTest extends WPTestCase {
	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var int
	 */
	private $comment_id;

	/**
	 * @var int
	 */
	private $ea_error_id;

	/**
	 * @before
	 */
	public function create_comments(): void {
		// A license key makes the Event Aggregator service count as active, which the exclusion requires.
		add_filter(
			'tribe_aggregator_api',
			static function ( $api ) {
				$api->key = 'test-license-key';

				return $api;
			}
		);

		$this->post_id     = static::factory()->post->create();
		$this->comment_id  = static::factory()->comment->create(
			[
				'comment_post_ID' => $this->post_id,
				'comment_type'    => 'comment',
			]
		);
		$this->ea_error_id = static::factory()->comment->create(
			[
				'comment_post_ID' => $this->post_id,
				'comment_type'    => Errors::$comment_type,
			]
		);
	}

	/**
	 * @test
	 */
	public function it_should_exclude_ea_errors_when_type_is_an_array_without_the_ea_type(): void {
		$ids = get_comments(
			[
				'post_id' => $this->post_id,
				'type'    => [ 'all' ],
				'fields'  => 'ids',
			]
		);

		$this->assertSame( [ $this->comment_id ], array_map( 'absint', $ids ) );
	}

	/**
	 * @test
	 */
	public function it_should_include_ea_errors_when_type_is_an_array_with_the_ea_type(): void {
		$ids = get_comments(
			[
				'post_id' => $this->post_id,
				'type'    => [ 'comment', Errors::$comment_type ],
				'fields'  => 'ids',
				'orderby' => 'comment_ID',
				'order'   => 'ASC',
			]
		);

		$this->assertSame( [ $this->comment_id, $this->ea_error_id ], array_map( 'absint', $ids ) );
	}

	/**
	 * @test
	 */
	public function it_should_exclude_ea_errors_when_type_is_a_string_without_the_ea_type(): void {
		$ids = get_comments(
			[
				'post_id' => $this->post_id,
				'type'    => 'all',
				'fields'  => 'ids',
			]
		);

		$this->assertSame( [ $this->comment_id ], array_map( 'absint', $ids ) );
	}

	/**
	 * @test
	 */
	public function it_should_include_ea_errors_when_type_is_the_ea_type_string(): void {
		$ids = get_comments(
			[
				'post_id' => $this->post_id,
				'type'    => Errors::$comment_type,
				'fields'  => 'ids',
			]
		);

		$this->assertSame( [ $this->ea_error_id ], array_map( 'absint', $ids ) );
	}
}
