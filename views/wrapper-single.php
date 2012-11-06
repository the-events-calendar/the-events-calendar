<?php
/**
 * Single Page Wrapper Template
 * This file loads the single page wrapper template for the single page specific views (single-event.php,
 * single-venue.php, etc).
 *
 * If 'Default Events Template' is selected in Events -> Settings -> Template -> Events Template, 
 * then this file loads the single page wrapper template for all the single page views. Generally,
 * this setting should only be used if you want to manually specify all the wrapper markup of
 * your views in this template file. You can also select one of the other Events Template 
 * Settings to automatically integrate views into your theme.
 *
 * You can recreate an ENTIRELY new single page wrapper template by doing a template override,
 * and placing a wrapper-single.php file in a tribe-events/ directory within your theme
 * directory, which will override the /views/wrapper-single.php. 
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }
?>

<?php get_header(); ?>
	<?php tribe_events_before_html(); ?>

		<?php the_post(); global $post; ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class('hentry vevent'); ?>>
				<?php include( tribe_get_current_template() ); ?>
				<?php edit_post_link( __( 'Edit', 'tribe-events-calendar' ), '<span class="edit-link">', '</span>' ); ?>
			</div><!-- .hentry .vevent -->
		<?php if( tribe_get_option( 'showComments','no' ) == 'yes' ) { comments_template(); } ?>
		
		
		<?php /* get_sidebar(); */ ?> 
		
	<?php tribe_events_after_html(); ?>
<?php get_footer(); ?>
