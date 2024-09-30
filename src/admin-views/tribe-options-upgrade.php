<div id="modern-tribe-info" class="tribe-upgrade">
	<?php
	if ( tribe_events_views_v2_is_enabled() ) {
		?>
		<div id="tribe-upgrade-complete">
			<?php
			/**
			 * Fires at the top of the upgrade complete on Settings > Upgrade
			 *
			 * @since 4.9.12
			 */
			do_action( 'tribe_upgrade_complete_before' );
			?>

			<div class="content-container">
				<h3><?php esc_html_e( 'You\'re all set!', 'the-events-calendar' ); ?></h3>

				<p><?php esc_html_e( 'Go check out your new calendar designs! Each calendar view has been updated, so feel free to enable views you may not have been using. If necessary, you can revert back to the legacy calendar views in the Display tab of your Event Settings.', 'the-events-calendar' ); ?></p>

				<a class="button tec-settings-button-secondary" href="<?php echo esc_url( Tribe__Events__Main::instance()->getLink() ); ?>"><?php esc_html_e( 'View Calendar', 'the-events-calendar' ); ?></a>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-success.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade complete on Settings > Upgrade
			 *
			 * @since 4.9.12
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
			 *
			 * @since 4.9.12
			 */
			do_action( 'tribe_upgrade_step1_before' );
			?>

			<div class="content-container">
				<span>✨ <?php esc_html_e( 'A new look for The Events Calendar is here!', 'the-events-calendar' ); ?></span>

				<h3><?php esc_html_e( 'Upgrade your calendar.', 'the-events-calendar' ); ?></h3>

				<p><?php esc_html_e( 'We\'ve redesigned all of the calendar views to give you and your users a better experience. Beyond a brand new look, we\'ve optimized every design for mobile and introduced key improvements for each view.', 'the-events-calendar' ); ?></p>

				<button type="button"><?php esc_html_e( 'Start your upgrade', 'the-events-calendar' ); ?></button>
				<a href="http://evnt.is/updated-views" target="_blank" rel="noopener" class="tribe-upgrade-absolute-text">
					<?php esc_html_e( 'Learn more about the upgrade', 'the-events-calendar' ); ?>
				</a>

				<div class="step"><?php esc_html_e( 'Step 1 of 2', 'the-events-calendar' ); ?></div>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-screenshot.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade step 1 on Settings > Upgrade
			 *
			 * @since 4.9.12
			 */
			do_action( 'tribe_upgrade_step1_after' );
			?>
		</div>

		<div id="tribe-upgrade-step2" class="hidden">
			<?php
			/**
			 * Fires at the top of the upgrade step 2 on Settings > Upgrade
			 *
			 * @since 4.9.12
			 */
			do_action( 'tribe_upgrade_step2_before' );
			?>

			<div class="content-container">
				<h3><?php esc_html_e( 'Confirm your upgrade', 'the-events-calendar' ); ?></h3>

				<ul>
					<li>
						<h4><span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Existing calendar customizations will be overridden.', 'the-events-calendar' ); ?></h4>
						<p><?php esc_html_e( 'If you have any template overrides, custom calendar CSS, or other code customizations to your calendar, those modifications will be overridden by the new designs.', 'the-events-calendar' ); ?></p>
					</li>
					<li>
						<h4><span class="dashicons dashicons-warning"></span>
						<?php echo sprintf(
							esc_html__( 'We recommend making this update on a %1$sstaging site%2$s.', 'the-events-calendar' ),
							'<a href="http://evnt.is/kb-staging" target="_blank">',
							'</a>'
						); ?></h4>
						<p><?php esc_html_e( 'This is especially true if you have made any code customizations to your calendar.', 'the-events-calendar' ); ?></p>
					</li>
				</ul>

				<button type="submit"><?php esc_html_e( 'Let\'s go!', 'the-events-calendar' ); ?></button>
				<a href="<?php echo esc_url( tribe( 'tec.main' )->settings()->get_url() ); ?>" rel="noopener"><?php esc_html_e( 'I\'m not ready', 'the-events-calendar' ); ?></a>

				<div class="step"><?php esc_html_e( 'Step 2 of 2', 'the-events-calendar' ); ?></div>
			</div>

			<div class="image-container">
				<img class="screenshot" src="<?php echo esc_url( plugins_url( 'resources/images/upgrade-views-screenshot.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
			</div>

			<?php
			/**
			 * Fires at the bottom of the upgrade step 2 on Settings > Upgrade
			 *
			 * @since 4.9.12
			 */
			do_action( 'tribe_upgrade_step2_after' );
			?>
		</div>
		<?php
	}
	?>

</div>
