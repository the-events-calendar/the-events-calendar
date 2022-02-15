<?php
/**
 * Filter bar upsell banner.
 *
 * @since 5.14.0
 */
$main = Tribe__Events__Main::instance();
?>
<div class="tec-filterbar-upsell">
    <div class="tec-filterbar-upsell__content">
        <div class="tec-filterbar-upsell__title">
            <img
                src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar.svg', false, null, $main ) ); ?>"
                alt="<?php esc_attr_e( 'Filter Bar Icon', 'the-events-calendar' ); ?>"
            >
            <h3>
                <?php esc_html_e( 'Filter Bar', 'the-events-calendar' ); ?>
            </h3>
        </div>
        <p>
            <?php esc_html_e( 'Looking for front-end Event Filters so that your website visitors can find exactly the event they are looking for?', 'the-events-calendar' ); ?>
        </p>
        <div class="tec-filterbar-upsell__btn">
            <a href="https://evnt.is/1b31" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Check out our Filter Bar add-on', 'the-events-calendar' ); ?>
            </a>
        </div>
    </div>

    <div class="tec-filterbar-upsell__icon">
        <img
            src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar-banner.png', false, null, $main ) ); ?>"
            alt="<?php esc_attr_e( 'Filter Bar Banner Icon', 'the-events-calendar' ); ?>"
        >
    </div>
</div>