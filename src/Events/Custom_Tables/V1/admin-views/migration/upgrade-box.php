<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since TBD
 *
 * @var string            $phase The current Migration phase the site is in.
 * @var Upgrade_Tab       $tab   The Tab we are hosting this template.
 * @var String_Dictionary $text  Instance of our Dictionary of String.
 */

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;

?>
<div id="tec-ct1-upgrade-box">
	<div id="tec-ct1-upgrade-dynamic" class="tec-ct1-upgrade">
		<?php // @todo Do we want a spinner here? ?>
		<div class="tec-ct1-upgrade__row">
			<div class="content-container">
				<p><?php echo esc_html( $text->get( 'loading-message' ) ); ?></p>
			</div>
		</div>
	</div>
</div>

