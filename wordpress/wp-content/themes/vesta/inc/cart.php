<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Sacola Vesta — drawer + AJAX (paridade com CartDrawer / CartProvider do Next).
 */
add_action('wp_enqueue_scripts', static function (): void {
	if (! function_exists('WC')) {
		return;
	}

	wp_enqueue_script('wc-cart-fragments');

	wp_enqueue_script(
		'vesta-cart',
		VESTA_THEME_URI . '/assets/js/cart.js',
		['jquery', 'wc-cart-fragments', 'vesta-main'],
		VESTA_THEME_VERSION,
		true
	);

	wp_localize_script('vesta-cart', 'vestaCart', [
		'ajaxUrl'      => admin_url('admin-ajax.php'),
		'nonce'        => wp_create_nonce('vesta_cart'),
		'checkoutUrl'  => wc_get_checkout_url(),
		'cartUrl'      => wc_get_cart_url(),
		'shopUrl'      => home_url('/produtos/'),
		'i18n'         => [
			'added'     => __('Peça adicionada à sacola.', 'vesta'),
			'exists'    => __('Esta peça já está na sua sacola.', 'vesta'),
			'sold'      => __('Esta peça já foi vendida.', 'vesta'),
			'error'     => __('Não foi possível atualizar a sacola.', 'vesta'),
			'empty'     => __('Sua sacola está esperando uma escolha especial.', 'vesta'),
		],
	]);
});

add_action('wp_footer', static function (): void {
	if (! function_exists('WC')) {
		return;
	}
	get_template_part('template-parts/cart', 'drawer');
}, 5);

add_filter('woocommerce_add_to_cart_fragments', static function (array $fragments): array {
	ob_start();
	vesta_cart_count_badge();
	$fragments['span.cart-count'] = (string) ob_get_clean();

	ob_start();
	vesta_render_cart_drawer_body();
	$fragments['div.vesta-drawer__body'] = '<div class="vesta-drawer__body">' . (string) ob_get_clean() . '</div>';

	ob_start();
	vesta_render_cart_drawer_footer();
	$fragments['div.vesta-drawer__footer'] = '<div class="vesta-drawer__footer">' . (string) ob_get_clean() . '</div>';

	return $fragments;
});

add_action('wp_ajax_vesta_add_to_cart', 'vesta_ajax_add_to_cart');
add_action('wp_ajax_nopriv_vesta_add_to_cart', 'vesta_ajax_add_to_cart');
add_action('wp_ajax_vesta_remove_from_cart', 'vesta_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_vesta_remove_from_cart', 'vesta_ajax_remove_from_cart');
add_action('wp_ajax_vesta_get_cart', 'vesta_ajax_get_cart');
add_action('wp_ajax_nopriv_vesta_get_cart', 'vesta_ajax_get_cart');
add_action('wp_ajax_vesta_apply_coupon', 'vesta_ajax_apply_coupon');
add_action('wp_ajax_nopriv_vesta_apply_coupon', 'vesta_ajax_apply_coupon');
add_action('wp_ajax_vesta_update_qty', 'vesta_ajax_update_qty');
add_action('wp_ajax_nopriv_vesta_update_qty', 'vesta_ajax_update_qty');

function vesta_ajax_verify(): void {
	check_ajax_referer('vesta_cart', 'nonce');
}

