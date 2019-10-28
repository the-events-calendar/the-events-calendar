<?php
/**
 * View: Breadcrumbs
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/breadcrumbs.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var string $today_url The URL to the today page.
 */

$context   = $this->view->get_context();
$tax       = $context->get( 'taxonomy', false );
$term_slug = $tax ? $context->get( $tax, false ) : false;

if ( ! $term_slug ) {
	return;
}

$term = get_term_by( 'slug', $term_slug, $tax );
?>
<div class="tribe-events-header__breadcrumbs tribe-events-c-breadcrumbs">
	<ol class="tribe-events-c-breadcrumbs__list">
		<li class="tribe-events-c-breadcrumbs__list-item">
			<a
				href="<?php echo esc_url( $today_url ); ?>"
				class="tribe-events-c-breadcrumbs__list-item-link"
				data-js="tribe-events-view-link"
			>
				<?php echo esc_html( tribe_get_event_label_plural() ); ?>
			</a>
		</li>
		<li class="tribe-events-c-breadcrumbs__list-item">
			<span class="tribe-events-c-breadcrumbs__list-item-text">
				<?php echo esc_html( $term ); ?>
			</span>
		</li>
	</ol>
</div>
