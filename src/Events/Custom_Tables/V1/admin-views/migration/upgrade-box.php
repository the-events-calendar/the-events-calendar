<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since TBD
 *
 * @var string $phase The current Migration phase the site is in.
 * @var string $template_path The absolute path to the Migration template root directory.
 */
?>

<div class="tec-upgrade tec-upgrade-recurrence tec-upgrade-recurrence--<?php echo esc_attr( $phase ); ?>">
	<?php
	/**
	 * Fires at the top of the upgrade step 1 on Settings > Upgrade
	 *
	 * @since TBD
	 */
	do_action( 'tribe_upgrade_recurrence_migration_before' );

	ob_start();
	include_once $template_path . '/upgrade-logo.php';
	$logo = ob_get_clean();

	include_once $template_path . '/phase/' . $phase . '.php';

	/**
	 * Fires at the bottom of the upgrade step 1 on Settings > Upgrade
	 *
	 * @since TBD
	 */
	do_action( 'tribe_upgrade_recurrence_migration_after' );
	?>
</div>
<a href="<?php echo esc_url( add_query_arg( 'phase', 'view-upgrade-needed', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">View Upgrade Needed</a>
| <a href="<?php echo esc_url( add_query_arg( 'phase', 'preview-prompt', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview Prompt</a>
| <a href="<?php echo esc_url( add_query_arg( 'phase', 'preview-in-progress', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview In Progress</a>
| <a href="<?php echo esc_url( add_query_arg( 'phase', 'migration-prompt', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration Prompt</a>
| <a href="<?php echo esc_url( add_query_arg( 'phase', 'migration-in-progress', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration In Progress</a>
| <a href="<?php echo esc_url( add_query_arg( 'phase', 'migration-complete', admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration Complete</a>
