<div id="tribe-app-shop" class="wrap">

	<div class="header">
		<h1><?php esc_html_e( 'Tribe Event Add-Ons', 'the-events-calendar' ); ?></h1>
	</div>

	<div class="content-wrapper">
		<?php

		if ( ! empty( $banner ) ) {
			$banner_markup = '';
			if ( property_exists( $banner, 'top_banner_url' ) && ! empty( $banner->top_banner_url ) ) {
				$banner_markup = sprintf( "<img src='%s'/>", esc_url( $banner->top_banner_url ) );
			}
			if ( property_exists( $banner, 'top_banner_link' ) && ! empty( $banner->top_banner_link ) ) {
				$banner_markup = sprintf( "<a href='%s' target='_blank'>%s</a>", esc_url( $banner->top_banner_link ), $banner_markup );
			}
			echo $banner_markup;
		}

		$category = null;
		$i = 1;
		foreach ( (array) $products as $product ) {

		?>

		<?php if ( $product->category != $category ) { ?>

			<?php if ( $category !== null ) : ?>
				</div>
			<?php endif; ?>

	<div class="addon-grid">

		<?php
		$category = $product->category;
		} ?>
		<div class="tribe-addon<?php if ( $i == 1 ) {
			echo ' first tribe-clearfix';
		} ?>">
			<div class="thumb">
				<a href="<?php echo esc_url( $product->permalink ); ?>"><img src="<?php echo esc_attr( $product->featured_image_url ); ?>" /></a>
			</div>
			<div class="caption">
				<h4><a href="<?php echo esc_url( $product->permalink ); ?>"><?php echo $product->title; ?></a></h4>

				<div class="description">
					<p><?php echo $product->description; ?></p>
				</div>
				<div class="meta">
					<?php
					if ( $product->version ) {
						echo sprintf( '<strong>%s</strong>: %s<br/>', esc_html__( 'Version', 'the-events-calendar' ), esc_html( $product->version ) );
					}
					if ( $product->last_update ) {
						echo sprintf( '<strong>%s</strong>: %s<br/>', esc_html__( 'Last Update', 'the-events-calendar' ), esc_html( $product->last_update ) );
					}
					?>
				</div>
				<a class="button button-primary" href="<?php echo esc_url( $product->permalink ); ?>">Get This Add-on</a>
			</div>
		</div>

		<?php $i ++;
		} ?>
	</div>
</div>
