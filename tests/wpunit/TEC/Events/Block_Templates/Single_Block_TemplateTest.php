<?php

namespace TEC\Events\Block_Templates;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Block_Templates\Single_Event\Single_Block_Template;
use WP_Query;
use WP_REST_Request;

class Single_Block_TemplateTest extends WPTestCase {
	/**
	 * The `wp_theme` term our templates are filed under.
	 *
	 * @var string
	 */
	private const THEME = 'tec';

	/**
	 * The slug the Single Event template claims.
	 *
	 * @var string
	 */
	private const SLUG = 'single-event';

	public function test_it_keeps_the_most_recently_modified_post_and_trashes_the_duplicates(): void {
		$stale_content = '<!-- wp:paragraph --><p>Stale layout</p><!-- /wp:paragraph -->';
		$fresh_content = '<!-- wp:paragraph --><p>Current layout</p><!-- /wp:paragraph -->';

		/* The stale post is the newest row by ID, so only the modified date can pick the winner. */
		$fresh_id = $this->given_a_template_post( $fresh_content );
		$stale_id = $this->given_a_template_post( $stale_content, 30 );

		$template = tribe( Single_Block_Template::class )->get_block_template();

		$this->assertSame( $fresh_id, $template->wp_id );
		$this->assertSame( $fresh_content, $template->content );
		$this->assertSame( [ $fresh_id ], $this->get_posts_claiming_slug() );
		$this->assertSame( 'trash', get_post_status( $stale_id ) );
	}

	public function test_it_leaves_a_single_template_post_untouched(): void {
		$content = '<!-- wp:paragraph --><p>Only layout</p><!-- /wp:paragraph -->';
		$post_id = $this->given_a_template_post( $content );

		$template = tribe( Single_Block_Template::class )->get_block_template();

		$this->assertSame( $post_id, $template->wp_id );
		$this->assertSame( $content, $template->content );
		$this->assertSame( [ $post_id ], $this->get_posts_claiming_slug() );
		$this->assertSame( 'publish', get_post_status( $post_id ) );
	}

	public function test_it_ignores_trashed_posts_when_a_published_post_exists(): void {
		$content    = '<!-- wp:paragraph --><p>Published layout</p><!-- /wp:paragraph -->';
		$post_id    = $this->given_a_template_post( $content );
		$trashed_id = $this->given_a_template_post( '<!-- wp:paragraph --><p>Trashed layout</p><!-- /wp:paragraph -->' );

		wp_trash_post( $trashed_id );

		$template = tribe( Single_Block_Template::class )->get_block_template();

		$this->assertSame( $post_id, $template->wp_id );
		$this->assertSame( $content, $template->content );
	}

	public function test_a_site_editor_save_updates_the_canonical_post_in_place(): void {
		$this->given_a_template_post( '<!-- wp:paragraph --><p>Default markup</p><!-- /wp:paragraph -->', 30 );
		$canonical_id = $this->given_a_template_post( '<!-- wp:paragraph --><p>Customer layout</p><!-- /wp:paragraph -->' );

		/*
		 * Nothing lists the templates first: opening a template by its URL resolves it straight by ID,
		 * which is the path the save runs through and the one that used to skip consolidation.
		 */
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$edited  = '<!-- wp:heading --><h2>Edited layout</h2><!-- /wp:heading -->';
		$request = new WP_REST_Request( 'PUT', '/wp/v2/templates/' . self::THEME . '//' . self::SLUG );
		$request->set_param( 'id', self::THEME . '//' . self::SLUG );
		$request->set_param( 'content', $edited );

		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );

		$saved = get_post( $canonical_id );

		$this->assertSame( self::SLUG, $saved->post_name );
		$this->assertSame( $edited, $saved->post_content );
		$this->assertSame( [ $canonical_id ], $this->get_posts_claiming_slug() );
		$this->assertSame( $canonical_id, get_block_template( self::THEME . '//' . self::SLUG )->wp_id );
	}

	/**
	 * Inserts a published `wp_template` post claiming our slug inside our namespace.
	 *
	 * WordPress scopes `wp_template` slug uniqueness to the active stylesheet, so repeated calls
	 * here reproduce the customer state: several published posts all named `single-event` and all
	 * filed under the `tec` term.
	 *
	 * @param string $content The post content to store.
	 * @param int    $age     How many minutes in the past the post was last modified.
	 *
	 * @return int The created post ID.
	 */
	private function given_a_template_post( string $content, int $age = 0 ): int {
		global $wpdb;

		$post_id = wp_insert_post(
			[
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_name'    => self::SLUG,
				'post_title'   => 'Single Event',
				'post_content' => $content,
			]
		);

		wp_set_post_terms( $post_id, self::THEME, 'wp_theme' );

		if ( $age > 0 ) {
			$modified = gmdate( 'Y-m-d H:i:s', strtotime( get_post( $post_id )->post_modified_gmt ) - ( $age * MINUTE_IN_SECONDS ) );
			$wpdb->update(
				$wpdb->posts,
				[
					'post_modified'     => $modified,
					'post_modified_gmt' => $modified,
				],
				[ 'ID' => $post_id ]
			);
			clean_post_cache( $post_id );
		}

		return $post_id;
	}

	/**
	 * Returns the IDs of every non-trashed post currently claiming our slug in our namespace.
	 *
	 * @return int[] The matching post IDs.
	 */
	private function get_posts_claiming_slug(): array {
		$query = new WP_Query(
			[
				'post_name__in'  => [ self::SLUG ],
				'post_type'      => 'wp_template',
				'post_status'    => [ 'publish', 'draft', 'auto-draft' ],
				'fields'         => 'ids',
				/* Mirrors the ceiling the production lookup uses, so both read the same corrupted table. */
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The `wp_theme` term is how a block template is namespaced.
					[
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => self::THEME,
					],
				],
			]
		);

		return $query->posts;
	}
}
