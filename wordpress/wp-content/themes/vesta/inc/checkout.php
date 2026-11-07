<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Checkout Kra de Rica — layout e campos alinhados ao CheckoutForm do Next.
 */

/**
 * True quando algum gateway Mercado Pago está disponível no checkout.
 */
function vesta_has_mercadopago(): bool {
	if (! function_exists('WC') || ! WC()->payment_gateways()) {
		return false;
	}
	foreach (WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway) {
		if (stripos((string) $id, 'mercado') !== false) {
			return true;
		}
	}
	return false;
}

/**
 * Gateway padrão: prioriza PIX/cartão no site (sem login MP).
 */
function vesta_checkout_default_gateway(): string {
	if (! function_exists('WC')) {
		return '';
	}
	$gateways = WC()->payment_gateways()->get_available_payment_gateways();
	if (! $gateways) {
		return '';
	}
	$preferred = [
		'woo-mercado-pago-pix',
		'woo-mercado-pago-custom',
	];
	foreach ($preferred as $id) {
		if (isset($gateways[$id])) {
			return $id;
		}
	}
	foreach ($gateways as $id => $gateway) {
		if (stripos((string) $id, 'mercado') !== false && $id !== 'woo-mercado-pago-basic') {
			return (string) $id;
		}
	}
	return (string) array_key_first($gateways);
}

add_action('wp_loaded', static function (): void {
	// Resumo fica na coluna direita; pagamento é chamado direto no form-checkout.
	remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
	remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
});

/** Ordem: PIX → cartão no site. Sem Checkout Pro (conta MP). */
add_filter('woocommerce_available_payment_gateways', static function (array $gateways): array {
	unset($gateways['woo-mercado-pago-basic']);

	$order = [
		'woo-mercado-pago-pix',
		'woo-mercado-pago-custom',
	];
	$sorted = [];
	foreach ($order as $id) {
		if (isset($gateways[$id])) {
			$sorted[$id] = $gateways[$id];
			unset($gateways[$id]);
		}
	}
	return $sorted + $gateways;
});

/** Garante payment_method do Mercado Pago mesmo se o POST vier vazio. */
add_action('woocommerce_checkout_process', static function (): void {
	$method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash((string) $_POST['payment_method'])) : '';
	if ($method !== '') {
		return;
	}
	$default = vesta_checkout_default_gateway();
	if ($default !== '') {
		$_POST['payment_method'] = $default;
	}
}, 0);

