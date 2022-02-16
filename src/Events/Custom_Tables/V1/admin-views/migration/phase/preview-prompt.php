<div class="tec-upgrade-recurrence__row">
	<div class="content-container">
		<span><?php esc_html_e( 'Get ready for the new recurring events!', 'ical-tec' ); ?></span>

		<h3>
			<?php echo $logo; ?>
			<?php esc_html_e( 'Upgrade your recurring events.', 'ical-tec' ); ?>
		</h3>

		<p><?php esc_html_e( 'Faster event editing. Smarter save options. More flexibility. Events Calendar 6.0  is full of features to make managing recurring and connected events better than ever. Before you get started, we need to migrate your existing events into the new system.', 'ical-tec' ); ?></p>

		<p>
			<strong><?php esc_html_e('Ready to go? The first step is a migration preview.', 'ical-tec' ); ?></strong>
			<?php esc_html_e( 'We\'ll scan all existing events and let you know what to expect from the migration process. You\'ll also get an idea of how long your migration will take. The preview runs in the background, so you’ll be able to continue using your site.', 'ical-tec' ); ?>
		</p>

		<button type="button"><?php esc_html_e( 'Start migration preview', 'ical-tec' ); ?></button>
		<a href="http://evnt.is/recurrence-2-0" target="_blank" rel="noopener">
			<?php esc_html_e( 'Learn more about the migration', 'ical-tec' ); ?>
		</a>
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
