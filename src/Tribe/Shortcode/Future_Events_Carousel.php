<?php
/**
 * Provides a shortcode to render upcoming events in a carousel layout.
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
// phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase

/**
 * Setup the Future Events Carousel shortcode.
 *
 * Usage: [tribe:future-events-carousel limit="5"]
 *
 * @since TBD
 */
class Tribe__Events__Shortcode__Future_Events_Carousel {
	/**
	 * Tracks if styles and scripts were already printed.
	 *
	 * @since TBD
	 * @var bool
	 */
	private static $did_print_assets = false;

	/**
	 * Add hooks.
	 *
	 * @since TBD
	 * @return void
	 */
	public function hook() {
		add_action( 'init', [ $this, 'add_shortcode' ] );
	}

	/**
	 * Namespace for shortcode.
	 *
	 * @since TBD
	 * @var string
	 */
	private $nspace = 'tribe';

	/**
	 * Shortcode slug.
	 *
	 * @since TBD
	 * @var string
	 */
	private $slug = 'future-events-carousel';

	/**
	 * Build shortcode tag.
	 *
	 * @since TBD
	 * @return string
	 */
	public function get_shortcode_tag() {
		$nspace = apply_filters( 'tribe_events_shortcode_namespace', $this->nspace, __CLASS__, $this );
		$slug   = $this->get_shortcode_slug();
		$tag    = sanitize_title_with_dashes( $nspace ) . ':' . sanitize_title_with_dashes( $slug );

		return apply_filters( 'tribe_events_shortcode_tag', $tag, __CLASS__, $this );
	}

	/**
	 * Gets shortcode slug.
	 *
	 * @since TBD
	 * @return string
	 */
	public function get_shortcode_slug() {
		return apply_filters( 'tribe_events_shortcode_slug', $this->slug, __CLASS__, $this );
	}

	/**
	 * Register shortcode.
	 *
	 * @since TBD
	 * @return void
	 */
	public function add_shortcode() {
		add_shortcode( $this->get_shortcode_tag(), [ $this, 'do_shortcode' ] );
	}

	/**
	 * Render shortcode.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $args Shortcode args.
	 *
	 * @return string
	 */
	public function do_shortcode( $args ) {
		$tag = $this->get_shortcode_tag();

		$args = shortcode_atts(
			[
				'limit' => 5,
			],
			$args,
			$tag
		);

		$limit = max( 1, absint( $args['limit'] ) );

		$query = new WP_Query(
			[
				'post_type'      => Tribe__Events__Main::POSTTYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_key'       => '_EventStartDate',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => [
					[
						'key'     => '_EventStartDate',
						'value'   => current_time( 'mysql' ),
						'compare' => '>=',
						'type'    => 'DATETIME',
					],
				],
			]
		);

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No upcoming events found.', 'the-events-calendar' ) . '</p>';
		}

		$carousel_id = 'tec-future-events-carousel-' . wp_unique_id();

		ob_start();
		?>
		<div class="tec-future-events-carousel" id="<?php echo esc_attr( $carousel_id ); ?>">
			<div class="tec-future-events-carousel__track-wrap">
				<div class="tec-future-events-carousel__track">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						?>
						<article class="tec-future-events-carousel__item">
							<h3 class="tec-future-events-carousel__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="tec-future-events-carousel__date"><?php echo esc_html( tribe_get_start_date( get_the_ID(), false, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></p>
						</article>
						<?php
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
			<div class="tec-future-events-carousel__controls">
				<button type="button" class="tec-future-events-carousel__button" data-direction="prev"><?php esc_html_e( 'Previous', 'the-events-calendar' ); ?></button>
				<button type="button" class="tec-future-events-carousel__button" data-direction="next"><?php esc_html_e( 'Next', 'the-events-calendar' ); ?></button>
			</div>
		</div>
		<?php

		if ( ! self::$did_print_assets ) {
			self::$did_print_assets = true;
			$this->print_assets();
		}

		return (string) ob_get_clean();
	}

	/**
	 * Print scoped styles and behavior once.
	 *
	 * @since TBD
	 * @return void
	 */
	private function print_assets() {
		?>
		<style>
			.tec-future-events-carousel{width:100%;max-width:100%}
			.tec-future-events-carousel__track-wrap{width:100%;overflow:hidden}
			.tec-future-events-carousel__track{display:flex;width:100%;transition:transform .3s ease}
			.tec-future-events-carousel__item{flex:0 0 100%;width:100%;max-width:100%;max-height:300px;padding:16px;border:1px solid #ddd;border-radius:8px;background:#fff;box-sizing:border-box;overflow:hidden}
			.tec-future-events-carousel__controls{display:flex;gap:8px;margin-top:12px}
			.tec-future-events-carousel__button{cursor:pointer}

			@media (min-width: 901px) {
				.tec-future-events-carousel__item{flex:0 0 25%;width:25%;max-width:25%}
			}
		</style>
		<script>
			document.addEventListener('DOMContentLoaded',function(){
				document.querySelectorAll('.tec-future-events-carousel').forEach(function(carousel){
					var track=carousel.querySelector('.tec-future-events-carousel__track');
					if(!track){return;}
					var items=track.querySelectorAll('.tec-future-events-carousel__item');
					var buttons=carousel.querySelectorAll('.tec-future-events-carousel__button');
					var index=0;
					var getVisibleItems=function(){
						return window.innerWidth > 900 ? 4 : 1;
					};
					var getMaxIndex=function(){
						return Math.max(items.length - getVisibleItems(), 0);
					};
					var update=function(){
						var visibleItems=getVisibleItems();
						var maxIndex=getMaxIndex();
						var translateStep=100 / visibleItems;
						index=Math.min(index,maxIndex);
						track.style.transform='translateX(' + (-index * translateStep) + '%)';
						buttons.forEach(function(btn){
							btn.disabled=maxIndex===0;
						});
					};

					buttons.forEach(function(btn){
						btn.addEventListener('click',function(){
							var maxIndex=getMaxIndex();
							if(btn.dataset.direction==='next'){
								index=index >= maxIndex ? 0 : index + 1;
							}else{
								index=index <= 0 ? maxIndex : index - 1;
							}
							update();
						});
					});

					window.addEventListener('resize',update);
					update();
				});
			});
		</script>
		<?php
	}
}
