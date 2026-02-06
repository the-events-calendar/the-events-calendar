<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Class Events_VariablesTest
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_VariablesTest extends WPTestCase {
	use MatchesSnapshots;
	use With_Uopz;

	/**
	 * @var \TEC\Events\Integrations\Plugins\WordPress_SEO\Events_Variables
	 */
	protected $sut;

	/**
	 * Set up the test case.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->sut = new Events_Variables();
		$this->sut->register();

    add_filter( 'tribe_date_format', function() { return 'Y-m-d'; } );
	}

	/**
	 * Clean up after the test case.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_all_filters( 'tribe_date_format' );
		wp_reset_postdata();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_registers_custom_yoast_variables() {
		global $wp_filter;
		$this->assertArrayHasKey( 'wpseo_register_extra_replacements', $wp_filter );
	}

	/**
	 * Creates a test event with all needed meta/properties and sets up global postdata.
	 *
	 * @since 6.14.0
	 *
	 * @return int Event post ID.
	 */
	protected function get_test_event() {
		$venue_id = $this->factory()->post->create([
			'post_type'  => 'tribe_venue',
			'post_title' => 'Test Venue',
			'meta_input' => [
				'_VenueCity'  => 'Test City',
				'_VenueState' => 'CA',
			],
		]);

		$organizer_id = $this->factory()->post->create([
			'post_type'  => 'tribe_organizer',
			'post_title' => 'Test Organizer',
		]);

		$event_id = $this->factory()->post->create([
			'post_type'  => 'tribe_events',
			'meta_input' => [
				'_EventStartDate'    => '2035-08-02 21:30:00',
				'_EventEndDate'      => '2035-08-03 23:30:00',
				'_EventVenueID'      => $venue_id,
				'_EventOrganizerID'  => $organizer_id,
			],
		]);

		global $post;
		$post = get_post($event_id);
		setup_postdata($post);

		return $event_id;
	}

	/**
	 * @test
	 */
	public function it_replaces_event_start_date_variable() {
		$result = $this->sut->get_event_start_date( $this->get_test_event() );
		$this->assertStringContainsString( '2035-08-02', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_event_end_date_variable() {
		$result = $this->sut->get_event_end_date( $this->get_test_event() );
		$this->assertStringContainsString( '2035-08-03', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_title_variable() {
		$result = $this->sut->get_venue_title( $this->get_test_event() );
		$this->assertEquals( 'Test Venue', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_city_variable() {
		$result = $this->sut->get_venue_city( $this->get_test_event() );
		$this->assertEquals( 'Test City', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_state_variable() {
		$result = $this->sut->get_venue_state( $this->get_test_event() );
		$this->assertEquals( 'CA', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_organizer_title_variable() {
		$result = $this->sut->get_organizer_title( $this->get_test_event() );
		$this->assertEquals( 'Test Organizer', $result );
	}

	/**
	 * @test
	 */
	public function it_registers_wpseo_replacements_filter() {
		global $wp_filter;
		$this->assertArrayHasKey( 'wpseo_replacements', $wp_filter );
	}

	/**
	 * @test
	 */
	public function it_populates_term_data_for_event_category_archive() {
		// Create an Event Category term.
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => \Tribe__Events__Main::TAXONOMY,
			'name'     => 'Test Category',
		] );

		// Mock WordPress functions to simulate being on an Event Category archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', function( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === \Tribe__Events__Main::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated.
		$this->assertEquals( 'Test Category', $args->name );
		$this->assertEquals( $term->term_id, $args->term_id );
		$this->assertEquals( \Tribe__Events__Main::TAXONOMY, $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_populates_term_data_for_event_tag_archive() {
		// Create an Event Tag term.
		$tag = $this->factory()->tag->create_and_get( [
			'name' => 'Test Tag',
		] );

		// Mock WordPress functions to simulate being on an Event Tag archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tag', true );
		$this->set_fn_return( 'tribe_is_event_query', true );
		$this->set_fn_return( 'get_queried_object', $tag );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated.
		$this->assertEquals( 'Test Tag', $args->name );
		$this->assertEquals( $tag->term_id, $args->term_id );
		$this->assertEquals( 'post_tag', $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_does_not_populate_term_data_in_admin_context() {
		// Create an Event Category term.
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => \Tribe__Events__Main::TAXONOMY,
			'name'     => 'Test Category',
		] );

		// Mock WordPress functions to simulate being in admin.
		$this->set_fn_return( 'is_admin', true );
		$this->set_fn_return( 'is_tax', function( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === \Tribe__Events__Main::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was NOT populated.
		$this->assertEquals( '', $args->name );
		$this->assertEquals( '', $args->term_id );
		$this->assertEquals( '', $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_does_not_populate_term_data_for_non_event_taxonomy() {
		// Create a regular category term (not an Event Category).
		$term = $this->factory()->category->create_and_get( [
			'name' => 'Regular Category',
		] );

		// Mock WordPress functions to simulate being on a regular category archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', false );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was NOT populated.
		$this->assertEquals( '', $args->name );
		$this->assertEquals( '', $args->term_id );
		$this->assertEquals( '', $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_does_not_populate_term_data_for_regular_post_tag() {
		// Create a regular post tag (not on an event query).
		$tag = $this->factory()->tag->create_and_get( [
			'name' => 'Regular Tag',
		] );

		// Mock WordPress functions to simulate being on a regular tag archive page (not event query).
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tag', true );
		$this->set_fn_return( 'tribe_is_event_query', false );
		$this->set_fn_return( 'get_queried_object', $tag );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was NOT populated.
		$this->assertEquals( '', $args->name );
		$this->assertEquals( '', $args->term_id );
		$this->assertEquals( '', $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_handles_non_term_queried_object_gracefully() {
		// Create a post object (not a term).
		$post = $this->factory()->post->create_and_get();

		// Mock WordPress functions.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', false );
		$this->set_fn_return( 'is_tag', false );
		$this->set_fn_return( 'get_queried_object', $post );

		// Create a mock args object.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [ '%%term_title%%' => '' ];

		// Call the method.
		$result = $this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was NOT populated.
		$this->assertEquals( '', $args->name );
		$this->assertEquals( '', $args->term_id );
		$this->assertEquals( '', $args->taxonomy );
		$this->assertEquals( $replacements, $result );
	}

	/**
	 * @test
	 */
	public function it_allows_custom_seo_title_to_be_used_for_event_category() {
		// Create an Event Category term.
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => \Tribe__Events__Main::TAXONOMY,
			'name'     => 'Test Category',
		] );

		// Set a custom SEO title in Yoast's term meta.
		$custom_title = 'Custom SEO Title for Event Category';
		\WPSEO_Taxonomy_Meta::set_values( $term->term_id, $term->taxonomy, [
			'wpseo_title' => $custom_title,
		] );

		// Mock WordPress functions to simulate being on an Event Category archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', function( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === \Tribe__Events__Main::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object that would be passed to wpseo_replace_vars.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [];

		// Call the method to populate term data.
		$this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated with term data.
		$this->assertEquals( 'Test Category', $args->name );
		$this->assertEquals( $term->term_id, $args->term_id );
		$this->assertEquals( \Tribe__Events__Main::TAXONOMY, $args->taxonomy );

		// Now test that Yoast can use this data to replace variables in a title template.
		$title_template = '%%term_title%% %%sep%% %%sitename%%';
		$replaced_title = wpseo_replace_vars( $title_template, $args );

		// Verify that %%term_title%% was replaced with the actual term name.
		$this->assertStringContainsString( 'Test Category', $replaced_title );
		$this->assertStringNotContainsString( '%%term_title%%', $replaced_title );
	}

	/**
	 * @test
	 */
	public function it_allows_custom_seo_title_to_be_used_for_event_tag() {
		// Create an Event Tag term.
		$tag = $this->factory()->tag->create_and_get( [
			'name' => 'Test Tag',
		] );

		// Set a custom SEO title in Yoast's term meta.
		$custom_title = 'Custom SEO Title for Event Tag';
		\WPSEO_Taxonomy_Meta::set_values( $tag->term_id, $tag->taxonomy, [
			'wpseo_title' => $custom_title,
		] );

		// Mock WordPress functions to simulate being on an Event Tag archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tag', true );
		$this->set_fn_return( 'tribe_is_event_query', true );
		$this->set_fn_return( 'get_queried_object', $tag );

		// Create a mock args object that would be passed to wpseo_replace_vars.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [];

		// Call the method to populate term data.
		$this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated with term data.
		$this->assertEquals( 'Test Tag', $args->name );
		$this->assertEquals( $tag->term_id, $args->term_id );
		$this->assertEquals( 'post_tag', $args->taxonomy );

		// Now test that Yoast can use this data to replace variables in a title template.
		$title_template = '%%term_title%% %%sep%% %%sitename%%';
		$replaced_title = wpseo_replace_vars( $title_template, $args );

		// Verify that %%term_title%% was replaced with the actual term name.
		$this->assertStringContainsString( 'Test Tag', $replaced_title );
		$this->assertStringNotContainsString( '%%term_title%%', $replaced_title );
	}

	/**
	 * @test
	 */
	public function it_ensures_term_title_replacement_works_for_both_title_and_og_title() {
		// Create an Event Category term.
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => \Tribe__Events__Main::TAXONOMY,
			'name'     => 'Concert Events',
		] );

		// Set a custom SEO title in Yoast's term meta.
		$custom_title = 'Custom Concert Category Title';
		\WPSEO_Taxonomy_Meta::set_values( $term->term_id, $term->taxonomy, [
			'wpseo_title' => $custom_title,
		] );

		// Mock WordPress functions to simulate being on an Event Category archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', function( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === \Tribe__Events__Main::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object that would be passed to wpseo_replace_vars.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [];

		// Call the method to populate term data.
		$this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated.
		$this->assertEquals( 'Concert Events', $args->name );
		$this->assertEquals( $term->term_id, $args->term_id );

		// Test title template replacement (simulates what Yoast does for <title> tag).
		$title_template = '%%term_title%% %%sep%% %%sitename%%';
		$replaced_title = wpseo_replace_vars( $title_template, $args );
		$this->assertStringContainsString( 'Concert Events', $replaced_title );
		$this->assertStringNotContainsString( '%%term_title%%', $replaced_title );

		// Test og:title template replacement (simulates what Yoast does for og:title meta tag).
		$og_title_template = '%%term_title%%';
		$replaced_og_title = wpseo_replace_vars( $og_title_template, $args );
		$this->assertEquals( 'Concert Events', $replaced_og_title );
		$this->assertStringNotContainsString( '%%term_title%%', $replaced_og_title );

		// Both should work because the term data is now properly populated.
		$this->assertNotEquals( '', $replaced_title );
		$this->assertNotEquals( '', $replaced_og_title );
	}

	/**
	 * @test
	 */
	public function it_outputs_correct_title_and_og_title_for_event_category_with_custom_seo_title() {
		// Create an Event Category term.
		$term = $this->factory()->term->create_and_get( [
			'taxonomy' => \Tribe__Events__Main::TAXONOMY,
			'name'     => 'Music Events',
		] );

		// Set a custom SEO title in Yoast's term meta.
		$custom_title = 'Custom Music Events SEO Title';
		\WPSEO_Taxonomy_Meta::set_values( $term->term_id, $term->taxonomy, [
			'wpseo_title' => $custom_title,
		] );

		// Mock WordPress functions to simulate being on an Event Category archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tax', function( $taxonomy = null ) use ( $term ) {
			if ( $taxonomy === null || $taxonomy === \Tribe__Events__Main::TAXONOMY ) {
				return true;
			}
			return false;
		} );
		$this->set_fn_return( 'get_queried_object', $term );

		// Create a mock args object that would be passed to wpseo_replace_vars.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [];

		// Call the method to populate term data.
		$this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated.
		$this->assertEquals( 'Music Events', $args->name );
		$this->assertEquals( $term->term_id, $args->term_id );

		// Simulate what Yoast does: replace variables in the custom title template.
		$replaced_title = wpseo_replace_vars( $custom_title, $args );

		// Format as HTML tags (simulating what Yoast's presenters do).
		$title_html = '<title>' . esc_html( $replaced_title ) . '</title>';
		$og_title_html = '<meta property="og:title" content="' . esc_attr( $replaced_title ) . '" />';

		// Combine both outputs for snapshot testing.
		$output = $title_html . "\n" . $og_title_html;

		// Verify both tags are present and contain the custom title.
		$this->assertStringContainsString( '<title>', $title_html );
		$this->assertStringContainsString( 'property="og:title"', $og_title_html );
		$this->assertStringContainsString( $custom_title, $title_html );
		$this->assertStringContainsString( $custom_title, $og_title_html );

		// Snapshot test to ensure the HTML output matches expected format.
		$this->assertMatchesSnapshot( $output );
	}

	/**
	 * @test
	 */
	public function it_outputs_correct_title_and_og_title_for_event_tag_with_custom_seo_title() {
		// Create an Event Tag term.
		$tag = $this->factory()->tag->create_and_get( [
			'name' => 'Jazz Events',
		] );

		// Set a custom SEO title in Yoast's term meta.
		$custom_title = 'Custom Jazz Events SEO Title';
		\WPSEO_Taxonomy_Meta::set_values( $tag->term_id, $tag->taxonomy, [
			'wpseo_title' => $custom_title,
		] );

		// Mock WordPress functions to simulate being on an Event Tag archive page.
		$this->set_fn_return( 'is_admin', false );
		$this->set_fn_return( 'is_tag', true );
		$this->set_fn_return( 'tribe_is_event_query', true );
		$this->set_fn_return( 'get_queried_object', $tag );

		// Create a mock args object that would be passed to wpseo_replace_vars.
		$args = (object) [
			'name'     => '',
			'term_id'  => '',
			'taxonomy' => '',
		];

		$replacements = [];

		// Call the method to populate term data.
		$this->sut->populate_term_replace_vars( $replacements, $args );

		// Verify the args object was populated.
		$this->assertEquals( 'Jazz Events', $args->name );
		$this->assertEquals( $tag->term_id, $args->term_id );

		// Simulate what Yoast does: replace variables in the custom title template.
		$replaced_title = wpseo_replace_vars( $custom_title, $args );

		// Format as HTML tags (simulating what Yoast's presenters do).
		$title_html = '<title>' . esc_html( $replaced_title ) . '</title>';
		$og_title_html = '<meta property="og:title" content="' . esc_attr( $replaced_title ) . '" />';

		// Combine both outputs for snapshot testing.
		$output = $title_html . "\n" . $og_title_html;

		// Verify both tags are present and contain the custom title.
		$this->assertStringContainsString( '<title>', $title_html );
		$this->assertStringContainsString( 'property="og:title"', $og_title_html );
		$this->assertStringContainsString( $custom_title, $title_html );
		$this->assertStringContainsString( $custom_title, $og_title_html );

		// Snapshot test to ensure the HTML output matches expected format.
		$this->assertMatchesSnapshot( $output );
	}
}
