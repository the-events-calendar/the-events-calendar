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
<div id="tec-ct1-upgrade-box" >
	<div id="tec-ct1-upgrade-dynamic" class="tec-ct1-upgrade">
		<?php // @todo Do we want a spinner here? ?>
		<div class="tec-ct1-upgrade__row">
			<div class="content-container">
				<p>Loading...</p>
			</div>
		</div>
	</div>
	<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_PREVIEW_PROMPT, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview
		Prompt</a>
	|
	<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_PREVIEW_IN_PROGRESS, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Preview
		In Progress</a>
	|
	<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_PROMPT, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration
		Prompt</a>
	|
	<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_IN_PROGRESS, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration
		In Progress</a>
	|
	<a href="<?php echo esc_url( add_query_arg( 'tec_ct1_phase', State::PHASE_MIGRATION_COMPLETE, admin_url( 'edit.php?page=tribe-common&tab=upgrade&post_type=tribe_events' ) ) ); ?>">Migration
		Complete</a>

</div>

