<?php
/**
 * View: Events Bar Search
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/search.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.10
 *
 */
?>
<div
	class="tribe-events-c-events-bar__search"
	id="tribe-events-events-bar-search"
	data-js="tribe-events-events-bar-tabpanel tribe-events-events-bar-search"
>
	<form
		class="tribe-events-c-search tribe-events-c-events-bar__search-form"
		method="get"
		data-js="tribe-events-view-form"
		role="search"
	>
		<?php wp_nonce_field( 'wp_rest', 'tribe-events-views[_wpnonce]' ); ?>
		<input type="hidden" name="tribe-events-views[url]" value="<?php echo esc_url( $this->get( 'url' ) ); ?>" />

		<div class="tribe-events-c-search__input-group">
			<?php $this->template( 'components/events-bar/search/keyword' ); ?>
		</div>

		<?php $this->template( 'components/events-bar/search/submit' ); ?>
	</form>
</div>