function vesta_ajax_add_to_cart(): void {
	vesta_ajax_verify();

	$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
	$quantity   = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;
	$buy_now    = ! empty($_POST['buy_now']);

	$product = wc_get_product($product_id);
	if (! $product || ! $product->is_purchasable()) {
		wp_send_json_error(['message' => __('Produto indisponível.', 'vesta')], 400);
	}

	if (! $product->is_in_stock()) {
		wp_send_json_error(['message' => __('Esta peça já foi vendida.', 'vesta')], 400);
	}

	$unique = get_post_meta($product_id, '_vesta_unique_piece', true);
	if ($unique !== 'no') {
		$quantity = 1;
	}

	$cart = WC()->cart;
	if (! $cart) {
		wp_send_json_error(['message' => __('Carrinho indisponível.', 'vesta')], 500);
	}

	foreach ($cart->get_cart() as $item) {
		if ((int) $item['product_id'] === $product_id) {
			wp_send_json_success([
				'message' => __('Esta peça já está na sua sacola.', 'vesta'),
				'already' => true,
				'count'   => $cart->get_cart_contents_count(),
				'open'    => true,
			]);
		}
	}

	$added = $cart->add_to_cart($product_id, $quantity);
	if (! $added) {
		$notices = wc_get_notices('error');
		wc_clear_notices();
		$msg = $notices[0]['notice'] ?? __('Não foi possível adicionar à sacola.', 'vesta');
		wp_send_json_error(['message' => wp_strip_all_tags((string) $msg)], 400);
	}

	WC()->cart->calculate_totals();

	wp_send_json_success([
		'message'     => __('Peça adicionada à sacola.', 'vesta'),
		'already'     => false,
		'count'       => $cart->get_cart_contents_count(),
		'open'        => ! $buy_now,
		'redirect'    => $buy_now ? wc_get_checkout_url() : '',
		'fragments'   => apply_filters('woocommerce_add_to_cart_fragments', []),
	]);
}

function vesta_ajax_remove_from_cart(): void {
	vesta_ajax_verify();
	$key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash((string) $_POST['cart_item_key'])) : '';
	if ($key === '' || ! WC()->cart) {
		wp_send_json_error(['message' => __('Item não encontrado.', 'vesta')], 400);
	}
	WC()->cart->remove_cart_item($key);
	WC()->cart->calculate_totals();
	wp_send_json_success([
		'count'     => WC()->cart->get_cart_contents_count(),
		'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
	]);
}

function vesta_ajax_get_cart(): void {
	vesta_ajax_verify();
	wp_send_json_success([
		'count'     => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
		'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
	]);
}

function vesta_ajax_update_qty(): void {
	vesta_ajax_verify();
	$key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash((string) $_POST['cart_item_key'])) : '';
	$qty = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;
	if ($key === '' || ! WC()->cart) {
		wp_send_json_error(['message' => __('Item não encontrado.', 'vesta')], 400);
	}
	$item = WC()->cart->get_cart_item($key);
	if (! $item) {
		wp_send_json_error(['message' => __('Item não encontrado.', 'vesta')], 400);
	}
	$product_id = (int) $item['product_id'];
	if (get_post_meta($product_id, '_vesta_unique_piece', true) !== 'no') {
		$qty = 1;
	}
	WC()->cart->set_quantity($key, $qty, true);
	WC()->cart->calculate_totals();
	wp_send_json_success([
		'count'     => WC()->cart->get_cart_contents_count(),
		'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
	]);
}

function vesta_ajax_apply_coupon(): void {
	vesta_ajax_verify();
	$code = isset($_POST['coupon']) ? wc_format_coupon_code(wp_unslash((string) $_POST['coupon'])) : '';
	if ($code === '' || ! WC()->cart) {
		wp_send_json_error(['message' => __('Informe um cupom.', 'vesta')], 400);
	}
	$result = WC()->cart->apply_coupon($code);
	WC()->cart->calculate_totals();
	if (! $result) {
		$notices = wc_get_notices('error');
		wc_clear_notices();
		$msg = $notices[0]['notice'] ?? __('Cupom inválido.', 'vesta');
		wp_send_json_error(['message' => wp_strip_all_tags((string) $msg)], 400);
	}
	wc_clear_notices();
	wp_send_json_success([
		'message'   => sprintf(__('Cupom %s aplicado.', 'vesta'), strtoupper($code)),
		'discount'  => WC()->cart->get_discount_total(),
		'subtotal'  => WC()->cart->get_subtotal(),
		'total'     => WC()->cart->get_total('edit'),
		'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
	]);
}

