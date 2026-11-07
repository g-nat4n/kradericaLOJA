<?php
/**
 * Campos de cobrança — ordem e grupos iguais ao CheckoutForm do Next.
 *
 * @package Vesta
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$checkout = WC()->checkout();
$all      = $checkout->get_checkout_fields('billing');

$render = static function (string $key) use ($checkout, $all): void {
	if (empty($all[$key])) {
		return;
	}
	woocommerce_form_field($key, $all[$key], $checkout->get_value($key));
};
?>
<div class="woocommerce-billing-fields vesta-billing">
	<?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

	<div class="vesta-billing__stack">
		<?php $render('billing_first_name'); ?>
		<?php $render('billing_email'); ?>

		<div class="vesta-billing__row vesta-billing__row--2">
			<?php $render('billing_cpf'); ?>
			<?php $render('billing_phone'); ?>
		</div>

		<?php $render('billing_postcode'); ?>
		<?php $render('billing_address_1'); ?>

		<div class="vesta-billing__row vesta-billing__row--3">
			<?php $render('billing_number'); ?>
			<?php $render('billing_complement'); ?>
			<?php $render('billing_neighborhood'); ?>
		</div>

		<div class="vesta-billing__row vesta-billing__row--2">
			<?php $render('billing_city'); ?>
			<?php $render('billing_state'); ?>
		</div>

		<?php
		// País oculto (sempre BR).
		$render('billing_country');

		// Qualquer campo extra que plugins adicionem.
		$done = [
			'billing_first_name', 'billing_email', 'billing_cpf', 'billing_phone',
			'billing_postcode', 'billing_address_1', 'billing_number', 'billing_complement',
			'billing_neighborhood', 'billing_city', 'billing_state', 'billing_country',
			'billing_last_name', 'billing_company', 'billing_address_2',
		];
		foreach ($all as $key => $field) {
			if (in_array($key, $done, true)) {
				continue;
			}
			woocommerce_form_field($key, $field, $checkout->get_value($key));
		}
		?>
	</div>

	<?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
</div>
<?php
// Sem "Criar uma conta?" no checkout — igual ao Next (cadastro é /cadastro).
