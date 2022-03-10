<?php

use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php include $template_directory . '/upgrade-logo.php'; ?>
			<?php echo esc_html( $text->get( 'migration-in-progress' ) ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
					$text->get( 'migration-in-progress-paragraph' ),
					'<strong>',
					'</strong>'
			);

			if ( $addendum = tribe( Upgrade_Tab::class )->get_migration_prompt_addendum() ) {
				?>
				<strong><?php echo esc_html( $addendum ); ?></strong>
				<?php
			}
			?>
			<a href="<?php echo esc_url( $text->get( 'learn-more-button-url' ) ); ?>" target="_blank" rel="noopener">
				<strong><?php echo esc_html( $text->get( 'learn-more-button' ) ); ?></strong>
			</a>
		</p>

		<div class="tec-ct1-upgrade-update-bar-container">
			<p><?php echo esc_html( $text->get( 'loading-message' ) ); ?></p>
		</div>
		<div>
			<a href="#"
			   class="tec-ct1-upgrade-cancel-migration tec-ct1-upgrade__link-danger"><?php echo esc_html( $text->get( 'cancel-migration-button' ) ); ?></a>
		</div>
	</div>
	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( 'completed-screenshot-url' ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'updated-views-screenshot-alt' ) ); ?>"/>
	</div>
</div>
