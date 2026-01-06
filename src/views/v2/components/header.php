<?php
/**
 * View Component: Header
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/header.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 6.15.7
 * @since   6.2.0
 * @since 6.15.7 Add support for displaying either breadcrumbs or a back link in the header.
 *
 * @var \Tribe\Events\Views\V2\Template $this                 Template Engine instance rendering.
 * @var bool                            $disable_event_search Boolean on whether to disable the event search.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}
?>

<header <?php tec_classes( $header_classes ); ?>>
	<?php $this->template( 'components/messages' ); ?>

	<?php $this->template( 'components/messages', [ 'classes' => [ 'tribe-events-header__messages--mobile' ] ] ); ?>

	<?php $this->template( 'components/header-title' ); ?>

	<?php
	if ( ! empty( $backlink ) ) {
		$this->template( 'components/backlink' );
	} elseif ( ! empty( $breadcrumbs ) ) {
		$this->template( 'components/breadcrumbs' );
	}
	?>

	<?php $this->template( 'components/events-bar' ); ?>

	<?php $this->template( 'components/content-title' ); ?>

	<?php $this->template( [ $this->get_view_slug(), 'top-bar' ] ); ?>
</header>
