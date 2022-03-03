<?php


use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\State;

$state = tribe( State::class );

if ( $state->is_completed() ) {
	$report_meta = $state->get( 'migration' );
} else {
	$report_meta = $state->get( 'preview' );
}
//@Todo...
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			@Todo...
		</h3>


	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
