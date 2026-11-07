<?php
/**
 * Endereços no detalhe do pedido (PT).
 *
 * @package Vesta
 * @var WC_Order $order
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>

<section class="woocommerce-customer-details vesta-order-customer">
	<?php if ($show_shipping) : ?>
		<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
			<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
	<?php endif; ?>

	<h2 class="woocommerce-column__title"><?php esc_html_e('Endereço de cobrança', 'vesta'); ?></h2>
	<address>
		<?php echo wp_kses_post($order->get_formatted_billing_address(__('Nenhum endereço de cobrança informado.', 'vesta'))); ?>
		<?php if ($order->get_billing_phone()) : ?>
			<p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_billing_phone()); ?></p>
		<?php endif; ?>
		<?php if ($order->get_billing_email()) : ?>
			<p class="woocommerce-customer-details--email"><?php echo esc_html($order->get_billing_email()); ?></p>
		<?php endif; ?>
	</address>

	<?php if ($show_shipping) : ?>
			</div>
			<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
				<h2 class="woocommerce-column__title"><?php esc_html_e('Endereço de entrega', 'vesta'); ?></h2>
				<address>
					<?php echo wp_kses_post($order->get_formatted_shipping_address(__('Nenhum endereço de entrega informado.', 'vesta'))); ?>
					<?php if ($order->get_shipping_phone()) : ?>
						<p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_shipping_phone()); ?></p>
					<?php endif; ?>
				</address>
			</div>
		</section>
	<?php endif; ?>

	<?php do_action('woocommerce_order_details_after_customer_details', $order); ?>
</section>
