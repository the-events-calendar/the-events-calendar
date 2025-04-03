<?php
/**
 * Template for a calendar embed.
 *
 * @since 6.11.0
 *
 * @version 6.11.0
 */

defined( 'ABSPATH' ) || exit;

tec_embed_header();

while ( have_posts() ) {
	the_post();
	the_content();
}

tec_embed_footer();
