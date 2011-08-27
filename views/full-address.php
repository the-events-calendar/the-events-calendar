<?php
/**
* Render an address
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>

<div itemprop="location" itemscope itemtype="http://schema.org/Place">
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<?php if( $includeVenueName && tribe_get_venue( $postId ) ) { ?>
			<span itemprop="addressLocality"><?php tribe_get_venue( $postId ); ?></span>
		<?php } ?>
		
		<?php if( tribe_get_address( $postId ) ) { ?>
			<span itemprop="streetAddress"><?php echo tribe_get_address( $postId ); ?></span>
		<?php } ?>

		<?php
		$cityregion = '';
		if( tribe_get_city( $postId ) ) {
			$cityregion .= tribe_get_city( $postId );
		}
		if( tribe_get_region( $postId ) ) {
			if( $cityregion != '' ) $cityregion .= ', ';
			$cityregion .= tribe_get_region( $postId );
		}
		if( $cityregion != '' ) { ?>
			<span itemprop="addressRegion"><?php echo $cityregion; ?></span>
		<?php } ?>

		<?php if( tribe_get_zip( $postId ) ) { ?>
			<span itemprop="postalCode"><?php echo tribe_get_zip( $postId ); ?></span>
		<?php } ?>

		<?php if( tribe_get_country( $postId ) ) { ?>
			<span itemprop="addressCountry"><?php echo tribe_get_country( $postId ); ?></span>
		<?php } ?>
	</div>
</div>