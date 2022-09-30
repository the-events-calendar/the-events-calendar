<?php

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * Template to be used on the Events Settings tabs for maintenance modal locking.
 *
 * @since 6.0.0
 *
 * @var String_Dictionary $text The text dictionary for translations.
 */
?>
<style>
	.tribe-settings-form.form {
		position: relative;
	}

	.tribe-settings-form #ct1-settings-tab-maintenance-modal {
		background: rgba(0, 0, 0, 0.7);
		position: absolute;
		top: -4px;
		bottom: -4px;
		left: -4px;
		right: -4px;
		z-index: 999;
		padding: 24px;
	}

	.tribe-settings-form #ct1-settings-tab-maintenance-modal-container {
		box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.5);
		-webkit-box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.5);
		-moz-box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.5);
		background: #fff;
		padding: 2em;
		width: 400px;
		margin: 24px auto;
		text-align: center;
	}
</style>
<div id="ct1-settings-tab-maintenance-modal">
	<div id="ct1-settings-tab-maintenance-modal-container">
		<p>
			<?php
			echo sprintf(
					$text->get( 'migration-in-progress-paragraph' ),
					'<strong>',
					'</strong>'
			);
			?>
		</p>
	</div>
</div>
