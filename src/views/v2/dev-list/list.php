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
	<?php var_dump( $_GET ); ?>


	<div class="tribe-events-view-loader tribe-hidden">
		<div class="tribe-events-view-loader-spinner">Loading...</div>
	</div>
	<input type="text" name="tribe-events-views[view]" value="list" />

	<br />

	<a
		href="<?php echo esc_url( home_url( '/events/list/page/3' ) ); ?>"
		class="tribe-events-navigation-link"
	>
		Page 3!
	</a>

	<br />

	<button>
		Searched!
	</button>

</form>