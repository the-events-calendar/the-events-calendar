<?php
/**
 * View: Events Bar Form
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/form.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */

use Tribe\Events\Views\V2\Rest_Endpoint;
?>
<div class="tribe-events-c-events-bar__form">
	<form
		class="tribe-common-c-search"
		method="get"
		data-js="tribe-events-view-form"
	>
		<?php wp_nonce_field( 'wp_rest', 'tribe-events-views[_wpnonce]' ); ?>
		<input type="hidden" name="tribe-events-views[url]" value="<?php echo esc_url( $this->get( 'url' ) ); ?>" />

		<div class="tribe-common-form-control-text-group tribe-common-c-search__input-group">
			<?php $this->template( 'events-bar/form/keyword' ); ?>
			<?php $this->template( 'events-bar/form/date' ); ?>
			<?php $this->template( 'events-bar/form/submit' ); ?>
		</div>

	</form>
</div>
