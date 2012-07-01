<div id="tribe-app-shop" class="wrap">

	<div class="header">
		<h2><?php _e( 'Tribe Event Add-Ons', 'tribe-events-calendar' ); ?></h2>
	</div>


<div class="content-wrapper">
	<?php

	if ( $banner ) {
		$banner_markup = "";
		if ( property_exists( $banner, 'top_banner_url' ) && $banner->top_banner_url ) {
			$banner_markup = sprintf( "<img src='%s'/>", $banner->top_banner_url );
		}
		if ( property_exists( $banner, 'top_banner_link' ) && $banner->top_banner_link ) {
			$banner_markup = sprintf( "<a href='%s' target='_blank'>%s</a>", $banner->top_banner_link, $banner_markup );
		}
		echo $banner_markup;
	}

	?>
	<?php
	$category = NULL;

	foreach ( (array)$products as $product ) {

		?>

		<?php if ( $product->category != $category ) { ?>

			<?php if ( $category !== NULL ) { ?></div><?php } ?>

			<div class="category-title">
				<h3><?php echo $product->category; ?></h3>
			</div>
				<div class="addon-grid">

		<?php
			$category = $product->category;
		} ?>

		<div class="tribe-addon">
			<div class="thumb">
				<a href="<?php echo $product->permalink;?>"><img src="<?php echo $product->featured_image_url;?>"/></a>
			</div>
			<div class="caption">
				<h4><a href="<?php echo $product->permalink;?>"><?php echo $product->title;?></a></h4>

				<div class="description">
					<p><?php echo $product->description;?></p>
				</div>
				<div class="meta">
					<?php
					if ( $product->version ) {
						echo sprintf( "<strong>%s</strong>: %s<br/>", __( 'Version', 'tribe-events-calendar' ), $product->version );
					}
					if ( $product->last_update ) {
						echo sprintf( "<strong>%s</strong>: %s<br/>", __( 'Last Update', 'tribe-events-calendar' ), $product->last_update );
					}
					?>
				</div>
			</div>
		</div>

		<?php }?>
</div>
</div>
</div>