<?php
/**
 * View: TEC 6.0.0 update notification
 *
 * @since   TBD
 *
 * @var string $title - The notification title.
 * @var string $description - The notification text.
 * @var string $upgrade_link - The link to the upgrade tab.
 * @var string $learn_link - The link to the knowledgebase article.
 */
?>

<div class="tec-update-notice">
    <h3 class="tec-update-notice__title">
        <?php echo esc_html( $title ); ?>
    </h3>
    <div class="tec-update-notice__description">
        <?php echo esc_html( $description  ); ?>
    </div>
    <div class="tec-update-notice__actions">
        <a class="tec-update-notice__button button" href="<?php echo esc_url( get_admin_url( null, $upgrade_link ) ); ?>">
            <?php esc_html_e( 'Upgrade your events', 'the-events-calendar' ); ?>
        </a>
        <a class="tec-update-notice__link" href="<?php echo esc_url( $learn_link ); ?>">
            <?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?>
        </a>
    </div>
</div>
