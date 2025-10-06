<?php
/**
 * View: backlink
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/backlink.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.7
 *
 * @var array $backlink An array of data for breadcrumbs.
 */

if ( empty( $backlink['url'] ) || empty( $backlink['label'] ) ) {
	return;
}

?>
<nav class="tribe-events-back" aria-label="<?php esc_attr_e( 'Back link', 'the-events-calendar' ); ?>">
	<a href="<?php echo esc_url( $backlink['url'] ); ?>" class="tribe-events-c-back-link tribe-common-anchor">
		&laquo; <?php echo esc_html( $backlink['label'] ); ?>
	</a>
</nav>
