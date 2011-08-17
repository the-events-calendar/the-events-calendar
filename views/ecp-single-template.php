<?php
	$tribe_ecp = Events_Calendar_Pro::instance();
	get_header();
	echo stripslashes(sp_get_option('spEventsBeforeHTML'));
?>		
	<div id="container">
	<div id="content" class="tec-event widecolumn">
	<?php the_post(); global $post; ?>
			<div id="post-<?php the_ID() ?>" <?php post_class() ?>>
				<h2 class="entry-title"><?php the_title() ?></h2>
				<?php include(tribe_get_current_template()) ?>
				<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
			</div><!-- post -->
		<?php if(sp_get_option('showComments','no') == 'yes'){ comments_template();} ?>
	</div><!-- #content -->
	</div><!--#container-->
<?php get_sidebar(); ?>	
<?php
	echo stripslashes(sp_get_option('spEventsAfterHTML'));
	get_footer();
?>