<?php
/**
 * Resumo do pedido (apenas itens + totais) — CTA e pagamento ficam à esquerda.
 *
 * @package Vesta
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<ul class="vesta-order-items">
	<?php
	foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
		$product = $cart_item['data'];
		if (! $product instanceof WC_Product || ! $product->exists() || $cart_item['quantity'] <= 0) {
			continue;
		}
		$product_id = (int) $cart_item['product_id'];
		$brand      = (string) get_post_meta($product_id, '_vesta_brand', true);
		$size       = (string) get_post_meta($product_id, '_vesta_size', true);
		$unique     = get_post_meta($product_id, '_vesta_unique_piece', true) !== 'no';
		$available  = $product->is_in_stock();
		?>
		<li class="vesta-order-item">
			<div class="vesta-order-item__thumb">
				<?php echo $product->get_image('woocommerce_thumbnail'); // phpcs:ignore ?>
			</div>
			<div class="vesta-order-item__body">
				<div class="vesta-order-item__top">
					<div>
						<?php if ($brand) : ?><p class="vesta-order-item__brand"><?php echo esc_html($brand); ?></p><?php endif; ?>
						<p class="vesta-order-item__name"><?php echo esc_html($product->get_name()); ?></p>
						<?php if ($size) : ?><p class="vesta-order-item__size"><?php echo esc_html('Tam. ' . $size); ?></p><?php endif; ?>
					</div>
					<button type="button" class="vesta-order-item__remove" data-remove-cart="<?php echo esc_attr($cart_item_key); ?>" aria-label="Remover">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
					</button>
				</div>
				<p class="vesta-order-item__stock <?php echo $available ? 'is-ok' : 'is-bad'; ?>">
					<?php
					echo esc_html(! $available ? 'Indisponível' : ($unique ? 'Disponível · peça única' : 'Disponível'));
					?>
				</p>
				<div class="vesta-order-item__bottom">
					<?php if (! $unique && $available) : ?>
						<div class="vesta-qty" data-qty="<?php echo esc_attr($cart_item_key); ?>">
							<button type="button" data-qty-minus>−</button>
							<span><?php echo esc_html((string) $cart_item['quantity']); ?></span>
							<button type="button" data-qty-plus>+</button>
						</div>
					<?php else : ?>
						<span class="vesta-qty-fixed">Qtd. <?php echo esc_html((string) $cart_item['quantity']); ?></span>
					<?php endif; ?>
					<strong><?php echo wp_kses_post(WC()->cart->get_product_subtotal($product, (int) $cart_item['quantity'])); ?></strong>
				</div>
			</div>
		</li>
		<?php
	}
	?>
</ul>

<dl class="vesta-order-totals">
	<div>
		<dt><?php esc_html_e('Subtotal', 'vesta'); ?></dt>
		<dd><?php wc_cart_totals_subtotal_html(); ?></dd>
	</div>
	<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
		<div>
			<dt>Descontos (<?php echo esc_html($code); ?>)</dt>
			<dd><?php wc_cart_totals_coupon_html($coupon); ?></dd>
		</div>
	<?php endforeach; ?>
	<?php if (! WC()->cart->get_coupons()) : ?>
		<div><dt>Descontos</dt><dd>- <?php echo wp_kses_post(wc_price(0)); ?></dd></div>
	<?php endif; ?>
	<div>
		<dt>Frete</dt>
		<dd>
			<?php
			$packages = WC()->shipping()->get_packages();
			$chosen   = WC()->session ? WC()->session->get('chosen_shipping_methods') : [];
			$ship     = 0.0;
			if ($packages && ! empty($chosen[0]) && ! empty($packages[0]['rates'][$chosen[0]])) {
				$ship = (float) $packages[0]['rates'][$chosen[0]]->get_cost();
			}
			echo wp_kses_post(wc_price($ship));
			?>
		</dd>
	</div>
	<div class="vesta-order-totals__total">
		<dt><?php esc_html_e('Total', 'vesta'); ?></dt>
		<dd><?php wc_cart_totals_order_total_html(); ?></dd>
	</div>
</dl>
