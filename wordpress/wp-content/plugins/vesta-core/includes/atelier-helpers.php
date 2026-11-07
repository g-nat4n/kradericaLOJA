<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * @return array<string, string>
 */
function vesta_atelier_nav(): array {
	return [
		''             => 'Visão geral',
		'produtos'     => 'Produtos',
		'best-sellers' => 'Mais vendidos',
		'pedidos'      => 'Pedidos',
		'categorias'   => 'Categorias',
		'clientes'     => 'Clientes',
	];
}

/**
 * @return array<string, string>
 */
function vesta_atelier_status_labels(): array {
	return [
		'DRAFT'     => 'Rascunho',
		'AVAILABLE' => 'Disponível',
		'RESERVED'  => 'Reservado',
		'SOLD'      => 'Vendido',
		'ARCHIVED'  => 'Arquivado',
	];
}

function vesta_atelier_product_status(WC_Product $product): string {
	$meta = (string) get_post_meta($product->get_id(), '_vesta_status', true);
	if ($meta !== '' && isset(vesta_atelier_status_labels()[$meta])) {
		return $meta;
	}
	$post = get_post($product->get_id());
	if ($post && $post->post_status === 'draft') {
		return 'DRAFT';
	}
	if ($post && $post->post_status === 'private') {
		return 'ARCHIVED';
	}
	if (! $product->is_in_stock()) {
		return 'SOLD';
	}
	return 'AVAILABLE';
}

function vesta_atelier_format_brl(float $amount): string {
	return 'R$ ' . number_format($amount, 2, ',', '.');
}

/**
 * Labels de situação do pedido em português (painel Atelier).
 *
 * @return array<string, string>
 */
function vesta_atelier_order_status_labels(): array {
	return [
		'wc-pending'    => 'Aguardando pagamento',
		'wc-processing' => 'Em processamento',
		'wc-on-hold'    => 'Em espera',
		'wc-completed'  => 'Concluído',
		'wc-cancelled'  => 'Cancelado',
		'wc-refunded'   => 'Reembolsado',
		'wc-failed'     => 'Falhou',
	];
}

function vesta_atelier_order_status_label(string $status): string {
	$key = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
	$labels = vesta_atelier_order_status_labels();
	if (isset($labels[ $key ])) {
		return $labels[ $key ];
	}
	return wc_get_order_status_name($status);
}

/** Pedido conta na receita (pago MP/Woo, mesmo se status atrasar). */
function vesta_atelier_order_counts_as_sale(WC_Order $order): bool {
	if ($order->has_status(['cancelled', 'refunded', 'failed', 'checkout-draft'])) {
		return false;
	}
	if ($order->get_date_paid()) {
		return true;
	}
	if ($order->is_paid()) {
		return true;
	}
	if ($order->has_status(['processing', 'completed'])) {
		return true;
	}
	// Flag da sincronização Vesta ↔ Mercado Pago (aprovado mesmo com status atrasado).
	if ((string) $order->get_meta('_vesta_mp_paid') === 'yes') {
		return true;
	}
	return false;
}

/**
 * @return WC_Product[]
 */
function vesta_atelier_list_products(): array {
	$ids = get_posts([
		'post_type'      => 'product',
		'post_status'    => ['publish', 'draft', 'private'],
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	$out = [];
	foreach ($ids as $id) {
		$p = wc_get_product((int) $id);
		if ($p instanceof WC_Product) {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * @return array{products:int,available:int,sold:int,orders:int,revenue:float}
 */
function vesta_atelier_stats(): array {
	$products = vesta_atelier_list_products();
	$available = 0;
	$sold = 0;
	foreach ($products as $p) {
		$status = vesta_atelier_product_status($p);
		if ($status === 'AVAILABLE') {
			$available++;
		}
		if ($status === 'SOLD') {
			$sold++;
		}
	}
	$orders = wc_get_orders([
		'limit'  => -1,
		'return' => 'objects',
		'status' => array_keys(wc_get_order_statuses()),
	]);
	$revenue = 0.0;
	$paid_count = 0;
	foreach ($orders as $order) {
		if (! $order instanceof WC_Order) {
			continue;
		}
		if (vesta_atelier_order_counts_as_sale($order)) {
			$revenue += (float) $order->get_total();
			$paid_count++;
		}
	}
	return [
		'products'  => count($products),
		'available' => $available,
		'sold'      => $sold,
		'orders'    => is_array($orders) ? count($orders) : 0,
		'paid'      => $paid_count,
		'revenue'   => $revenue,
	];
}

function vesta_atelier_nonce_field(): void {
	wp_nonce_field('vesta_atelier');
}