function vesta_render_cart_drawer_body(): void {
	$cart = WC()->cart;
	if (! $cart || $cart->is_empty()) {
		echo '<p class="vesta-drawer__empty">' . esc_html__('Sua sacola está esperando uma escolha especial.', 'vesta') . '</p>';
		return;
	}
	echo '<ul class="vesta-drawer__list">';
	foreach ($cart->get_cart() as $key => $item) {
		$product = $item['data'] ?? null;
		if (! $product instanceof WC_Product) {
			continue;
		}
		$product_id = (int) $item['product_id'];
		$brand      = (string) get_post_meta($product_id, '_vesta_brand', true);
		$size       = (string) get_post_meta($product_id, '_vesta_size', true);
		$permalink  = get_permalink($product_id);
		$thumb      = $product->get_image('woocommerce_thumbnail', ['class' => 'vesta-drawer__thumb-img']);
		?>
		<li class="vesta-drawer__item" data-cart-key="<?php echo esc_attr($key); ?>">
			<a class="vesta-drawer__thumb" href="<?php echo esc_url($permalink); ?>"><?php echo $thumb; // phpcs:ignore ?></a>
			<div class="vesta-drawer__meta">
				<?php if ($brand) : ?>
					<p class="vesta-drawer__brand"><?php echo esc_html($brand); ?></p>
				<?php endif; ?>
				<p class="vesta-drawer__name">
					<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($product->get_name()); ?></a>
				</p>
				<?php if ($size) : ?>
					<p class="vesta-drawer__size"><?php echo esc_html(sprintf(__('Tam. %s', 'vesta'), $size)); ?></p>
				<?php endif; ?>
				<p class="vesta-drawer__price"><?php echo wp_kses_post(WC()->cart->get_product_subtotal($product, (int) $item['quantity'])); ?></p>
			</div>
			<button type="button" class="vesta-remove-btn" data-remove-cart="<?php echo esc_attr($key); ?>" aria-label="<?php esc_attr_e('Remover da sacola', 'vesta'); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
			</button>
		</li>
		<?php
	}
	echo '</ul>';
}

function vesta_render_cart_drawer_footer(): void {
	$cart = WC()->cart;
	$subtotal = $cart ? $cart->get_cart_subtotal() : wc_price(0);
	?>
	<div class="vesta-drawer__subtotal">
		<span><?php esc_html_e('Subtotal', 'vesta'); ?></span>
		<strong><?php echo wp_kses_post($subtotal); ?></strong>
	</div>
	<a class="btn btn--wine vesta-drawer__checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
		<?php esc_html_e('Finalizar compra', 'vesta'); ?>
	</a>
	<a class="vesta-drawer__continue" href="<?php echo esc_url(home_url('/produtos/')); ?>">
		<?php esc_html_e('Continuar escolhendo', 'vesta'); ?>
	</a>
	<?php
}

add_filter('woocommerce_product_single_add_to_cart_text', static fn (): string => __('Adicionar à sacola', 'vesta'));
add_filter('woocommerce_product_add_to_cart_text', static fn (): string => __('Adicionar à sacola', 'vesta'));

add_action('woocommerce_single_product_summary', static function (): void {
	global $product;
	if (! $product instanceof WC_Product || $product->is_in_stock()) {
		return;
	}
	echo '<p class="vesta-sold-label">' . esc_html__('Peça vendida', 'vesta') . '</p>';
}, 30);

add_action('woocommerce_after_add_to_cart_button', static function (): void {
	global $product;
	if (! $product instanceof WC_Product || ! $product->is_in_stock()) {
		return;
	}
	?>
	<button type="button" class="button alt vesta-buy-now" data-buy-now="<?php echo esc_attr((string) $product->get_id()); ?>">
		<?php esc_html_e('Comprar agora', 'vesta'); ?>
	</button>
	<?php
});

/** Evita redirects padrão após add-to-cart; o JS abre o drawer. */
add_filter('woocommerce_add_to_cart_redirect', static function ($url) {
	if (wp_doing_ajax()) {
		return $url;
	}
	return $url;
});

add_filter('woocommerce_get_cart_url', static function (string $url): string {
	return home_url('/carrinho/');
});
