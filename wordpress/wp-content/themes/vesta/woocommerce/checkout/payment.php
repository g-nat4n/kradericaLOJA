<?php
/**
 * Payment — no checkout Vesta o CTA fica no form-checkout (igual Next).
 * Este template só existe para fragments AJAX do Woo não quebrarem.
 *
 * @package Vesta
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$available = WC()->payment_gateways()->get_available_payment_gateways();
$default   = function_exists('vesta_checkout_default_gateway') ? vesta_checkout_default_gateway() : '';
?>
<div id="payment" class="woocommerce-checkout-payment vesta-checkout-payment">
	<?php if ($available) : ?>
		<ul class="wc_payment_methods payment_methods methods vesta-pay-methods">
			<?php
			foreach ($available as $gateway) {
				if ($default !== '' && $gateway->id === $default) {
					$gateway->chosen = true;
				}
				wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
			}
			?>
		</ul>
	<?php endif; ?>
	<div class="form-row place-order">
		<?php echo apply_filters('woocommerce_order_button_html', ''); // phpcs:ignore ?>
		<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
	</div>
</div>
