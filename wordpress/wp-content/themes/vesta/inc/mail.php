<?php
/**
 * Entrega de e-mails da loja (From + confirmação de pedido).
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/** Remetente alinhado ao domínio (evita wordpress@ e spam). */
add_filter('wp_mail_from', static function (): string {
	$from = (string) get_option('woocommerce_email_from_address', '');
	if ($from === '' || ! is_email($from)) {
		$from = (string) get_option('admin_email');
	}
	return is_email($from) ? $from : 'vesta@vestabrasil.com.br';
}, 20);

add_filter('wp_mail_from_name', static function (): string {
	$name = (string) get_option('woocommerce_email_from_name', '');
	if ($name === '') {
		$name = (string) get_option('blogname', 'Kra de Rica');
	}
	return $name !== '' ? $name : 'Kra de Rica';
}, 20);

/** Assunto / headings — valores reais (placeholders crus não são substituídos após o filter). */
function vesta_email_site_name(): string {
	$name = wp_specialchars_decode((string) get_bloginfo('name'), ENT_QUOTES);
	return $name !== '' ? $name : 'Kra de Rica';
}

function vesta_email_order_number($order): string {
	return $order instanceof WC_Order ? (string) $order->get_order_number() : '';
}

add_filter('woocommerce_email_subject_customer_on_hold_order', static function ($subject, $order = null): string {
	return sprintf('[%s]: recebemos seu pedido #%s', vesta_email_site_name(), vesta_email_order_number($order));
}, 20, 2);
add_filter('woocommerce_email_heading_customer_on_hold_order', static function (): string {
	return 'Compra confirmada';
});
add_filter('woocommerce_email_subject_customer_processing_order', static function ($subject, $order = null): string {
	return sprintf('[%s]: pagamento confirmado #%s', vesta_email_site_name(), vesta_email_order_number($order));
}, 20, 2);
add_filter('woocommerce_email_heading_customer_processing_order', static function (): string {
	return 'Compra confirmada';
});
add_filter('woocommerce_email_subject_customer_completed_order', static function ($subject, $order = null): string {
	return sprintf('[%s]: pedido concluído #%s', vesta_email_site_name(), vesta_email_order_number($order));
}, 20, 2);
add_filter('woocommerce_email_heading_customer_completed_order', static function (): string {
	return 'Pedido concluído';
});
add_filter('woocommerce_email_subject_new_order', static function ($subject, $order = null): string {
	return sprintf('[%s]: novo pedido #%s', vesta_email_site_name(), vesta_email_order_number($order));
}, 20, 2);

/** Textos padrão do Woo que ainda vazam em inglês nos e-mails. */
add_filter('gettext', static function (string $translation, string $text, string $domain): string {
	if ($domain !== 'woocommerce') {
		return $translation;
	}
	$map = [
		'Thank you for your order' => 'Obrigada pelo seu pedido',
		'Thanks for shopping with us.' => 'Obrigada por comprar com a Kra de Rica.',
		'Hi %s,' => 'Olá, %s.',
		"We've received your order and it is now being processed." => 'Recebemos o seu pedido e ele já está sendo preparado.',
		'We’ve received your order and it’s currently on-hold until we confirm that payment has been received.' => 'Recebemos o seu pedido. Ele fica em espera até confirmarmos o pagamento.',
		"We've received your order and it is currently on hold until we confirm that payment has been received." => 'Recebemos o seu pedido. Ele fica em espera até confirmarmos o pagamento.',
		'Order summary' => 'Resumo do pedido',
		'Order #%1$s (%2$s)' => 'Pedido #%1$s (%2$s)',
		'Product' => 'Produto',
		'Quantity' => 'Qtd.',
		'Price' => 'Preço',
		'Subtotal:' => 'Subtotal:',
		'Shipping:' => 'Envio:',
		'Payment method:' => 'Forma de pagamento:',
		'Total:' => 'Total:',
		'Billing address' => 'Endereço de cobrança',
		'Shipping address' => 'Endereço de entrega',
		'To view this page, you must either login or verify the email address associated with the order.' => 'Para ver esta página, entre na conta ou confirme o e-mail do pedido.',
		'Email address' => 'E-mail',
		'Verify' => 'Confirmar',
		'Order received' => 'Pedido recebido',
		'Please log in to your account below to continue to the payment form.' => 'Entre na sua conta para continuar o pagamento.',
		'Username or email' => 'Usuário ou e-mail',
		'Username or email address' => 'Usuário ou e-mail',
		'Password' => 'Senha',
		'Remember me' => 'Lembrar-me',
		'Log in' => 'Entrar',
		'Login' => 'Entrar',
		'Lost your password?' => 'Esqueceu a senha?',
		'Pay for order' => 'Pagar pedido',
		'Pay' => 'Pagar',
		'Cancel' => 'Cancelar',
		'Cancel order' => 'Cancelar pedido',
		'Order number' => 'Número do pedido',
		'Date' => 'Data',
		'Total' => 'Total',
		'Actions' => 'Ações',
		'Order details' => 'Detalhes do pedido',
		'Order updates' => 'Atualizações do pedido',
		'Pending payment' => 'Aguardando pagamento',
		'Processing' => 'Em processamento',
		'On hold' => 'Em espera',
		'Completed' => 'Concluído',
		'Cancelled' => 'Cancelado',
		'Refunded' => 'Reembolsado',
		'Failed' => 'Falhou',
		'Order #%1$s was placed on %2$s and is currently %3$s.' => 'Pedido #%1$s feito em %2$s · Situação: %3$s.',
		'First name' => 'Nome',
		'Last name' => 'Sobrenome',
		'Last name (optional)' => 'Sobrenome (opcional)',
		'Select a country / region…' => 'Selecione um país / região…',
		'Select a country / region...' => 'Selecione um país / região...',
		'Apartment, suite, unit, etc. (optional)' => 'Apartamento, bloco, etc. (opcional)',
		'Company name' => 'Empresa',
		'Town / City' => 'Cidade',
		'State / County' => 'Estado',
		'Postcode / ZIP' => 'CEP',
		'Country / Region' => 'País / Região',
		'Street address' => 'Rua',
	];
	return $map[$text] ?? $translation;
}, 30, 3);

