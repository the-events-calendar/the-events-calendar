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
	<input type="text" name="tribe-events-views[view]" value="default" />

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