<?php
use Tribe\Events\Views\V2\Rest_Endpoint;
use Tribe\Events\Views\V2\View;

tribe_asset_enqueue( 'tribe-events-views-v2-manager' );
?>
<form
	class="tribe-events-container"
	action=""
	method="get"
	data-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<div class="tribe-events-view-loader tribe-hidden">
		<div class="tribe-events-view-loader-spinner">Loading...</div>
	</div>
	<input type="text" name="tribe-events-views[view]" value="default" />

	<?php wp_nonce_field( 'wp_rest', 'tribe-events-views[nonce]' ); ?>

	<br />

	<a
		href="<?php echo esc_url( home_url( '/events/list/page/2' ) ); ?>"
		class="tribe-events-navigation-link"
	>
		Page 2
	</a>

	<br />

	<button>
		Search?
	</button>

</form>
<form
	class="tribe-events-container"
	action=""
	method="get"
	data-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<div class="tribe-events-view-loader tribe-hidden">
		<div class="tribe-events-view-loader-spinner">Loading...</div>
	</div>
	<input type="text" name="tribe-events-views[view]" value="default" />

	<br />

	<a
		href="<?php echo esc_url( home_url( '/events/list/page/2' ) ); ?>"
		class="tribe-events-navigation-link"
	>
		Page 2
	</a>

	<br />

	<button>
		Search?
	</button>

</form>

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
