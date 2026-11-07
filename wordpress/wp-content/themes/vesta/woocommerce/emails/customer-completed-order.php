<?php
/**
 * E-mail: pedido concluído (PT · Vesta).
 *
 * @package Vesta
 * @var WC_Order $order
 * @var bool $sent_to_admin
 * @var bool $plain_text
 * @var WC_Email $email
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$name = trim((string) $order->get_billing_first_name());
$code = 'VS' . $order->get_order_number();

do_action('woocommerce_email_header', __('Pedido concluído', 'vesta'), $email);
?>

<p style="margin:0 0 16px;font-size:18px;">
	<?php
	echo esc_html(
		$name !== ''
			? sprintf(__('Olá, %s.', 'vesta'), $name)
			: __('Olá.', 'vesta')
	);
	?>
</p>

<p style="margin:0 0 20px;color:#d8d2c6;">
	<?php
	echo wp_kses_post(
		sprintf(
			__('O pedido <strong>%1$s</strong> foi concluído. Obrigada por escolher a Kra de Rica.', 'vesta'),
			esc_html($code)
		)
	);
	?>
</p>

<?php
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_footer', $email);
