<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since TBD
 *
 * @var string $phase The current Migration phase the site is in.
 * @var string $template_path The absolute path to the Migration template root directory.
 */

use TEC\Events\Custom_Tables\V1\Migration\State;

?>

<div class="tec-ct1-upgrade tec-ct1-upgrade tec-ct1-upgrade--<?php echo esc_attr( $phase ); ?>">
	<?php
	/**
	 * Fires at the top of the upgrade step 1 on Settings > Upgrade.
	 *
	 * @since TBD
	 */
	do_action( 'tec_events_custom_tables_v1_upgrade_before' );

	ob_start();
	include_once $template_path . '/upgrade-logo.php';
	$logo = ob_get_clean();

	include_once $template_path . '/phase/' . $phase . '.php';

	/**
	 * Fires at the bottom of the upgrade step 1 on Settings > Upgrade.
	 *
	 * @since TBD
	 */
	do_action( 'tec_events_custom_tables_v1_upgrade_after' );
	?>
</div>
<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_PREVIEW_PROMPT, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview Prompt</a>
| <a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_PREVIEW_IN_PROGRESS, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview In Progress</a>
| <a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_PROMPT, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration Prompt</a>
| <a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_IN_PROGRESS, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration In Progress</a>
| <a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_COMPLETE, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration Complete</a>
