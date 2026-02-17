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
 * @since 6.15.16 Moved the `content-title` template higher up. [TEC-5733]
 * @since 6.15.16 Conditionally order title templates to ensure primary h1 renders first, with backlink/breadcrumb directly after. [TEC-5733]
 *
 * @var \Tribe\Events\Views\V2\Template $this                 Template Engine instance rendering.
 * @var bool                            $disable_event_search Boolean on whether to disable the event search.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}

$has_header_title = ! empty( $header_title );
$has_backlink     = ! empty( $backlink );
$has_breadcrumbs  = ! empty( $breadcrumbs );
?>

<header <?php tec_classes( $header_classes ); ?>>
	<?php
	// Primary title should always render first (H1).
	if ( $has_header_title ) {
		$this->template( 'components/header-title' );
	} else {
		$this->template( 'components/content-title' );
	}

	// Navigation should render directly after the primary title.
	if ( $has_backlink ) {
		$this->template( 'components/backlink' );
	} elseif ( $has_breadcrumbs ) {
		$this->template( 'components/breadcrumbs' );
	}

	// If header-title was the primary title, render content-title as a secondary title.
	if ( $has_header_title ) {
		$this->template( 'components/content-title' );
	}
	?>

	<?php $this->template( 'components/messages' ); ?>

	<?php $this->template( 'components/messages', [ 'classes' => [ 'tribe-events-header__messages--mobile' ] ] ); ?>

	<?php $this->template( 'components/events-bar' ); ?>

	<?php $this->template( [ $this->get_view_slug(), 'top-bar' ] ); ?>
</header>
