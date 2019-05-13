<?php
use Tribe\Events\Views\V2\Rest_Endpoint;

tribe_asset_enqueue( 'tribe-events-views-v2-manager' );

?>
<form
	class="tribe-events-container"
	action=""
	method="get"
	data-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<?php // @todo remove this css ?>
	<style type="text/css">
		.tribe-events-container {
			position: relative;
		}

		.tribe-events-view-loader {
			align-content: center;
			justify-content: center;
			display: flex;
			position: absolute;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			background: rgba( 255, 255, 255, 0.6 );
		}

		.tribe-events-view-loader.tribe-hidden {
			display: none;
		}

		.tribe-events-view-loader .tribe-events-view-loader-spinner {
			align-self: center;
		}
	</style>
	<?php // @todo remove this css ?>

	<?php

	var_dump( $_GET );

	?>


	<div class="tribe-events-view-loader tribe-hidden">
		<div class="tribe-events-view-loader-spinner">Loading...</div>
	</div>
	<input type="text" name="tribe-events-views[view]" value="list" />

	<br />

	<a
		href="<?php echo esc_url( home_url( '/events/list' ) ); ?>"
		class="tribe-events-navigation-link"
	>
		Developers, Developers, Developers
	</a>

	<br />

	<button>
		Button from Developers
	</button>

</form>