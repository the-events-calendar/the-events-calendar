<?php

use \Tribe\Events\Views\V2\Template;

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
 * @version 6.2.0
 * @since   6.2.0
 *
 * @var Template $this                 Template Engine instance rendering.
 * @var bool     $disable_event_search Boolean on whether to disable the event search.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}
?>

<header <?php tribe_classes( $header_classes ); ?>>
	<?php $this->template( 'components/messages' ); ?>

	<?php $this->template( 'components/messages', [ 'classes' => [ 'tribe-events-header__messages--mobile' ] ] ); ?>

	<?php $this->template( 'components/header-title' ); ?>

	<?php $this->template( 'components/breadcrumbs' ); ?>

	<?php $this->template( 'components/events-bar' ); ?>

	<?php $this->template( 'components/content-title' ); ?>

	<?php $this->template( [ $this->get_view_slug(), 'top-bar' ] ); ?>
</header>
