<?php
/**
 * Páginas de autenticação alinhadas ao Next (/login, /cadastro, /recuperar-senha).
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/** Guests em /minha-conta → /login (igual Next). */
add_action('template_redirect', static function (): void {
	if (! function_exists('is_account_page') || ! is_account_page() || is_user_logged_in()) {
		return;
	}
	$endpoint = WC()->query->get_current_endpoint();
	if (in_array($endpoint, ['lost-password', 'reset-password', 'customer-logout'], true)) {
		return;
	}
	// Permite processar POST de login/registro se alguém postar na conta.
	if (! empty($_POST['login']) || ! empty($_POST['register']) || ! empty($_POST['wc_reset_password'])) {
		return;
	}
	$login = get_page_by_path('login');
	if ($login) {
		wp_safe_redirect(get_permalink($login), 302);
		exit;
	}
}, 5);

/** Logados que abrem /login ou /cadastro → minha conta. */
add_action('template_redirect', static function (): void {
	if (! is_user_logged_in() || ! is_page(['login', 'cadastro'])) {
		return;
	}
	wp_safe_redirect(wc_get_page_permalink('myaccount'), 302);
	exit;
}, 6);

/** Salva nome/telefone no registro. */
add_action('woocommerce_created_customer', static function (int $customer_id): void {
	if (isset($_POST['billing_first_name'])) {
		$name = sanitize_text_field(wp_unslash((string) $_POST['billing_first_name']));
		wp_update_user(['ID' => $customer_id, 'first_name' => $name, 'display_name' => $name]);
		update_user_meta($customer_id, 'billing_first_name', $name);
	}
	if (isset($_POST['billing_phone'])) {
		update_user_meta($customer_id, 'billing_phone', sanitize_text_field(wp_unslash((string) $_POST['billing_phone'])));
	}
});

/**
 * Destinos de compra (checkout / pagar / pedido recebido) nunca devem ir para o Atelier.
 */
function vesta_is_storefront_checkout_url(string $url): bool {
	$path = (string) (wp_parse_url($url, PHP_URL_PATH) ?? '');
	return str_contains($path, 'checkout')
		|| str_contains($path, 'order-pay')
		|| str_contains($path, 'order-received')
		|| str_contains($path, 'pedido')
		|| str_contains($path, 'minha-conta')
		|| str_contains($path, 'carrinho')
		|| str_contains($path, 'cart');
}

/** Após login: compra → volta à compra; equipe só vai ao Atelier no login “normal”. */
add_filter('woocommerce_login_redirect', static function (string $redirect, $user): string {
	if (! empty($_POST['redirect'])) {
		$to = esc_url_raw(wp_unslash((string) $_POST['redirect']));
		if ($to && wp_validate_redirect($to, false)) {
			return $to;
		}
	}
	if ($redirect !== '' && vesta_is_storefront_checkout_url($redirect)) {
		return $redirect;
	}
	if ($user instanceof WP_User && user_can($user, 'edit_products')) {
		return home_url('/admin/');
	}
	return wc_get_page_permalink('myaccount');
}, 10, 2);

/** Login via wp-login.php: Atelier só quando não estiver no meio de uma compra. */
add_filter('login_redirect', static function ($redirect_to, $requested, $user) {
	if ($user instanceof WP_User && ! is_wp_error($user) && user_can($user, 'edit_products')) {
		if (is_string($requested) && $requested !== '' && wp_validate_redirect($requested, false)) {
			if (vesta_is_storefront_checkout_url($requested)) {
				return $requested;
			}
			if (str_contains($requested, '/admin') && ! str_contains($requested, 'wp-admin')) {
				return $requested;
			}
		}
		if (is_string($redirect_to) && vesta_is_storefront_checkout_url($redirect_to)) {
			return $redirect_to;
		}
		return home_url('/admin/');
	}
	return $redirect_to;
}, 10, 3);

/** Lost-password URL → página dedicada. */
add_filter('woocommerce_get_endpoint_url', static function (string $url, string $endpoint): string {
	if ($endpoint === 'lost-password') {
		$page = get_page_by_path('recuperar-senha');
		if ($page) {
			return trailingslashit(get_permalink($page));
		}
	}
	return $url;
}, 10, 2);

/** Após enviar e-mail de reset, volta para nossa página com flag. */
add_action('woocommerce_customer_reset_password', static function (): void {
	// fluxo padrão Woo após redefinir.
});

add_filter('woocommerce_lost_password_redirect', static function (string $url): string {
	$page = get_page_by_path('recuperar-senha');
	if ($page) {
		return add_query_arg('reset-link-sent', 'true', get_permalink($page));
	}
	return $url;
});

/** Habilita registro se ainda não estiver. */
add_action('init', static function (): void {
	if (get_option('woocommerce_enable_myaccount_registration') !== 'yes') {
		update_option('woocommerce_enable_myaccount_registration', 'yes');
	}
	if (get_option('woocommerce_registration_generate_username') !== 'yes') {
		update_option('woocommerce_registration_generate_username', 'yes');
	}
}, 20);
