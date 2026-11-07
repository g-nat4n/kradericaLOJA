<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('after_setup_theme', static function (): void {
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
});

add_filter('loop_shop_per_page', static fn (): int => 24);
add_filter('woocommerce_product_thumbnails_columns', static fn (): int => 4);

// Curadoria usa toolbar própria.
add_action('wp', static function (): void {
	if (function_exists('is_shop') && is_shop()) {
		remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
		remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
		remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
	}
});

/** PDP custom: remove gancho padrão (título/preço/carrinho/tabs) — layout no content-single-product. */
add_action('wp', static function (): void {
	if (! function_exists('is_product') || ! is_product()) {
		return;
	}
	remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
	remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
});

add_filter('woocommerce_product_tabs', '__return_empty_array');

/** Frete na PDP (igual ShippingEstimator do Next). */
add_action('wp_ajax_vesta_pdp_shipping', 'vesta_ajax_pdp_shipping');
add_action('wp_ajax_nopriv_vesta_pdp_shipping', 'vesta_ajax_pdp_shipping');

function vesta_ajax_pdp_shipping(): void {
	check_ajax_referer('vesta_cart', 'nonce');
	$ip  = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0';
	$key = 'vesta_pdp_ship_rl_' . md5($ip);
	$hits = (int) get_transient($key);
	if ($hits >= 40) {
		wp_send_json_error(['message' => __('Muitas consultas. Aguarde um momento.', 'vesta')], 429);
	}
	set_transient($key, $hits + 1, 10 * MINUTE_IN_SECONDS);

	$cep = isset($_POST['cep']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['cep'])) : '';
	if (strlen((string) $cep) !== 8) {
		wp_send_json_error(['message' => __('CEP inválido.', 'vesta')], 400);
	}

	wp_send_json_success([
		'quotes' => [
			[
				'id'    => 'pickup',
				'label' => __('Retirada na loja', 'vesta'),
				'price' => wc_price(0),
			],
			[
				'id'    => 'combine',
				'label' => __('Frete a combinar', 'vesta'),
				'price' => wc_price(0),
			],
		],
	]);
}

add_action('wp_enqueue_scripts', static function (): void {
	if (! function_exists('is_product') || ! is_product()) {
		return;
	}
	wp_enqueue_script(
		'vesta-pdp',
		VESTA_THEME_URI . '/assets/js/pdp.js',
		['jquery', 'vesta-cart'],
		VESTA_THEME_VERSION,
		true
	);
});

/** Esconde o menu padrão do WooCommerce — a área do cliente usa o layout Vesta. */
add_action('init', static function (): void {
	remove_action('woocommerce_account_navigation', 'woocommerce_account_navigation');
});

/**
 * Voltar à área do cliente (usado se algum template padrão do Woo aparecer).
 */
function vesta_account_back_link(): void {
	$url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/minha-conta/');
	echo '<p class="vesta-account__back"><a href="' . esc_url($url) . '">← ' . esc_html__('Voltar à área do cliente', 'vesta') . '</a></p>';
}

/** Evita detalhe duplicado: view-order Vesta já renderiza order-details. */
add_action('wp', static function (): void {
	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
		remove_action('woocommerce_view_order', 'woocommerce_order_details_table', 10);
	}
}, 20);

/** Evita linha Actions em inglês duplicada no resumo do pedido. */
add_filter('woocommerce_get_order_item_totals', static function (array $totals): array {
	unset($totals['order_actions'], $totals['actions']);
	return $totals;
}, 40);

/** Remove "Order again" / Comprar novamente na conta. */
add_action('init', static function (): void {
	remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
}, 20);

add_filter('woocommerce_my_account_my_orders_actions', static function (array $actions, $order): array {
	unset($actions['view'], $actions['order-again']);
	if ($order instanceof WC_Order && ($order->is_paid() || ! $order->has_status(['pending', 'on-hold']))) {
		unset($actions['cancel']);
	}
	return $actions;
}, 20, 2);

/** Status de pedido em português. */
add_filter('wc_order_statuses', static function (array $statuses): array {
	$map = [
		'wc-pending'    => 'Aguardando pagamento',
		'wc-processing' => 'Em processamento',
		'wc-on-hold'    => 'Em espera',
		'wc-completed'  => 'Concluído',
		'wc-cancelled'  => 'Cancelado',
		'wc-refunded'   => 'Reembolsado',
		'wc-failed'     => 'Falhou',
	];
	foreach ($map as $key => $label) {
		if (isset($statuses[ $key ])) {
			$statuses[ $key ] = $label;
		}
	}
	return $statuses;
}, 20);
