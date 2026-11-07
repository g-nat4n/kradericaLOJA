<?php
/**
 * Checkout — layout CheckoutForm do Next:
 * esquerda: dados + frete + cupom + CTA
 * direita: resumo sticky
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_checkout_form', WC()->checkout());

if (! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && ! is_user_logged_in()) {
	echo esc_html__('Você precisa estar logado para finalizar.', 'vesta');
	return;
}

$gateways = WC()->payment_gateways()->get_available_payment_gateways();
$default_gateway = function_exists('vesta_checkout_default_gateway')
	? vesta_checkout_default_gateway()
	: '';
if ($default_gateway === '' && $gateways) {
	foreach ($gateways as $id => $gateway) {
		if (stripos((string) $id, 'mercado') !== false) {
			$default_gateway = (string) $id;
			break;
		}
	}
	if ($default_gateway === '') {
		$default_gateway = (string) array_key_first($gateways);
	}
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout vesta-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" data-payment-gateway="<?php echo esc_attr($default_gateway); ?>">

	<div class="vesta-checkout__grid">
		<div class="vesta-checkout__main" id="customer_details">
			<h2 class="vesta-checkout__section-title">Dados e entrega</h2>

			<?php do_action('woocommerce_checkout_before_customer_details'); ?>
			<div class="vesta-checkout__fields">
				<?php do_action('woocommerce_checkout_billing'); ?>
			</div>
			<?php do_action('woocommerce_checkout_after_customer_details'); ?>

			<div class="vesta-checkout__shipping" id="vesta-shipping">
				<p class="vesta-checkout__label">Envio</p>
				<div id="vesta-shipping-methods">
					<?php if (WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
						<table class="vesta-shipping-table"><tbody>
							<?php wc_cart_totals_shipping_html(); ?>
						</tbody></table>
					<?php else : ?>
						<p class="vesta-checkout__hint">Frete a combinar — alinhamos a entrega após o pedido.</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="vesta-checkout__coupon">
				<label for="vesta_checkout_coupon">Cupom</label>
				<div class="vesta-checkout__coupon-row">
					<input type="text" id="vesta_checkout_coupon" placeholder="Cupom" autocomplete="off">
					<button type="button" id="vesta_apply_coupon">Aplicar</button>
				</div>
				<p class="vesta-checkout__coupon-msg" id="vesta_coupon_msg" hidden></p>
			</div>

			<div class="vesta-checkout__cta" id="order_review">
				<?php do_action('woocommerce_checkout_before_order_review'); ?>

				<?php if ($gateways) : ?>
					<p class="vesta-checkout__label"><?php esc_html_e('Pagamento', 'vesta'); ?></p>
					<ul class="wc_payment_methods payment_methods methods vesta-pay-methods">
						<?php
						$chosen = $default_gateway !== '' && isset($gateways[$default_gateway])
							? $default_gateway
							: (string) array_key_first($gateways);
						foreach ($gateways as $gateway) {
							if ($chosen !== '' && $gateway->id === $chosen) {
								$gateway->chosen = true;
							}
							wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
						}
						?>
					</ul>
				<?php else : ?>
					<div class="woocommerce-error vesta-checkout__pay-error" role="alert">
						Nenhum método de pagamento disponível no momento. Fale conosco pelo WhatsApp.
					</div>
				<?php endif; ?>

				<?php
				// Terms ocultos visualmente (Woo ainda pode exigir via filtro).
				echo '<div class="vesta-checkout__terms-hidden">';
				wc_get_template('checkout/terms.php');
				echo '</div>';

				$btn_disabled = $default_gateway === '' ? ' disabled aria-disabled="true"' : '';
				echo apply_filters(
					'woocommerce_order_button_html',
					'<button type="submit" class="button alt btn btn--wine vesta-place-order" name="woocommerce_checkout_place_order" id="place_order" value="Finalizar pedido"' . $btn_disabled . '>Finalizar pedido</button>'
				);
				wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce');
				?>

				<?php if (function_exists('vesta_has_mercadopago') && vesta_has_mercadopago()) : ?>
					<p class="vesta-checkout__secure">PIX e cartão no site, sem precisar ter conta no Mercado Pago.</p>
				<?php else : ?>
					<p class="vesta-checkout__secure">Pagamento por transferência ou na retirada. Frete a combinar.</p>
				<?php endif; ?>
			</div>
		</div>

		<aside class="vesta-checkout__aside">
			<div class="vesta-checkout__summary">
				<h2 class="vesta-checkout__section-title">Seu pedido</h2>
				<p class="vesta-checkout__aside-sub">Confira o que você está levando</p>
				<div class="vesta-checkout__summary-body">
					<?php wc_get_template('checkout/review-order.php'); ?>
				</div>
			</div>
		</aside>
	</div>
</form>

<?php
do_action('woocommerce_after_checkout_form', WC()->checkout());
