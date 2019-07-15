<?php
/**
 * View: Top Bar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
?>
<div class="tribe-events-c-top-bar tribe-events-header__top-bar">

	<?php $this->template( 'top-bar/nav' ); ?>

	<?php $this->template( 'top-bar/today' ); ?>

	<?php $this->template( 'top-bar/date-picker' ); ?>

	<?php $this->template( 'top-bar/actions' ); ?>

</div>
