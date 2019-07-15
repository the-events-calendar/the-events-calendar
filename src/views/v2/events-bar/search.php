<?php
/**
 * View: Events Bar Search
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/search.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

use Tribe\Events\Views\V2\Rest_Endpoint;
?>
<div
	class="tribe-events-c-events-bar__search"
	id="tribe-events-events-bar-search"
	data-js="tribe-events-events-bar-tabpanel tribe-events-events-bar-search"
>
	<form
		class="tribe-common-c-search tribe-events-c-events-bar__search-form"
		method="get"
		data-js="tribe-events-view-form"
		role="search"
	>
		<?php wp_nonce_field( 'wp_rest', 'tribe-events-views[_wpnonce]' ); ?>
		<input type="hidden" name="tribe-events-views[url]" value="<?php echo esc_url( $this->get( 'url' ) ); ?>" />

		<div class="tribe-common-form-control-text-group tribe-common-c-search__input-group">
			<?php $this->template( 'events-bar/search/keyword' ); ?>
		</div>

		<?php $this->template( 'events-bar/search/submit' ); ?>
	</form>
</div>
