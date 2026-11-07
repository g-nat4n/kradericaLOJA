<?php
declare(strict_types=1);
if (! defined('ABSPATH') || ! function_exists('WC')) {
	return;
}
?>
<div class="vesta-drawer" data-cart-drawer hidden>
	<button type="button" class="vesta-drawer__overlay" data-cart-close aria-label="<?php esc_attr_e('Fechar sacola', 'vesta'); ?>"></button>
	<aside class="vesta-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Sacola', 'vesta'); ?>">
		<div class="vesta-drawer__header">
			<div>
				<p class="vesta-drawer__kicker"><?php esc_html_e('Sua seleção', 'vesta'); ?></p>
				<h2 class="vesta-drawer__title"><?php esc_html_e('Sacola Kra de Rica', 'vesta'); ?></h2>
			</div>
			<button type="button" class="vesta-drawer__close" data-cart-close aria-label="<?php esc_attr_e('Fechar', 'vesta'); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>
		<div class="vesta-drawer__body">
			<?php vesta_render_cart_drawer_body(); ?>
		</div>
		<div class="vesta-drawer__sand" aria-hidden="true"></div>
		<div class="vesta-drawer__footer">
			<?php vesta_render_cart_drawer_footer(); ?>
		</div>
	</aside>
</div>
