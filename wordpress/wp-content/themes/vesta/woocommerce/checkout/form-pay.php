<?php
/**
 * Pagar pedido (order-pay) — layout Vesta + voltar.
 *
 * @package Vesta
 * @var WC_Order $order
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$totals = $order->get_order_item_totals();
$back_url = wc_get_cart_url() ?: home_url('/');
if (function_exists('wc_get_checkout_url')) {
	$checkout = wc_get_checkout_url();
	if ($checkout) {
		$back_url = $checkout;
	}
}
?>

<div class="vesta-pay">
	<header class="vesta-pay__head">
		<p class="vesta-pay__brand">VESTA</p>
		<p class="vesta-pay__eyebrow"><?php esc_html_e('Pagamento', 'vesta'); ?></p>
	</header>

	<p class="vesta-pay__lead">
		<?php esc_html_e('Finalize o pagamento do pedido. Se algo travar, use Voltar à loja.', 'vesta'); ?>
	</p>

	<ul class="vesta-pay__meta">
		<li>
			<span><?php esc_html_e('Pedido', 'vesta'); ?></span>
			<strong>#<?php echo esc_html($order->get_order_number()); ?></strong>
		</li>
		<li>
			<span><?php esc_html_e('Data', 'vesta'); ?></span>
			<strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
		</li>
		<li>
			<span><?php esc_html_e('Total', 'vesta'); ?></span>
			<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
		</li>
	</ul>

	<form id="order_review" method="post" class="vesta-pay__form">
		<table class="shop_table vesta-pay__table">
			<thead>
				<tr>
					<th class="product-name"><?php esc_html_e('Produto', 'vesta'); ?></th>
					<th class="product-quantity"><?php esc_html_e('Qtd.', 'vesta'); ?></th>
					<th class="product-total"><?php esc_html_e('Total', 'vesta'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($order->get_items()) > 0) : ?>
					<?php foreach ($order->get_items() as $item_id => $item) : ?>
						<?php
						if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
							continue;
						}
						?>
						<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
							<td class="product-name">
								<?php
								echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));
								do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
								wc_display_item_meta($item);
								do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
								?>
							</td>
							<td class="product-quantity"><?php echo esc_html((string) $item->get_quantity()); ?></td>
							<td class="product-total"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<?php if ($totals) : ?>
					<?php foreach ($totals as $total) : ?>
						<tr>
							<th scope="row" colspan="2"><?php echo wp_kses_post($total['label']); ?></th>
							<td class="product-total"><?php echo wp_kses_post($total['value']); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tfoot>
		</table>

		<div id="payment" class="vesta-pay__payment">
			<?php if ($order->needs_payment()) : ?>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
					if (! empty($available_gateways)) {
						foreach ($available_gateways as $gateway) {
							wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html(apply_filters('woocommerce_no_available_payment_methods_message', __('Não há métodos de pagamento disponíveis. Fale conosco para concluir.', 'vesta'))) . '</li>';
					}
					?>
				</ul>
			<?php endif; ?>

			<div class="form-row vesta-pay__actions">
				<input type="hidden" name="woocommerce_pay" value="1">
				<?php wc_get_template('checkout/terms.php'); ?>
				<?php do_action('woocommerce_pay_order_before_submit'); ?>
				<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt vesta-pay__btn" id="place_order" value="' . esc_attr__('Pagar agora', 'vesta') . '" data-value="' . esc_attr__('Pagar agora', 'vesta') . '">' . esc_html__('Pagar agora', 'vesta') . '</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php do_action('woocommerce_pay_order_after_submit'); ?>
				<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
			</div>
		</div>
	</form>

	<p class="vesta-pay__back">
		<a href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('← Voltar ao checkout', 'vesta'); ?></a>
		<span>·</span>
		<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Voltar à loja', 'vesta'); ?></a>
	</p>
</div>
