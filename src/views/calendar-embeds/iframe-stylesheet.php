<?php
/**
 * Iframe stylesheet for a calendar embed.
 *
 * @since 6.11.0
 *
 * @version 6.11.0
 */

defined( 'ABSPATH' ) || exit;
?>
<style scoped>
	iframe[data-tec-events-ece-iframe="true"] {
		width: 100%;
		height: calc( 100vw + 100px );
		max-width: 100%;
	}

	@media screen and (min-width: 600px) {
		iframe[data-tec-events-ece-iframe="true"] {
			height: 100vw;
		}
	}

	@media screen and (min-width: 853px) {
		iframe[data-tec-events-ece-iframe="true"] {
			height: 1065px;
		}
	}
</style>
