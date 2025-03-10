<?php
/**
 * Template for a calendar embed.
 *
 * @since TBD
 *
 * @version TBD
 */

defined( 'ABSPATH' ) || exit;

tec_embed_header();

while ( have_posts() ) {
	the_post();
	the_content();
}

tec_embed_footer();
