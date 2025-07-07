<?php
/**
 * Category Colors Migration Modal (Thickbox)
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<div id="tec-category-colors-migration-thickbox" style="display:none;">
	<div class="tec-category-colors-migration-thickbox">
		<h2><?php esc_html_e( 'Category Colors Migration', 'the-events-calendar' ); ?></h2>
		<p>
			<?php
			esc_html_e(
				"You're about to migrate your Category Colors plugin settings to the new built-in Category Colors feature in The Events Calendar.",
				'the-events-calendar'
			);
			?>
		</p>
		<ul class="ul-disc">
			<li><?php esc_html_e( 'Your current category colors will be mapped to the corresponding event categories.', 'the-events-calendar' ); ?></li>
			<li><?php esc_html_e( 'The Category Colors plugin will be deactivated after migration.', 'the-events-calendar' ); ?></li>
			<li><?php esc_html_e( "Category color mapping is now done directly in the Event Categories page. You'll be redirected there to review your colors.", 'the-events-calendar' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'This process is safe and non-destructive — no event data will be changed.', 'the-events-calendar' ); ?></p>
		<div style="display: flex; justify-content: flex-end; gap: 1em; margin-top: 2em;">
			<button type="button" class="button" onclick="tb_remove(); return false;">
				<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
			</button>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin: 0;">
				<input type="hidden" name="action" value="tec_start_category_colors_migration">
				<?php wp_nonce_field( 'tec_start_category_colors_migration' ); ?>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Migrate Now', 'the-events-calendar' ); ?>
				</button>
			</form>
		</div>
	</div>
</div>
