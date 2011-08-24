<?php
/**
* Single Post Template
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php get_header(); ?>
<?php tribe_events_before_html() ?>
<div id="container">
	<div id="content" class="tribe-events-event widecolumn">
		<?php the_post(); global $post; ?>
		<div id="post-<?php the_ID() ?>" <?php post_class() ?>>
			<h2 class="entry-title"><?php the_title() ?></h2>
			<?php include(tribe_get_current_template()) ?>
			<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
		</div><!-- post -->
		<?php if(tribe_get_option('showComments','no') == 'yes'){ comments_template();} ?>
	</div><!-- #content -->
</div><!--#container-->
<?php get_sidebar(); ?>
<?php tribe_events_after_html() ?>
<?php get_footer(); ?>
