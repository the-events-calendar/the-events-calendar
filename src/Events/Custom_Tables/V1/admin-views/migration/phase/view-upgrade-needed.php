<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<?php // @todo filter from here and hook in ECP or change the copy. ?>
		<span><?php esc_html_e( 'Get ready for the new recurring events!', 'ical-tec' ); ?></span>

		<h3>
			<?php echo $logo; ?>
			<?php // @todo filter from here and hook in ECP or change the copy. ?>
			<?php esc_html_e( 'Upgrade your recurring events.', 'ical-tec' ); ?>
		</h3>

		<?php // @todo filter from here and hook in ECP or change the copy. ?>
		<p><?php esc_html_e( 'Faster event editing. Smarter save options. More flexibility. Events Calendar 6.0 is full of features to make managing recurring and connected events better than ever. Before you get started, we need to migrate your existing events into the new system.', 'ical-tec' ); ?></p>

		<?php // @todo filter from here and hook in ECP or change the copy. ?>
		<p><strong><?php esc_html_e( 'You must be using the upgraded calendar views to take advantage of the new recurring events features.', 'ical-tec' ); ?></strong></p>

		<button type="button" disabled="disabled"><?php esc_html_e( 'Start migration preview', 'ical-tec' ); ?></button>
		<a href="http://evnt.is/recurrence-2-0" target="_blank" rel="noopener">
			<?php esc_html_e( 'Learn more about the migration', 'ical-tec' ); ?>
		</a>
	</div>

	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
