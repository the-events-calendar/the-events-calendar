<div id="modern-tribe-info" class="tribe-upgrade">
	<?php
	if ( tribe_events_views_v2_is_enabled() ) {
		?>
		<div id="tribe-upgrade-complete">
			<?php
			/**
			 * Fires at the top of the upgrade complete on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_complete_before' );
			?>

			<div class="content-container">
				<h3><?php esc_html_e( 'You\'re all set!', 'tribe-common' ); ?></h3>

				<p><?php esc_html_e( 'Go checkout your new views! Remember, each view has been updated so feel free to enable views you may not have been using. If necessary, you can revert back to the legacy views in the Display tab of your Event Settings.', 'tribe-common' ); ?></p>

				<a class="button" href="<?php echo esc_url( Tribe__Events__Main::instance()->getLink() ); ?>"><?php esc_html_e( 'View Calendar', 'tribe-common' ); ?></a>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-success.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'tribe-common' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade complete on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_complete_after' );
			?>
		</div>
		<?php
	} else {
		?>
		<div id="tribe-upgrade-step1">
			<?php
			/**
			 * Fires at the top of the upgrade step 1 on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_step1_before' );
			?>

			<div class="content-container">
				<span>✨ <?php esc_html_e( 'A new look for views is here!', 'tribe-common' ); ?></span>

				<h3><?php esc_html_e( 'Upgrade your calendar views.', 'tribe-common' ); ?></h3>

				<p><?php esc_html_e( 'We\'ve redesigned all of the calendar views to give you and your users a better experience. Beyond a brand new look, we\'ve optimized each look for mobile and introduced key improvements for each view.', 'tribe-common' ); ?></p>

				<button type="button"><?php esc_html_e( 'Start your update', 'tribe-common' ); ?></button>
				<a href="http://m.tri.be/updated-views" target="_blank" rel="noopener"><?php esc_html_e( 'Learn more about the update', 'tribe-common' ); ?></a>

				<div class="step"><?php esc_html_e( 'Step 1 of 2', 'tribe-common' ); ?></div>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-screenshot.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'tribe-common' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade step 1 on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_step1_after' );
			?>
		</div>

		<div id="tribe-upgrade-step2" class="hidden">
			<?php
			/**
			 * Fires at the top of the upgrade step 2 on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_step2_before' );
			?>

			<div class="content-container">
				<h3><?php esc_html_e( 'Confirm your update', 'tribe-common' ); ?></h3>

				<ul>
					<li>
						<h4><span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Existing calendar customizations will be overridden.', 'tribe-common' ); ?></h4>
						<p><?php esc_html_e( 'If you have any template overrides, custom calendar CSS, or other code customizations to you calendar, those modifications will be overridden by the new views', 'tribe-common' ); ?></p>
					</li>
					<li>
						<h4><span class="dashicons dashicons-warning"></span>
						<?php echo sprintf(
							esc_html__( 'We recommend making this update on a %1$sstaging site%2$s.', 'tribe-common' ),
							'<a href="http://m.tri.be/kb-staging" target="_blank">',
							'</a>'
						); ?></h4>
						<p><?php esc_html_e( 'This is especially true if you have made any code customizations to your calendar', 'tribe-common' ); ?></p>
					</li>
				</ul>

				<button type="submit"><?php esc_html_e( 'Let\'s go!', 'tribe-common' ); ?></button>
				<a href="<?php echo esc_url( Tribe__Settings::instance()->get_url() ); ?>" rel="noopener"><?php esc_html_e( 'I\'m not ready', 'tribe-common' ); ?></a>

				<div class="step"><?php esc_html_e( 'Step 2 of 2', 'tribe-common' ); ?></div>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-screenshot.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'tribe-common' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade step 2 on Settings > Upgrade
			 */
			do_action( 'tribe_upgrade_step2_after' );
			?>
		</div>
		<?php
	}
	?>

</div>