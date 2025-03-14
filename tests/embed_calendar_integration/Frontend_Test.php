<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use Tribe\Tests\Traits\With_Uopz;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Traits\ECE_Maker;
use Tribe\Tests\Traits\With_Clock_Mock;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Generator;
use Closure;

class Frontend_Test extends Controller_Test_Case {

	use With_Uopz;
	use ECE_Maker;
	use SnapshotAssertions;
	use With_Clock_Mock;

	protected string $controller_class = Frontend::class;

	protected static array $back_up = [];

	/**
	 * @before
	 */
	public function set_up(): void {
		global $post;

		self::$back_up['post'] = $post;

		$this->set_fn_return( 'is_admin', false );
	}

	/**
	 * @after
	 */
	public function restore(): void {
		global $post;

		$post = self::$back_up['post'];
	}

	/**
	 * @test
	 */
	public function it_should_enqueue_asset_group(): void {
		$controller = $this->make_controller();

		$store = [];

		$this->set_fn_return( 'tribe_asset_enqueue_group', function ( $group ) use ( &$store ) {
			$store[] = $group;
			return true;
		}, true );

		$controller->enqueue_scripts();

		$this->assertEmpty( $store );

		$this->set_fn_return( 'is_singular', true );

		$controller->enqueue_scripts();

		$this->assertEquals( [ 'events-views-v2' ], $store );
	}

	/**
	 * @test
	 */
	public function it_should_use_custom_template_for_embeds(): void {
		$this->make_controller()->register();

		$this->set_fn_return( 'is_embed', false );
		$this->set_fn_return( 'is_singular', false );

		$template = apply_filters( 'embed_template', 'foo' );

		$this->assertEquals( 'foo', $template );

		$this->set_fn_return( 'is_embed', true );

		$template = apply_filters( 'embed_template', 'foo' );

		$this->assertEquals( 'foo', $template );

		$this->set_fn_return( 'is_singular', true );

		$template = apply_filters( 'embed_template', 'foo' );

		$this->assertStringContainsString( 'plugins/the-events-calendar/src/views/calendar-embeds/embed.php', $template );
	}

	public function ece_data_provider(): Generator {
		yield 'no tags - no cats' => [
			function () {
				$event_ids = [];
				for ( $i = 1; $i < 10; $i++ ) {
					$args       = [
						'start_date'  => '2025-03-0' . $i . ' 09:00:00',
						'end_date'    => '2025-03-0' . $i . ' 11:00:00',
						'timezone'    => 'Europe/Paris',
						'title'       => 'A test event ' . $i,
						'post_status' => 'publish',
					];
					$event_ids[] = tribe_events()->set_args( $args )->create()->ID;
				}

				return [ $event_ids, [], [] ];
			}
		];

		yield 'tags - no cats' => [
			function () {
				$term_ids = [];
				$term_ids[] = self::factory()->tag->create( [ 'slug' => 'tag1' ] );
				$term_ids[] = self::factory()->tag->create( [ 'slug' => 'tag2' ] );
				$event_ids  = [];
				for ( $i = 1; $i < 10; $i++ ) {
					$args       = [
						'start_date' => '2025-03-0' . $i . ' 09:00:00',
						'end_date'   => '2025-03-0' . $i . ' 11:00:00',
						'timezone'   => 'Europe/Paris',
						'title'      => 'A test event ' . $i,
						'post_status' => 'publish',
					];
					$id = tribe_events()->set_args( $args )->create()->ID;
					$event_ids[] = $id;

					if ( $i === 3 || $i === 7 ) {
						continue;
					}
					wp_set_post_terms( $id, $term_ids, 'post_tag' );
				}

				return [ $event_ids, [ 'tag1', 'tag2' ], [] ];
			}
		];

		yield 'no tags - cats' => [
			function () {
				$term_ids = [];
				$term_ids[] = self::factory()->term->create( [ 'slug' => 'cat1', 'taxonomy' => TEC::TAXONOMY ] );
				$term_ids[] = self::factory()->term->create( [ 'slug' => 'cat2', 'taxonomy' => TEC::TAXONOMY ] );
				$event_ids  = [];
				for ( $i = 1; $i < 10; $i++ ) {
					$args       = [
						'start_date' => '2025-03-0' . $i . ' 09:00:00',
						'end_date'   => '2025-03-0' . $i . ' 11:00:00',
						'timezone'   => 'Europe/Paris',
						'title'      => 'A test event ' . $i,
						'post_status' => 'publish',
					];
					$id = tribe_events()->set_args( $args )->create()->ID;
					$event_ids[] = $id;

					if ( $i === 2 || $i === 5 ) {
						continue;
					}
					wp_set_post_terms( $id, $term_ids );
				}

				return [ $event_ids, [], [ 'cat1', 'cat2' ] ];
			}
		];

		yield 'tags and cats' => [
			function () {
				$term_ids = [];
				$term_ids[] = self::factory()->term->create( [ 'slug' => 'cat1', 'taxonomy' => TEC::TAXONOMY ] );
				$term_ids[] = self::factory()->term->create( [ 'slug' => 'cat2', 'taxonomy' => TEC::TAXONOMY ] );
				$tag_ids = [];
				$tag_ids[] = self::factory()->tag->create( [ 'slug' => 'tag1' ] );
				$tag_ids[] = self::factory()->tag->create( [ 'slug' => 'tag2' ] );
				$event_ids = [];
				for ( $i = 1; $i < 10; $i++ ) {
					$args       = [
						'start_date' => '2025-03-0' . $i . ' 09:00:00',
						'end_date'   => '2025-03-0' . $i . ' 11:00:00',
						'timezone'   => 'Europe/Paris',
						'title'      => 'A test event ' . $i,
						'post_status' => 'publish',
					];
					$id = tribe_events()->set_args( $args )->create()->ID;
					$event_ids[] = $id;

					if ( $i === 2 || $i === 3 || $i === 7 ) {
						wp_set_post_terms( $id, $tag_ids, 'post_tag' );
					}

					if ( $i === 2 || $i === 5 ) {
						continue;
					}
					wp_set_post_terms( $id, $term_ids );
				}

				return [ $event_ids, [ 'tag1', 'tag2' ], [ 'cat1', 'cat2' ] ];
			}
		];
	}

