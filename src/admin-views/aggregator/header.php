<h1><?php echo esc_html( $this->get_page_title() ); ?></h1>
<h2 class="nav-tab-wrapper">
	<?php foreach ( $this->tabs->get() as $tab ): ?>
	<a id="<?php echo esc_attr( $tab->get_slug() ); ?>" class="nav-tab<?php echo ( $tab->is_active() ? ' nav-tab-active' : '' ); ?>" href="<?php echo $tab->get_url(); ?>"><?php echo $tab->get_label(); ?></a>
	<?php endforeach; ?>
</h2>