add_filter('woocommerce_email_styles', static function (string $css): string {
	$css .= '
		body, #wrapper { background-color: #0b0b0b !important; }
		#template_header, #template_body, #template_footer, #body_content, #body_content_inner { background: #111 !important; color: #f5f2ea !important; }
		#template_container { border: 1px solid #2a2a2a !important; box-shadow: none !important; }
		h1, h2, h3, td, th, p { color: #f5f2ea !important; }
		a { color: #c4a574 !important; }
		#template_header_image img { margin-bottom: 0 !important; }
	';
	return $css;
}, 20);

/**
 * Pedidos no cartão/PIX costumam ficar "pending" até o webhook.
 * O Woo não envia e-mail de cliente nesse status — forçamos confirmação 1x.
 */
add_action('woocommerce_checkout_order_processed', static function (int $order_id): void {
	vesta_maybe_send_order_emails($order_id, 'checkout');
}, 40);

add_action('woocommerce_thankyou', static function ($order_id): void {
	if (! $order_id) {
		return;
	}
	vesta_maybe_send_order_emails((int) $order_id, 'thankyou');
}, 5);

/**
 * @param int    $order_id Order ID.
 * @param string $context  checkout|thankyou|manual.
 * @param bool   $force    Ignora flag (reenvio).
 */
function vesta_maybe_send_order_emails(int $order_id, string $context = 'checkout', bool $force = false): void {
	if (! function_exists('WC') || ! $order_id) {
		return;
	}

	$order = wc_get_order($order_id);
	if (! $order instanceof WC_Order) {
		return;
	}

	$flag = '_vesta_order_emails_sent';
	$lock = '_vesta_order_emails_lock';
	$paid = $order->is_paid() || $order->has_status(['processing', 'completed']);

	// Sem pagamento confirmado: avisa só a loja. O cliente não recebe "compra confirmada".
	if (! $force && ! $paid) {
		if ($order->get_meta('_vesta_admin_order_email_sent') !== 'yes' && function_exists('WC')) {
			$mailer = WC()->mailer();
			$emails = $mailer->get_emails();
			if (isset($emails['WC_Email_New_Order']) && is_a($emails['WC_Email_New_Order'], 'WC_Email')) {
				$emails['WC_Email_New_Order']->trigger($order_id);
			}
			$order->update_meta_data('_vesta_admin_order_email_sent', 'yes');
			$order->save();
		}
		return;
	}

	if (! $force && $order->get_meta($flag) === 'yes') {
		return;
	}

	// Lock só vale no mesmo minuto (evita travar envio para sempre).
	$lock_ts = (int) $order->get_meta($lock);
	if (! $force && $lock_ts > 0 && (time() - $lock_ts) < 60) {
		return;
	}
	$order->update_meta_data($lock, (string) time());
	$order->save();

	try {
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();
		$to     = (string) $order->get_billing_email();

		if (! $force && isset($emails['WC_Email_New_Order']) && is_a($emails['WC_Email_New_Order'], 'WC_Email')) {
			$emails['WC_Email_New_Order']->trigger($order_id);
		}

		$status = $order->get_status();
		$customer_email = null;

		if (in_array($status, ['processing', 'completed'], true) && isset($emails['WC_Email_Customer_Processing_Order'])) {
			$customer_email = $emails['WC_Email_Customer_Processing_Order'];
		} elseif ($status === 'completed' && isset($emails['WC_Email_Customer_Completed_Order'])) {
			$customer_email = $emails['WC_Email_Customer_Completed_Order'];
		} elseif (isset($emails['WC_Email_Customer_Processing_Order'])) {
			$customer_email = $emails['WC_Email_Customer_Processing_Order'];
		}

		$sent = false;
		if ($customer_email && is_a($customer_email, 'WC_Email') && is_email($to)) {
			// Garante destinatário do pedido (Woo às vezes deixa vazio fora do fluxo).
			$customer_email->recipient = $to;
			$customer_email->trigger($order_id);
			$sent = true;
		}

		// Fallback direto se o trigger do Woo falhar silenciosamente.
		if (! $sent && is_email($to)) {
			$subject = sprintf('[%s]: Compra confirmada #%s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $order->get_order_number());
			$body    = sprintf(
				"Olá, %s.\n\nRecebemos o pedido VS%s. Total: %s.\n\n%s",
				$order->get_billing_first_name() ?: 'cliente',
				$order->get_order_number(),
				wp_strip_all_tags($order->get_formatted_order_total()),
				home_url('/')
			);
			$sent = (bool) wp_mail($to, $subject, $body);
		}

		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order) {
			if ($sent) {
				$order->update_meta_data($flag, 'yes');
				$order->add_order_note(
					sprintf(
						/* translators: 1: context 2: email */
						__('E-mails Vesta enviados (%1$s) para %2$s.', 'vesta'),
						$context,
						$to
					)
				);
			}
			$order->delete_meta_data($lock);
			$order->save();
		}
	} catch (Throwable $e) {
		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order) {
			$order->delete_meta_data($lock);
			$order->save();
		}
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log('Vesta mail: ' . $e->getMessage());
		}
	}
}
