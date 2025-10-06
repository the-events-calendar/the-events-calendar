<?php
/**
 * View: Breadcrumbs
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breadcrumbs.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.7
 *
 * @since 6.15.7 Switched to `nav` element, added Aria labels to make more accessible.
 *
 * @var array $breadcrumbs An array of data for breadcrumbs.
 */

if ( empty( $breadcrumbs ) ) {
	return;
}

$last_index = array_key_last( $breadcrumbs );

$breadcrumbs = array_map(
	static function ( $crumb, $index ) use ( $last_index ) {
		$crumb['is_last'] = ( $index === $last_index );

		return $crumb;
	},
	$breadcrumbs,
	array_keys( $breadcrumbs )
);

?>
<nav class="tribe-events-header__breadcrumbs tribe-events-c-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'the-events-calendar' ); ?>">
	<ol class="tribe-events-c-breadcrumbs__list">
		<?php
		foreach ( $breadcrumbs as $breadcrumb ) :
			?>

			<?php if ( ! empty( $breadcrumb['link'] ) ) : ?>
				<?php $this->template( 'components/breadcrumbs/linked-breadcrumb', [ 'breadcrumb' => $breadcrumb ] ); ?>
		<?php else : ?>
			<?php $this->template( 'components/breadcrumbs/breadcrumb', [ 'breadcrumb' => $breadcrumb ] ); ?>
		<?php endif; ?>

		<?php endforeach; ?>
	</ol>
</nav>
