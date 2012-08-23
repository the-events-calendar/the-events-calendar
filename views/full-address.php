<?php
/**
* Render an address.  This is used by default in the single event view.
*
* You can customize this view by putting a replacement file of the same name (full-address.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
  if( !isset($postId) ) $postId = null;
?>
<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<?php $address_out = Array() ?>
	<?php if( isset($includeVenueName) && $includeVenueName && tribe_get_venue( $postId ) ) { ?>
		<?php $address_out []= '<span itemprop="addressLocality">' . tribe_get_venue( $postId ) .'</span>'; ?>
	<?php } ?>
	
	<?php if( tribe_get_address( $postId ) ) { ?>
		<?php $address_out []= '<span itemprop="streetAddress">' . tribe_get_address( $postId ) . '</span>'; ?>
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
		<?php $address_out []= '<span itemprop="addressRegion">' . $cityregion . '</span>'; ?>
	<?php } ?>

	<?php if( tribe_get_zip( $postId ) ) { ?>
		<?php $address_out []= '<span itemprop="postalCode">' . tribe_get_zip( $postId ) . '</span>'; ?>
	<?php } ?>

	<?php if( tribe_get_country( $postId ) ) { ?>
		<?php $address_out []= '<span itemprop="addressCountry">' . tribe_get_country( $postId ) . '</span>'; ?>
	<?php } ?>

	<?php if ( count( $address_out ) > 0 ) : ?>
	<?php echo implode( ', ', $address_out ); ?>
	<?php endif; ?>
</div>
