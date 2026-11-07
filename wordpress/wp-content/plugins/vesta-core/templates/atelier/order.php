<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$order = wc_get_order((int) ($atelier_id ?? 0));
if (! $order instanceof WC_Order) {
	echo '<p class="atelier-alert">Pedido não encontrado.</p>';
	return;
}
$statuses = function_exists('vesta_atelier_order_status_labels')
	? vesta_atelier_order_status_labels()
	: wc_get_order_statuses();
$status_label = function_exists('vesta_atelier_order_status_label')
	? vesta_atelier_order_status_label($order->get_status())
	: wc_get_order_status_name($order->get_status());
?>
<a class="atelier-back" href="<?php echo esc_url(Vesta_Core_Atelier::url('pedidos')); ?>">← Voltar aos pedidos</a>
<p class="eyebrow">Pedido #<?php echo esc_html((string) $order->get_order_number()); ?></p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;"><?php echo esc_html($status_label); ?></h1>
<p class="atelier-muted" style="margin-top:0.75rem;max-width:36rem;">
	Painel só da equipe. O cliente vê a confirmação na loja; entre em contato para combinar entrega ou retirada.
</p>

<form class="atelier-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:28rem;">
	<?php vesta_atelier_nonce_field(); ?>
	<input type="hidden" name="action" value="vesta_atelier_order_status">
	<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $order->get_id()); ?>">
	<div class="atelier-field">
		<label for="status">Situação</label>
		<select id="status" name="status">
			<?php foreach ($statuses as $key => $label) :
				$key_clean = str_replace('wc-', '', (string) $key);
				?>
				<option value="<?php echo esc_attr($key_clean); ?>" <?php selected($order->get_status(), $key_clean); ?>>
					<?php echo esc_html($label); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<button class="atelier-btn" type="submit">Atualizar situação</button>
</form>

<section class="atelier-section">
	<article class="atelier-list-item">
		<p class="eyebrow">Cliente</p>
		<h2><?php echo esc_html($order->get_formatted_billing_full_name()); ?></h2>
		<p class="atelier-muted" style="margin-top:0.5rem;"><?php echo esc_html($order->get_billing_email()); ?></p>
		<p class="atelier-muted"><?php echo esc_html($order->get_billing_phone()); ?></p>
		<?php
		$cpf = $order->get_meta('_billing_cpf');
		if ($cpf) :
			?>
			<p class="atelier-muted">CPF <?php echo esc_html((string) $cpf); ?></p>
		<?php endif; ?>
	</article>
</section>

<section class="atelier-section">
	<article class="atelier-list-item">
		<p class="eyebrow">Endereço e frete</p>
		<p style="margin-top:0.75rem;"><?php echo wp_kses_post($order->get_formatted_billing_address()); ?></p>
		<p class="atelier-muted" style="margin-top:0.75rem;">
			<?php echo esc_html($order->get_shipping_method() ?: '—'); ?>
		</p>
	</article>
</section>

<section class="atelier-section">
	<article class="atelier-list-item">
		<p class="eyebrow">Itens</p>
		<ul class="atelier-order-items">
			<?php foreach ($order->get_items() as $item) :
				if (! $item instanceof WC_Order_Item_Product) {
					continue;
				}
				$product = $item->get_product();
				$thumb   = '';
				if ($product instanceof WC_Product && $product->get_image_id()) {
					$thumb = wp_get_attachment_image((int) $product->get_image_id(), 'thumbnail', false, ['class' => 'atelier-order-items__img']);
				}
				if ($thumb === '') {
					$thumb = '<span class="atelier-order-items__ph" aria-hidden="true">·</span>';
				}
				?>
				<li class="atelier-order-items__row">
					<div class="atelier-order-items__thumb"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="atelier-order-items__meta">
						<strong><?php echo esc_html($item->get_name()); ?></strong>
						<span class="atelier-muted">Qtd. <?php echo esc_html((string) $item->get_quantity()); ?></span>
					</div>
					<span class="atelier-order-items__price"><?php echo esc_html(vesta_atelier_format_brl((float) $item->get_total())); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="atelier-card__price" style="margin-top:1rem;font-size:1.2rem;">
			Total do pedido <?php echo esc_html(vesta_atelier_format_brl((float) $order->get_total())); ?>
		</p>
		<?php if ($order->is_paid()) : ?>
			<p class="atelier-muted" style="margin-top:0.5rem;">Pagamento confirmado · <?php echo esc_html($order->get_payment_method_title() ?: 'Mercado Pago'); ?></p>
		<?php endif; ?>
	</article>
</section>
