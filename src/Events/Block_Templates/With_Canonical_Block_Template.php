<?php
/**
 * Resolves the single post that owns a Block Template slug inside our theme namespace.
 *
 * @since TBD
 *
 * @package TEC\Events\Block_Templates
 */

namespace TEC\Events\Block_Templates;

use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;
use WP_Post;
use WP_Query;

/**
 * Trait With_Canonical_Block_Template
 *
 * @since TBD
 *
 * @package TEC\Events\Block_Templates
 */
trait With_Canonical_Block_Template {
	/**
	 * Finds the post that owns a template slug in our namespace, trashing the published siblings
	 * competing for the same slug.
	 *
	 * WordPress resolves `wp_template` slug uniqueness against the active stylesheet rather than
	 * against the `wp_theme` term a post is filed under, so posts inserted into our namespace never
	 * receive a suffix and siblings accumulate. Once two of them exist, the next Site Editor save
	 * renames the post it writes to, stranding the customization on a `{slug}-N` post while the
	 * slug itself resolves to a different one.
	 *
	 * @since TBD
	 *
	 * @param string $slug  The template slug to resolve.
	 * @param string $theme The `wp_theme` term the template is filed under.
	 *
	 * @return WP_Block_Template|null The hydrated template, or null when no post owns the slug.
	 */
	protected function find_canonical_block_template( string $slug, string $theme ): ?WP_Block_Template {
		$posts     = $this->get_block_template_posts( $slug, $theme );
		$canonical = array_shift( $posts );

		if ( ! $canonical instanceof WP_Post ) {
			return null;
		}

		foreach ( $posts as $duplicate ) {
			if ( 'publish' !== $duplicate->post_status ) {
				continue;
			}

			wp_trash_post( $duplicate->ID );
		}

		return Template_Utils::hydrate_block_template_by_post( $canonical );
	}

	/**
	 * Reads the posts claiming a template slug in our namespace, most authoritative first.
	 *
	 * A published post always outranks an unpublished one, and the most recently modified post wins
	 * within each group so the freshest customization is the one that survives consolidation.
	 * Trashed posts are left out entirely, so a stale one can never answer for a live template.
	 *
	 * @since TBD
	 *
	 * @param string $slug  The template slug to resolve.
	 * @param string $theme The `wp_theme` term the template is filed under.
	 *
	 * @return WP_Post[] The matching posts, canonical first.
	 */
	protected function get_block_template_posts( string $slug, string $theme ): array {
		$query = new WP_Query(
			[
				'post_name__in'       => [ $slug ],
				'post_type'           => 'wp_template',
				'post_status'         => [ 'publish', 'draft', 'auto-draft' ],
				/* A namespace holds one post per slug; the ceiling only bounds an already corrupted table. */
				'posts_per_page'      => 100,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'orderby'             => [
					'modified' => 'DESC',
					'ID'       => 'DESC',
				],
				'tax_query'           => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The `wp_theme` term is how a block template is namespaced; there is no other way to scope this lookup.
					[
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => $theme,
					],
				],
			]
		);

		$published   = [];
		$unpublished = [];

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			if ( 'publish' === $post->post_status ) {
				$published[] = $post;
				continue;
			}

			$unpublished[] = $post;
		}

		return array_merge( $published, $unpublished );
	}
}