/** CPF obrigatório com dígitos válidos. */
add_action('woocommerce_checkout_process', static function (): void {
	$cpf = isset($_POST['billing_cpf']) ? sanitize_text_field(wp_unslash((string) $_POST['billing_cpf'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ($cpf === '' || ! function_exists('vesta_is_valid_cpf') || ! vesta_is_valid_cpf($cpf)) {
		wc_add_notice(__('Informe um CPF válido.', 'vesta'), 'error');
	}
}, 5);

/** Fragment AJAX: resumo sticky + opções de frete. */
add_filter('woocommerce_update_order_review_fragments', static function (array $fragments): array {
	ob_start();
	echo '<div class="vesta-checkout__summary-body">';
	wc_get_template('checkout/review-order.php');
	echo '</div>';
	$fragments['.vesta-checkout__summary-body'] = ob_get_clean();

	ob_start();
	echo '<div id="vesta-shipping-methods">';
	if (WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
		echo '<table class="vesta-shipping-table"><tbody>';
		wc_cart_totals_shipping_html();
		echo '</tbody></table>';
	} else {
		echo '<p class="vesta-checkout__hint">' . esc_html__('Frete a combinar — alinhamos a entrega após o pedido.', 'vesta') . '</p>';
	}
	echo '</div>';
	$fragments['#vesta-shipping-methods'] = ob_get_clean();

	return $fragments;
});

add_action('wp_enqueue_scripts', static function (): void {
	if (! function_exists('is_checkout') || ! is_checkout() || is_order_received_page()) {
		return;
	}
	wp_enqueue_script(
		'vesta-checkout',
		VESTA_THEME_URI . '/assets/js/checkout.js',
		['jquery', 'vesta-cart'],
		VESTA_THEME_VERSION,
		true
	);
	wp_localize_script('vesta-checkout', 'vestaCheckout', [
		'logoUrl'         => VESTA_THEME_URI . '/assets/img/icon-192.png?v=' . VESTA_THEME_VERSION,
		'hasMercadoPago'  => vesta_has_mercadopago(),
	]);
});

/** Campos BR + labels iguais ao Next. */
add_filter('woocommerce_checkout_fields', static function (array $fields): array {
	$fields['billing']['billing_first_name']['label'] = __('Nome completo', 'vesta');
	$fields['billing']['billing_first_name']['placeholder'] = '';
	$fields['billing']['billing_first_name']['class'] = ['form-row-wide'];
	$fields['billing']['billing_first_name']['priority'] = 10;

	unset($fields['billing']['billing_last_name']);
	unset($fields['billing']['billing_company']);
	unset($fields['billing']['billing_address_2']);

	$fields['billing']['billing_email']['label'] = __('E-mail', 'vesta');
	$fields['billing']['billing_email']['class'] = ['form-row-wide'];
	$fields['billing']['billing_email']['priority'] = 20;

	$fields['billing']['billing_cpf'] = [
		'type'         => 'text',
		'label'        => __('CPF', 'vesta'),
		'required'     => true,
		'class'        => ['form-row-wide'],
		'priority'     => 30,
		'autocomplete' => 'off',
	];

	$fields['billing']['billing_phone']['label'] = __('Telefone', 'vesta');
	$fields['billing']['billing_phone']['required'] = true;
	$fields['billing']['billing_phone']['class'] = ['form-row-wide'];
	$fields['billing']['billing_phone']['priority'] = 40;

	$fields['billing']['billing_postcode']['label'] = __('CEP', 'vesta');
	$fields['billing']['billing_postcode']['class'] = ['form-row-wide'];
	$fields['billing']['billing_postcode']['priority'] = 50;

	$fields['billing']['billing_address_1']['label'] = __('Rua', 'vesta');
	$fields['billing']['billing_address_1']['placeholder'] = '';
	$fields['billing']['billing_address_1']['class'] = ['form-row-wide'];
	$fields['billing']['billing_address_1']['priority'] = 60;

	$fields['billing']['billing_number'] = [
		'type'        => 'text',
		'label'       => __('Número', 'vesta'),
		'placeholder' => __('Ex.: 1835', 'vesta'),
		'required'    => true,
		'class'       => ['form-row-wide'],
		'priority'    => 70,
	];

	$fields['billing']['billing_complement'] = [
		'type'        => 'text',
		'label'       => __('Complemento', 'vesta'),
		'required'    => false,
		'class'       => ['form-row-wide'],
		'priority'    => 80,
		'label_class' => [],
	];

	$fields['billing']['billing_neighborhood'] = [
		'type'        => 'text',
		'label'       => __('Bairro', 'vesta'),
		'placeholder' => __('Ex.: Centro', 'vesta'),
		'required'    => true,
		'class'       => ['form-row-wide'],
		'priority'    => 90,
	];

	$fields['billing']['billing_city']['label'] = __('Cidade', 'vesta');
	$fields['billing']['billing_city']['class'] = ['form-row-wide'];
	$fields['billing']['billing_city']['priority'] = 100;

	$fields['billing']['billing_state']['label'] = __('Estado', 'vesta');
	$fields['billing']['billing_state']['type'] = 'text';
	$fields['billing']['billing_state']['class'] = ['form-row-wide'];
	$fields['billing']['billing_state']['priority'] = 110;
	$fields['billing']['billing_state']['maxlength'] = 2;
	$fields['billing']['billing_state']['custom_attributes'] = [
		'maxlength' => 2,
		'style'     => 'text-transform:uppercase',
	];

	$fields['billing']['billing_country']['class'] = ['form-row-wide', 'vesta-hidden-field'];
	$fields['billing']['billing_country']['priority'] = 120;
	$fields['billing']['billing_country']['default'] = 'BR';

	$fields['order']['order_comments']['class'] = ['notes', 'vesta-hidden-field'];

	return $fields;
}, 20);

/** Força Estado como texto (UF), igual Next — Woo tende a virar select. */
add_filter('woocommerce_checkout_fields', static function (array $fields): array {
	if (isset($fields['billing']['billing_state'])) {
		$fields['billing']['billing_state']['type'] = 'text';
		$fields['billing']['billing_state']['maxlength'] = 2;
		$fields['billing']['billing_state']['custom_attributes'] = [
			'maxlength'   => 2,
			'inputmode'   => 'text',
			'autocomplete'=> 'address-level1',
			'style'       => 'text-transform:uppercase',
		];
	}
	return $fields;
}, 9999);

add_filter('woocommerce_default_address_fields', static function (array $fields): array {
	if (isset($fields['first_name'])) {
		$fields['first_name']['label'] = __('Nome', 'vesta');
	}
	if (isset($fields['last_name'])) {
		$fields['last_name']['required'] = false;
		$fields['last_name']['label']   = __('Sobrenome (opcional)', 'vesta');
	}
	if (isset($fields['address_2'])) {
		$fields['address_2']['required']    = false;
		$fields['address_2']['label']       = __('Complemento', 'vesta');
		$fields['address_2']['placeholder'] = __('Apartamento, bloco, etc. (opcional)', 'vesta');
	}
	if (isset($fields['address_1'])) {
		$fields['address_1']['label']       = __('Rua', 'vesta');
		$fields['address_1']['placeholder'] = '';
	}
	if (isset($fields['city'])) {
		$fields['city']['label'] = __('Cidade', 'vesta');
	}
	if (isset($fields['state'])) {
		$fields['state']['label'] = __('Estado', 'vesta');
	}
	if (isset($fields['postcode'])) {
		$fields['postcode']['label'] = __('CEP', 'vesta');
	}
	if (isset($fields['country'])) {
		$fields['country']['label'] = __('País', 'vesta');
	}
	return $fields;
}, 20);

add_filter('woocommerce_shipping_fields', static function (array $fields): array {
	if (isset($fields['shipping_first_name'])) {
		$fields['shipping_first_name']['label'] = __('Nome', 'vesta');
	}
	if (isset($fields['shipping_last_name'])) {
		$fields['shipping_last_name']['label']    = __('Sobrenome (opcional)', 'vesta');
		$fields['shipping_last_name']['required'] = false;
	}
	if (isset($fields['shipping_address_1'])) {
		$fields['shipping_address_1']['label'] = __('Rua', 'vesta');
	}
	if (isset($fields['shipping_address_2'])) {
		$fields['shipping_address_2']['label']       = __('Complemento', 'vesta');
		$fields['shipping_address_2']['placeholder'] = __('Apartamento, bloco, etc. (opcional)', 'vesta');
	}
	return $fields;
}, 20);

add_filter('woocommerce_billing_fields', static function (array $fields): array {
	if (is_account_page() && isset($fields['billing_first_name'])) {
		$fields['billing_first_name']['label'] = __('Nome', 'vesta');
	}
	if (is_account_page() && isset($fields['billing_last_name'])) {
		$fields['billing_last_name']['label']    = __('Sobrenome (opcional)', 'vesta');
		$fields['billing_last_name']['required'] = false;
	}
	return $fields;
}, 25);

add_filter('woocommerce_checkout_posted_data', static function (array $data): array {
	// Nome completo no first_name; evita falha de last_name obrigatório.
	if (empty($data['billing_last_name'])) {
		$data['billing_last_name'] = '-';
	}
	$number = isset($_POST['billing_number']) ? sanitize_text_field(wp_unslash((string) $_POST['billing_number'])) : '';
	$compl  = isset($_POST['billing_complement']) ? sanitize_text_field(wp_unslash((string) $_POST['billing_complement'])) : '';
	$neigh  = isset($_POST['billing_neighborhood']) ? sanitize_text_field(wp_unslash((string) $_POST['billing_neighborhood'])) : '';
	if ($number !== '' && ! empty($data['billing_address_1'])) {
		$data['billing_address_1'] = trim($data['billing_address_1'] . ', ' . $number);
	}
	if ($compl !== '') {
		$data['billing_address_2'] = $compl;
	}
	if ($neigh !== '' && empty($data['billing_address_2'])) {
		$data['billing_address_2'] = $neigh;
	} elseif ($neigh !== '') {
		$data['billing_address_2'] = trim($data['billing_address_2'] . ' — ' . $neigh);
	}
	return $data;
});

add_action('woocommerce_checkout_update_order_meta', static function (int $order_id): void {
	$map = [
		'billing_cpf'          => '_billing_cpf',
		'billing_number'       => '_billing_number',
		'billing_complement'   => '_billing_complement',
		'billing_neighborhood' => '_billing_neighborhood',
	];
	foreach ($map as $post_key => $meta_key) {
		if (isset($_POST[$post_key])) {
			update_post_meta($order_id, $meta_key, sanitize_text_field(wp_unslash((string) $_POST[$post_key])));
		}
	}
	// Nome completo no first_name; last_name vazio
	if (isset($_POST['billing_first_name'])) {
		update_post_meta($order_id, '_billing_last_name', '');
	}
});

add_filter('woocommerce_order_button_text', static fn (): string => __('Ir para o pagamento', 'vesta'));

/** Botão de pedido com estado de loading próximo ao Next. */
add_filter('woocommerce_order_button_html', static function (string $html): string {
	$text = __('Ir para o pagamento', 'vesta');
	return '<button type="submit" class="button alt btn btn--wine vesta-place-order" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($text) . '" data-value="' . esc_attr($text) . '">' . esc_html($text) . '</button>';
});

add_filter('woocommerce_checkout_must_be_logged_in_message', static function (): string {
	return __('Entre na sua conta para continuar a compra.', 'vesta');
});

/** Remove título padrão "Detalhes de faturamento". */
add_filter('woocommerce_billing_fields', static function (array $fields): array {
	return $fields;
});

add_action('woocommerce_before_checkout_form', static function (): void {
	// Remove cupom padrão do Woo no topo (temos cupom no formulário).
	remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
	// Limpa notices de "produto adicionado" que poluem o checkout.
	if (function_exists('wc_clear_notices')) {
		wc_clear_notices();
	}
	echo '<div class="vesta-checkout-page">';
	echo '<p class="eyebrow">' . esc_html__('Finalização', 'vesta') . '</p>';
	echo '<h1 class="display vesta-checkout__title">' . esc_html__('Finalizar compra', 'vesta') . '</h1>';
	if (vesta_has_mercadopago()) {
		echo '<p class="vesta-checkout__note">' . esc_html__('PIX, cartão e parcelamento via Mercado Pago. Dados do cartão não são armazenados na Kra de Rica.', 'vesta') . '</p>';
	} else {
		echo '<p class="vesta-checkout__note">' . esc_html__('Finalize o pedido com transferência ou pagamento na retirada. Combinamos a entrega com você.', 'vesta') . '</p>';
	}
}, 5);

/** Remove sufixo "(optional)" do Complemento. */
add_filter('woocommerce_form_field', static function (string $field, string $key): string {
	if ($key !== 'billing_complement') {
		return $field;
	}
	$field = str_replace(['&nbsp;<span class="optional">(optional)</span>', '&nbsp;<span class="optional">(opcional)</span>'], '', $field);
	$field = str_replace(['(optional)', '(opcional)'], '', $field);
	return $field;
}, 10, 2);

add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');
add_filter('woocommerce_checkout_show_terms', '__return_false');
add_filter('woocommerce_enable_order_notes_field', '__return_false');
add_filter('woocommerce_checkout_registration_enabled', '__return_false');

/** Aceita termos automaticamente (não exibimos o bloco, igual Next). */
add_action('woocommerce_checkout_process', static function (): void {
	$_POST['terms'] = 1;
	$_POST['terms-field'] = 1;
}, 1);

/** Labels Woo em PT no checkout / pós-venda (site às vezes fica em EN). */
add_filter('gettext', static function (string $translation, string $text, string $domain): string {
	if ($domain !== 'woocommerce') {
		return $translation;
	}
	$map = [
		'(optional)'                                      => '(opcional)',
		'Select an option&hellip;'                        => 'Selecione…',
		'Select an option…'                               => 'Selecione…',
		'House number and street name'                    => 'Nome da rua',
		'Thank you. Your order has been received.'        => 'Obrigada. Seu pedido foi recebido.',
		'Order number:'                                   => 'Número do pedido:',
		'Date:'                                           => 'Data:',
		'Total:'                                          => 'Total:',
		'Payment method:'                                 => 'Forma de pagamento:',
		'Order details'                                   => 'Detalhes do pedido',
		'Product'                                         => 'Produto',
		'Subtotal:'                                       => 'Subtotal:',
		'Shipping:'                                       => 'Envio:',
		'Billing address'                                 => 'Endereço de cobrança',
		'Shipping address'                                => 'Endereço de entrega',
		'Pay for order'                                   => 'Pagar pedido',
		'My account'                                      => 'Minha conta',
		'%s is a required field.'                         => '%s é obrigatório.',
		'Billing %s'                                      => '%s',
	];
	return $map[$text] ?? $translation;
}, 20, 3);

/** "Billing Número" vira "Número é obrigatório." */
add_filter('gettext_with_context', static function (string $translation, string $text, string $context, string $domain): string {
	if ($domain === 'woocommerce' && $context === 'checkout-validation' && $text === 'Billing %s') {
		return '%s';
	}
	return $translation;
}, 20, 4);

add_filter('woocommerce_checkout_required_field_notice', static function (string $notice, string $field_label, string $key): string {
	$labels = [
		'billing_number'       => 'Número',
		'billing_neighborhood' => 'Bairro',
		'billing_postcode'     => 'CEP',
		'billing_address_1'    => 'Rua',
		'billing_city'         => 'Cidade',
		'billing_state'        => 'Estado',
		'billing_phone'        => 'Telefone',
		'billing_email'        => 'E-mail',
		'billing_first_name'   => 'Nome completo',
		'billing_cpf'          => 'CPF',
	];
	if (isset($labels[ $key ])) {
		return '<strong>' . esc_html($labels[ $key ]) . '</strong> é obrigatório.';
	}
	return $notice;
}, 10, 3);

/** Esconde overview padrão do Woo quando usamos o thankyou Vesta. */
add_action('wp', static function (): void {
	if (! function_exists('is_order_received_page') || ! is_order_received_page()) {
		return;
	}
	remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
});

add_action('woocommerce_after_checkout_form', static function (): void {
	echo '</div>';
}, 50);

/** Cupom no fluxo principal (como no Next). */
add_action('woocommerce_review_order_before_payment', static function (): void {
	// cupom já aparece no form custom
}, 5);
