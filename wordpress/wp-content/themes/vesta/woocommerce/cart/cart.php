<?php
/**
 * Sacola — layout alinhado ao CartView do Next.
 *
 * @package Vesta
 * @version 1.1.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

$cart = WC()->cart;
?>
<section class="vesta-cart container-main">
	<div class="vesta-cart__main">
		<p class="eyebrow"><?php esc_html_e('Sua seleção', 'vesta'); ?></p>
		<h1 class="display vesta-cart__heading"><?php esc_html_e('Sacola', 'vesta'); ?></h1>

		<?php if (! $cart || $cart->is_empty()) : ?>
			<p class="vesta-cart__empty">
				<?php esc_html_e('Nenhuma peça por aqui.', 'vesta'); ?>
				<a href="<?php echo esc_url(home_url('/produtos/')); ?>"><?php esc_html_e('Ver coleção', 'vesta'); ?></a>
			</p>
		<?php else : ?>
			<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
				<?php do_action('woocommerce_before_cart_table'); ?>
				<ul class="vesta-cart__list">
					<?php foreach ($cart->get_cart() as $cart_item_key => $cart_item) :
						$product = $cart_item['data'];
						if (! $product instanceof WC_Product || ! $product->exists() || $cart_item['quantity'] <= 0) {
							continue;
						}
						$product_id = (int) $cart_item['product_id'];
						$brand      = (string) get_post_meta($product_id, '_vesta_brand', true);
						$size       = (string) get_post_meta($product_id, '_vesta_size', true);
						$permalink  = $product->is_visible() ? $product->get_permalink($cart_item) : '';
						?>
						<li class="vesta-cart__item">
							<div class="vesta-cart__thumb">
								<?php
								$thumbnail = $product->get_image('woocommerce_thumbnail');
								echo $permalink ? '<a href="' . esc_url($permalink) . '">' . $thumbnail . '</a>' : $thumbnail; // phpcs:ignore
								?>
							</div>
							<div class="vesta-cart__info">
								<?php if ($brand) : ?>
									<p class="vesta-cart__brand"><?php echo esc_html($brand); ?></p>
								<?php endif; ?>
								<?php if ($permalink) : ?>
									<a class="vesta-cart__name" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($product->get_name()); ?></a>
								<?php else : ?>
									<span class="vesta-cart__name"><?php echo esc_html($product->get_name()); ?></span>
								<?php endif; ?>
								<p class="vesta-cart__meta">
									<?php
									$bits = [];
									if ($size) {
										$bits[] = sprintf(__('Tam. %s', 'vesta'), $size);
									}
									$bits[] = sprintf(__('Qtd. %d', 'vesta'), (int) $cart_item['quantity']);
									echo esc_html(implode(' · ', $bits));
									?>
								</p>
								<p class="vesta-cart__price">
									<?php echo wp_kses_post($cart->get_product_subtotal($product, (int) $cart_item['quantity'])); ?>
								</p>
							</div>
							<button type="button" class="vesta-remove-btn" data-remove-cart="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('Remover da sacola', 'vesta'); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php do_action('woocommerce_cart_contents'); ?>
				<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
				<?php do_action('woocommerce_after_cart_table'); ?>
			</form>
		<?php endif; ?>
	</div>

	<aside class="vesta-cart__summary">
		<h2 class="display"><?php esc_html_e('Resumo', 'vesta'); ?></h2>

		<form class="vesta-cart__coupon" data-cart-coupon>
			<label for="vesta_coupon"><?php esc_html_e('Cupom', 'vesta'); ?></label>
			<input id="vesta_coupon" type="text" name="coupon" placeholder="Cupom" autocomplete="off">
			<button type="submit"><?php esc_html_e('Aplicar', 'vesta'); ?></button>
			<p class="vesta-cart__coupon-msg" data-coupon-msg hidden></p>
		</form>

		<dl class="vesta-cart__totals">
			<div>
				<dt><?php esc_html_e('Subtotal', 'vesta'); ?></dt>
				<dd data-cart-subtotal><?php echo wp_kses_post($cart ? $cart->get_cart_subtotal() : wc_price(0)); ?></dd>
			</div>
			<div>
				<dt><?php esc_html_e('Desconto', 'vesta'); ?></dt>
				<dd data-cart-discount>- <?php echo wp_kses_post(wc_price($cart ? $cart->get_discount_total() : 0)); ?></dd>
			</div>
			<div>
				<dt><?php esc_html_e('Frete', 'vesta'); ?></dt>
				<dd><?php esc_html_e('Calcular no checkout', 'vesta'); ?></dd>
			</div>
			<div class="vesta-cart__total">
				<dt><?php esc_html_e('Total', 'vesta'); ?></dt>
				<dd data-cart-total><?php echo wp_kses_post($cart ? $cart->get_total() : wc_price(0)); ?></dd>
			</div>
		</dl>

		<?php if ($cart && ! $cart->is_empty()) : ?>
			<a class="btn btn--wine vesta-cart__checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
				<?php esc_html_e('Finalizar compra', 'vesta'); ?>
			</a>
		<?php endif; ?>
	</aside>
</section>
<?php
do_action('woocommerce_after_cart');
