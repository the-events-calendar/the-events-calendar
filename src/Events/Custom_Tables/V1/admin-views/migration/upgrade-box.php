<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since TBD
 *
 * @var string $phase The current Migration phase the site is in.
 * @var string $template_path The absolute path to the Migration template root directory.
 */
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

$text = tribe( String_Dictionary::class );
/*$state = tribe(\TEC\Events\Custom_Tables\V1\Migration\State::class);
$state->set('phase', \TEC\Events\Custom_Tables\V1\Migration\State::PHASE_REVERT_COMPLETE);
$state->save();*/
/*$state = tribe(\TEC\Events\Custom_Tables\V1\Migration\State::class);
$state->set('phase', \TEC\Events\Custom_Tables\V1\Migration\State::PHASE_MIGRATION_PROMPT);
$state->save();*/
?>
<div id="tec-ct1-upgrade-box" >
	<div id="tec-ct1-upgrade-dynamic" class="tec-ct1-upgrade">
		<?php // @todo Do we want a spinner here? ?>
		<div class="tec-ct1-upgrade__row">
			<div class="content-container">
				<p><?php echo esc_html( $text->get( 'loading-message' ) ); ?></p>
			</div>
		</div>
	</div>
</div>

