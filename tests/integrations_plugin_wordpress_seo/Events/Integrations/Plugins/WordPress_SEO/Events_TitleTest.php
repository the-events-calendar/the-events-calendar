<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Closure;
use Codeception\TestCase\WPTestCase;
use Generator;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC_Plugin;
use WP_Term;
use WPSEO_Taxonomy_Meta;

/**
 * Class Events_TitleTest
 *
 * Tests the Events_Title class which ensures Yoast SEO custom titles
 * for Event Categories and Tags are used in the document <title> tag.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_TitleTest extends WPTestCase {
	use MatchesSnapshots;
	use With_Uopz;

	/**
	 * The system under test.
	 *
	 * @since TBD
	 *
	 * @var Events_Title
	 */
	protected $sut;

	/**
	 * Term IDs created during tests, tracked for cleanup.
	 *
	 * @since TBD
	 *
	 * @var array<int>
	 */
	protected $created_term_ids = [];

	/**
	 * @before
	 */
	public function set_up_test(): void {
		$this->sut              = new Events_Title();
		$this->created_term_ids = [];

		// Register the hooks.
		$this->sut->register();

		// Also register the custom variables so %%event_start_date%% etc. work.
		$variables = new Events_Variables();
		$variables->register();

		// Use a fixed date format for predictable output.
		add_filter( 'tribe_date_format', function () {
			return 'Y-m-d';
		} );
	}

	/**
	 * @after
	 */
	public function tear_down_test(): void {
		remove_all_filters( 'tribe_date_format' );
		remove_all_filters( 'pre_get_document_title' );
		remove_all_filters( 'tribe_events_views_v2_category_title' );
		wp_reset_postdata();

		foreach ( $this->created_term_ids as $term_id ) {
			wp_delete_term( $term_id, TEC_Plugin::TAXONOMY );
		}
	}

	/**
	 * Create a tracked Event Category term.
	 *
	 * @since TBD
	 *
	 * @param string $name The term name.
	 *
	 * @return WP_Term The created term.
	 */
	protected function create_event_category( string $name ): WP_Term {
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => TEC_Plugin::TAXONOMY,
			'name'     => $name,
		] );

		$this->created_term_ids[] = $term->term_id;

		return $term;
	}

	/**
	 * Create a tracked tag term.
	 *
	 * @since TBD
	 *
	 * @param string $name The tag name.
	 *
	 * @return WP_Term The created tag.
	 */
	protected function create_event_tag( string $name ): WP_Term {
		$tag = $this->factory()->tag->create_and_get( [
			'name' => $name,
		] );

		$this->created_term_ids[] = $tag->term_id;

		return $tag;
	}

	/**
	 * Create a test event and set it as the global post.
	 *
	 * @since TBD
	 *
	 * @return int The event post ID.
	 */
	protected function create_test_event(): int {
		$venue_id = $this->factory()->post->create( [
			'post_type'  => 'tribe_venue',
			'post_title' => 'Brakus Hall',
			'meta_input' => [
				'_VenueCity'  => 'Denver',
				'_VenueState' => 'CO',
			],
		] );

		$organizer_id = $this->factory()->post->create( [
			'post_type'  => 'tribe_organizer',
			'post_title' => 'Test Organizer',
		] );

		$event_id = $this->factory()->post->create( [
			'post_type'  => 'tribe_events',
			'meta_input' => [
				'_EventStartDate'   => '2035-04-12 10:00:00',
				'_EventEndDate'     => '2035-04-12 18:00:00',
				'_EventVenueID'     => $venue_id,
				'_EventOrganizerID' => $organizer_id,
			],
		] );

		global $post;
		$post = get_post( $event_id );
		setup_postdata( $post );

		return $event_id;
	}

	/**
	 * Set Yoast term meta title for a term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $term  The term.
	 * @param string  $title The custom SEO title.
	 *
	 * @return void
	 */
	protected function set_yoast_title( WP_Term $term, string $title ): void {
		WPSEO_Taxonomy_Meta::set_values( $term->term_id, $term->taxonomy, [
			'wpseo_title' => $title,
		] );
	}

	/**
	 * Mock the frontend context for an Event Category term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $term The term to simulate.
	 *
	 * @return void
	 */
	protected function mock_event_category_frontend( WP_Term $term ): void {
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', function ( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === TEC_Plugin::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'is_tag', false );
		$this->set_fn_return( 'get_queried_object', $term );
	}

	/**
	 * Mock the frontend context for an Event Tag term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $tag The tag to simulate.
	 *
	 * @return void
	 */
	protected function mock_event_tag_frontend( WP_Term $tag ): void {
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', false );
		$this->set_fn_return( 'is_tag', true );
		$this->set_fn_return( 'tribe_is_event_query', true );
		$this->set_fn_return( 'get_queried_object', $tag );
	}

	// ──────────────────────────────────────────────────────────
	// 1. Data Provider: pre_get_document_title scenarios.
	// ──────────────────────────────────────────────────────────

	/**
	 * Data provider for pre_get_document_title tests.
	 *
	 * Uses Closures to defer fixture creation until test runtime.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function pre_get_document_title_provider(): Generator {
		yield 'event category with plain text title' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Music Events' );
				$this->set_yoast_title( $term, 'My Custom Category Title' );
				$this->mock_event_category_frontend( $term );

				return [
					'input_title'    => '',
					'expected_title' => 'My Custom Category Title',
				];
			},
		];

		yield 'event category with variable-based title' => [
			'fixture' => function (): array {
				$this->create_test_event();
				$term = $this->create_event_category( 'Category 89' );
				$this->set_yoast_title( $term, 'Upcoming Events starting %%event_start_date%% at %%venue_title%%' );
				$this->mock_event_category_frontend( $term );

				return [
					'input_title'    => '',
					'expected_title' => 'Upcoming Events starting 2035-04-12 at Brakus Hall',
				];
			},
		];

		yield 'event category with no custom title falls back to default' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Default Category' );
				// No Yoast title set.
				$this->mock_event_category_frontend( $term );

				return [
					'input_title'    => '',
					'expected_title' => '',
				];
			},
		];

		yield 'event tag with plain text title' => [
			'fixture' => function (): array {
				$tag = $this->create_event_tag( 'Jazz Events' );
				$this->set_yoast_title( $tag, 'Custom Jazz SEO Title' );
				$this->mock_event_tag_frontend( $tag );

				return [
					'input_title'    => '',
					'expected_title' => 'Custom Jazz SEO Title',
				];
			},
		];

		yield 'admin context is skipped' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Admin Category' );
				$this->set_yoast_title( $term, 'Should Not Appear' );
				$this->set_fn_return( 'is_admin', true );
				$this->set_fn_return( 'get_queried_object', $term );

				return [
					'input_title'    => 'Original Admin Title',
					'expected_title' => 'Original Admin Title',
				];
			},
		];

		yield 'non-event taxonomy is skipped' => [
			'fixture' => function (): array {
				$category = $this->factory()->category->create_and_get( [
					'name' => 'Regular Category',
				] );
				$this->set_fn_return( 'is_admin', false );
				$this->set_fn_return( 'is_tax', false );
				$this->set_fn_return( 'is_tag', false );
				$this->set_fn_return( 'get_queried_object', $category );

				return [
					'input_title'    => 'Original Title',
					'expected_title' => 'Original Title',
				];
			},
		];

		yield 'non-term queried object is skipped' => [
			'fixture' => function (): array {
				$post = $this->factory()->post->create_and_get();
				$this->set_fn_return( 'is_admin', false );
				$this->set_fn_return( 'get_queried_object', $post );

				return [
					'input_title'    => 'Post Title',
					'expected_title' => 'Post Title',
				];
			},
		];
	}

	/**
	 * @test
	 * @dataProvider pre_get_document_title_provider
	 */
	public function it_handles_pre_get_document_title_correctly( Closure $fixture ): void {
		$fixture = $fixture->bindTo( $this, self::class );
		$data    = $fixture();

		$result = $this->sut->pre_get_document_title( $data['input_title'] );

		$this->assertEquals( $data['expected_title'], $result );
	}

	// ──────────────────────────────────────────────────────────
	// 2. Data Provider: filter_category_title scenarios.
	// ──────────────────────────────────────────────────────────

	/**
	 * Data provider for filter_category_title tests.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function filter_category_title_provider(): Generator {
		yield 'replaces title with plain text Yoast title' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Concerts' );
				$this->set_yoast_title( $term, 'Best Concerts in Town' );

				return [
					'term'           => $term,
					'tec_title'      => 'Events from Jan 1 - Dec 31 › Concerts ›',
					'expected_title' => 'Best Concerts in Town',
				];
			},
		];

		yield 'replaces title with variable-based Yoast title' => [
			'fixture' => function (): array {
				$this->create_test_event();
				$term = $this->create_event_category( 'Festivals' );
				$this->set_yoast_title( $term, 'Events on %%event_start_date%%' );

				return [
					'term'           => $term,
					'tec_title'      => 'Events from Jan 1 - Dec 31 › Festivals ›',
					'expected_title' => 'Events on 2035-04-12',
				];
			},
		];

		yield 'falls back to TEC title when no Yoast title is set' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Workshops' );
				// No Yoast title set.

				return [
					'term'           => $term,
					'tec_title'      => 'Events from Jan 1 - Dec 31 › Workshops ›',
					'expected_title' => 'Events from Jan 1 - Dec 31 › Workshops ›',
				];
			},
		];

		yield 'handles HTML in custom title' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Special Events' );
				$this->set_yoast_title( $term, '<b>Bold</b> Category Title' );

				return [
					'term'           => $term,
					'tec_title'      => 'Original Title',
					'expected_title' => 'Bold Category Title',
				];
			},
		];
	}

	/**
	 * @test
	 * @dataProvider filter_category_title_provider
	 */
	public function it_handles_filter_category_title_correctly( Closure $fixture ): void {
		$fixture = $fixture->bindTo( $this, self::class );
		$data    = $fixture();

		$result = $this->sut->filter_category_title(
			$data['tec_title'],
			$data['tec_title'],
			$data['term'],
			true,
			' › '
		);

		$this->assertEquals( $data['expected_title'], $result );
	}

	// ──────────────────────────────────────────────────────────
	// 3. Snapshot tests: title and og:title match.
	// ──────────────────────────────────────────────────────────

	/**
	 * Data provider for snapshot tests that verify title and og:title match.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function title_and_og_title_snapshot_provider(): Generator {
		yield 'event category with plain text custom title' => [
			'fixture' => function (): array {
				$term = $this->create_event_category( 'Music Events' );
				$this->set_yoast_title( $term, 'My Custom Category Title' );
				$this->mock_event_category_frontend( $term );

				return [ 'term' => $term ];
			},
		];

		yield 'event category with variable-based custom title' => [
			'fixture' => function (): array {
				$this->create_test_event();
				$term = $this->create_event_category( 'Category 89' );
				$this->set_yoast_title( $term, 'Upcoming Events %%event_start_date%% at %%venue_title%%' );
				$this->mock_event_category_frontend( $term );

				return [ 'term' => $term ];
			},
		];

		yield 'event tag with plain text custom title' => [
			'fixture' => function (): array {
				$tag = $this->create_event_tag( 'Jazz Events' );
				$this->set_yoast_title( $tag, 'Custom Jazz Events SEO Title' );
				$this->mock_event_tag_frontend( $tag );

				return [ 'term' => $tag ];
			},
		];
	}

	/**
	 * @test
	 * @dataProvider title_and_og_title_snapshot_provider
	 */
	public function it_produces_matching_title_and_og_title_tags( Closure $fixture ): void {
		$fixture = $fixture->bindTo( $this, self::class );
		$data    = $fixture();
		$term    = $data['term'];

		// Get the title via pre_get_document_title (same path the <title> tag uses).
		$document_title = $this->sut->pre_get_document_title( '' );

		// Get the title via the Yoast variable replacement (same path og:title uses).
		$title_template = WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, 'title' );
		$replace_vars   = new \WPSEO_Replace_Vars();
		$og_title       = wp_strip_all_tags( trim( $replace_vars->replace( $title_template, $term ) ) );

		// Both must match.
		$this->assertNotEmpty( $document_title, 'Document title should not be empty.' );
		$this->assertNotEmpty( $og_title, 'OG title should not be empty.' );
		$this->assertEquals( $document_title, $og_title, 'Document <title> and og:title must match.' );

		// Build the HTML output for snapshot comparison.
		$title_html    = '<title>' . esc_html( $document_title ) . '</title>';
		$og_title_html = '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />';
		$output        = $title_html . "\n" . $og_title_html;

		$this->assertMatchesSnapshot( $output );
	}

	// ──────────────────────────────────────────────────────────
	// 4. Hook registration tests.
	// ──────────────────────────────────────────────────────────

	/**
	 * @test
	 */
	public function it_registers_pre_get_document_title_filter_at_priority_25(): void {
		global $wp_filter;

		$this->assertArrayHasKey( 'pre_get_document_title', $wp_filter );

		$found = false;
		foreach ( $wp_filter['pre_get_document_title']->callbacks[25] ?? [] as $callback ) {
			if (
				is_array( $callback['function'] )
				&& $callback['function'][0] instanceof Events_Title
				&& $callback['function'][1] === 'pre_get_document_title'
			) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, 'pre_get_document_title should be registered at priority 25.' );
	}

	/**
	 * @test
	 */
	public function it_registers_tribe_events_views_v2_category_title_filter(): void {
		global $wp_filter;

		$this->assertArrayHasKey( 'tribe_events_views_v2_category_title', $wp_filter );
	}

	// ──────────────────────────────────────────────────────────
	// 5. Data Provider: variable replacement in titles.
	// ──────────────────────────────────────────────────────────

	/**
	 * Data provider testing that each custom Yoast variable resolves correctly
	 * when used inside a title template on an Event Category archive page.
	 *
	 * Each scenario yields a Closure that creates the necessary fixtures
	 * (event, venue, organizer) and returns the title template + expected output.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function variable_replacement_provider(): Generator {
		yield '1. plain text title — no variables at all' => [
			'fixture' => function (): array {
				return [
					'title_template' => 'All Upcoming Events',
					'expected'       => 'All Upcoming Events',
				];
			},
		];

		yield '2. single variable — %%event_start_date%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Events starting %%event_start_date%%',
					'expected'       => 'Events starting 2035-04-12',
				];
			},
		];

		yield '3. single variable — %%event_end_date%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Events ending %%event_end_date%%',
					'expected'       => 'Events ending 2035-04-12',
				];
			},
		];

		yield '4. single variable — %%venue_title%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Events at %%venue_title%%',
					'expected'       => 'Events at Brakus Hall',
				];
			},
		];

		yield '5. single variable — %%venue_city%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Events in %%venue_city%%',
					'expected'       => 'Events in Denver',
				];
			},
		];

		yield '6. single variable — %%venue_state%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Events in %%venue_state%%',
					'expected'       => 'Events in CO',
				];
			},
		];

		yield '7. single variable — %%organizer_title%%' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => 'Hosted by %%organizer_title%%',
					'expected'       => 'Hosted by Test Organizer',
				];
			},
		];

		yield '8. multiple variables combined' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => '%%event_start_date%% at %%venue_title%%, %%venue_city%% %%venue_state%%',
					'expected'       => '2035-04-12 at Brakus Hall, Denver CO',
				];
			},
		];

		yield '9. all custom variables in one title' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => '%%event_start_date%% - %%event_end_date%% | %%venue_title%% %%venue_city%% %%venue_state%% | %%organizer_title%%',
					'expected'       => '2035-04-12 - 2035-04-12 | Brakus Hall Denver CO | Test Organizer',
				];
			},
		];

		yield '10. variables mixed with static text and special characters' => [
			'fixture' => function (): array {
				$this->create_test_event();

				return [
					'title_template' => '🎵 Live at %%venue_title%% — %%venue_city%%, %%venue_state%% (%%event_start_date%%)',
					'expected'       => '🎵 Live at Brakus Hall — Denver, CO (2035-04-12)',
				];
			},
		];
	}

	/**
	 * @test
	 * @dataProvider variable_replacement_provider
	 */
	public function it_replaces_variables_in_title_correctly( Closure $fixture ): void {
		$fixture = $fixture->bindTo( $this, self::class );
		$data    = $fixture();

		$term = $this->create_event_category( 'Variable Test Category' );
		$this->set_yoast_title( $term, $data['title_template'] );
		$this->mock_event_category_frontend( $term );

		$result = $this->sut->pre_get_document_title( '' );

		$this->assertEquals( $data['expected'], $result );
	}
}