	/**
	 * @test
	 * @dataProvider ece_data_provider
	 */
	public function it_should_overwrite_content( Closure $fixture ): void {
		$date = date( 'Y-m-d' );
		$this->freeze_time( Dates::immutable( '2025-03-03 10:00:00' ) );
		remove_all_filters( 'the_content' );
		$controller = $this->make_controller();
		$controller->register();

		[ $event_ids, $tags, $cats ] = $fixture();

		$ece_id = $this->create_ece( [ 'post_content' => '' ] );
		if ( ! empty( $tags ) ) {
			$this->add_tags_to_ece( $ece_id, $tags );
		}

		if ( ! empty( $cats ) ) {
			$this->add_categories_to_ece( $ece_id, $cats );
		}

		$ece = get_post( $ece_id );

		$this->assertEquals( '', $ece->post_content );
		$this->assertEquals( '', get_the_content( null, false, $ece_id ) );

		wp_update_post( [ 'ID' => $ece_id, 'post_content' => 'foo' ] );

		$ece = get_post( $ece_id );

		$this->assertEquals( 'foo', $ece->post_content );
		$this->assertEquals( 'foo', get_the_content( null, false, $ece_id ) );

		global $post;
		$post = $ece;

		$this->set_fn_return( 'is_singular', fn( $ptype = null ) => $ptype === Calendar_Embeds::POSTTYPE, true );
		$this->set_fn_return( 'wp_create_nonce', 'hdas64538ahda' );
		add_filter( 'tribe_events_views_v2_view_breakpoint_pointer', fn() => 'breakpoint-pointer' );

		$filtered = apply_filters( 'the_content', $ece->post_content );

		$this->assertNotEquals( $ece->post_content, $filtered );

		$filtered = preg_replace( '/"now":"[^"]*"/', '"now":"' . date( 'Y-m-d H:i:s' ) . '"', $filtered );
		$filtered = str_replace( (string) $ece_id, '{ECE_ID}', $filtered );
		$filtered = str_replace( $event_ids, '{EVENT_ID}', $filtered );
		$filtered = str_replace( $date, date( 'Y-m-d' ), $filtered );

		$this->assertMatchesHtmlSnapshot( $filtered );
	}
}
