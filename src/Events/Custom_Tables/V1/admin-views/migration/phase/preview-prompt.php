<?php
use TEC\Events\Custom_Tables\V1\Migration\Strings;

$strings = tribe( Strings::class );
?>

<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<span>
			<?php echo esc_html( $strings->get( 'preview-prompt-get-ready' ) ); ?>
		</span>

		<h3>
			<?php echo $logo; ?>
			<?php echo esc_html( $strings->get( 'preview-prompt-upgrade-cta' ) ); ?>
		</h3>

		<p>
			<?php echo esc_html( $strings->get( 'preview-prompt-features' ) ); ?>
		</p>

		<p>
			<strong>
				<?php echo esc_html( $strings->get( 'preview-prompt-ready' ) ); ?>
			</strong>
			<?php esc_html_e( 'We\'ll scan all existing events and let you know what to expect from the migration process. You\'ll also get an idea of how long your migration will take. The preview runs in the background, so you’ll be able to continue using your site.', 'the-events-calendar' ); ?>
		</p>

		<button type="button"><?php esc_html_e( 'Start migration preview', 'the-events-calendar' ); ?></button>
		<a href="http://evnt.is/recurrence-2-0" target="_blank" rel="noopener">
			<?php esc_html_e( 'Learn more about the migration', 'the-events-calendar' ); ?>
		</a>
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
