<?php
/**
 * Configura credenciais + Checkout Pro (woo-mercado-pago-basic).
 *
 * Uso:
 *   MP_PUBLIC_KEY=... MP_ACCESS_TOKEN=... MP_TEST_MODE=yes \
 *   wp eval-file /scripts/configure-mercadopago.php --allow-root
 */

$public_key   = getenv('MP_PUBLIC_KEY') ?: (getenv('MERCADOPAGO_PUBLIC_KEY') ?: '');
$access_token = getenv('MP_ACCESS_TOKEN') ?: (getenv('MERCADOPAGO_ACCESS_TOKEN') ?: '');
$test_mode    = strtolower((string) (getenv('MP_TEST_MODE') ?: 'yes'));
$test_mode    = in_array($test_mode, ['1', 'true', 'yes'], true) ? 'yes' : 'no';

if ($public_key === '' || $access_token === '') {
	fwrite(STDERR, "Defina MP_PUBLIC_KEY e MP_ACCESS_TOKEN (ou MERCADOPAGO_*).\n");
	exit(1);
}

update_option('_mp_public_key_test', $public_key);
update_option('_mp_access_token_test', $access_token);
update_option('_mp_public_key_prod', $public_key);
update_option('_mp_access_token_prod', $access_token);
update_option('checkbox_checkout_test_mode', $test_mode);
update_option('checkbox_checkout_production_mode', $test_mode === 'yes' ? 'no' : 'yes');

// Em localhost o MP rejeita notification_url da própria loja (Invalid notification_url).
// Usa um endpoint público só para criar a preferência; webhooks locais não são necessários.
$site = (string) get_site_url();
if (stripos($site, 'localhost') !== false || stripos($site, '127.0.0.1') !== false) {
	$notify = getenv('MP_NOTIFICATION_URL') ?: 'https://vestabrasil.com.br/wc-api/WC_WooMercadoPago_Basic_Gateway/';
	update_option('_mp_custom_domain', $notify);
	update_option('_mp_custom_domain_options', 'no');
	echo "localhost notify fallback={$notify}\n";
}

$option_key = 'woocommerce_woo-mercado-pago-basic_settings';
$settings   = get_option($option_key, []);
if (! is_array($settings)) {
	$settings = [];
}
$settings['enabled']     = 'yes';
$settings['title']       = $settings['title'] ?? 'Mercado Pago';
$settings['description'] = $settings['description'] ?? 'PIX, cartão e parcelamento no Mercado Pago.';
$settings['method']      = $settings['method'] ?? 'redirect';
$settings['auto_return'] = $settings['auto_return'] ?? 'yes';
update_option($option_key, $settings);

// Desliga métodos embutidos (PIX/cartão na loja) — o fluxo Vesta é Checkout Pro.
foreach (['woo-mercado-pago-custom', 'woo-mercado-pago-pix', 'woo-mercado-pago-ticket', 'woo-mercado-pago-credits'] as $gid) {
	$key = 'woocommerce_' . $gid . '_settings';
	$opt = get_option($key, []);
	if (! is_array($opt)) {
		$opt = [];
	}
	$opt['enabled'] = 'no';
	update_option($key, $opt);
}

echo "Mercado Pago configurado.\n";
echo "test_mode={$test_mode}\n";
echo "public_key=" . substr($public_key, 0, 16) . "...\n";
echo "basic_enabled=" . (get_option($option_key)['enabled'] ?? '?') . "\n";

if (function_exists('WC')) {
	WC()->payment_gateways()->init();
	$available = WC()->payment_gateways()->get_available_payment_gateways();
	echo "available=" . implode(',', array_keys($available)) . "\n";
	$all = WC()->payment_gateways()->payment_gateways();
	if (isset($all['woo-mercado-pago-basic'])) {
		$gw = $all['woo-mercado-pago-basic'];
		echo "basic.enabled={$gw->enabled}\n";
		echo "basic.is_available=" . ($gw->is_available() ? 'yes' : 'no') . "\n";
		if (method_exists($gw, 'isMissingCredentials')) {
			echo "basic.missing_credentials=" . ($gw->isMissingCredentials() ? 'yes' : 'no') . "\n";
		}
	}
}
