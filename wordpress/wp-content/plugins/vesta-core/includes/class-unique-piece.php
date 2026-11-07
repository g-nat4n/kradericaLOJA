<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Unique_Piece {
	public static function init(): void {
		add_action('woocommerce_payment_complete', [self::class, 'mark_sold']);
		add_action('woocommerce_order_status_processing', [self::class, 'mark_sold']);
		add_action('woocommerce_order_status_completed', [self::class, 'mark_sold']);
		add_filter('woocommerce_add_to_cart_validation', [self::class, 'validate_unique'], 10, 3);
	}

	public static function validate_unique(bool $passed, int $product_id, int $quantity): bool {
		$unique = get_post_meta($product_id, '_vesta_unique_piece', true);
		if ($unique === 'no') {
			return $passed;
		}
		if ($quantity > 1) {
			wc_add_notice(__('Esta é uma peça única — quantidade máxima 1.', 'vesta-core'), 'error');
			return false;
		}
		$product = wc_get_product($product_id);
		if ($product && ! $product->is_in_stock()) {
			wc_add_notice(__('Esta peça já foi vendida.', 'vesta-core'), 'error');
			return false;
		}
		return $passed;
	}

	public static function mark_sold(int $order_id): void {
		$order = wc_get_order($order_id);
		if (! $order) {
			return;
		}
		foreach ($order->get_items() as $item) {
			$product_id = $item->get_product_id();
			$unique     = get_post_meta($product_id, '_vesta_unique_piece', true);
			if ($unique === 'no') {
				continue;
			}
			$product = wc_get_product($product_id);
			if (! $product) {
				continue;
			}
			$product->set_stock_quantity(0);
			$product->set_stock_status('outofstock');
			$product->set_catalog_visibility('visible');
			$product->save();
			update_post_meta($product_id, '_vesta_status', 'SOLD');
		}
	}
}
