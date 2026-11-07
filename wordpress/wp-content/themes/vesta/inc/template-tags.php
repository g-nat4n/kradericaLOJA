<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

function vesta_cart_count_badge(): void {
	$count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	echo '<span class="cart-count"' . ($count ? '' : ' hidden') . '>' . esc_html((string) $count) . '</span>';
}

/**
 * @return array<int, WC_Product>
 */
function vesta_get_featured_products(int $limit = 8): array {
	if (! function_exists('wc_get_products')) {
		return [];
	}
	return wc_get_products([
		'status'   => 'publish',
		'limit'    => $limit,
		'featured' => true,
		'orderby'  => 'date',
		'order'    => 'DESC',
	]);
}

/**
 * @return array<int, WC_Product>
 */
function vesta_get_bestseller_products(int $limit = 12): array {
	if (! function_exists('wc_get_products')) {
		return [];
	}
	$ids = get_option('vesta_bestsellers', []);
	if (! is_array($ids) || ! $ids) {
		return wc_get_products([
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => 'popularity',
		]);
	}
	$ids = array_map('absint', $ids);
	return wc_get_products([
		'status'  => 'publish',
		'include' => array_slice($ids, 0, $limit),
		'orderby' => 'include',
		'limit'   => $limit,
	]);
}

function vesta_condition_label(string $condition): string {
	$map = [
		'NEW_WITH_TAG' => __('Nova com etiqueta', 'vesta'),
		'EXCELLENT'    => __('Excelente', 'vesta'),
		'VERY_GOOD'    => __('Muito boa', 'vesta'),
		'GOOD'         => __('Boa', 'vesta'),
		'VINTAGE'      => __('Retrô', 'vesta'),
	];
	return $map[$condition] ?? $condition;
}

/** Contatos oficiais Kra de Rica. */
function vesta_instagram_url(): string {
	return 'https://www.instagram.com/kraderica/';
}

function vesta_maps_url(): string {
	return 'https://maps.app.goo.gl/RyFP4FuVAeNhi1Ch9';
}

function vesta_maps_embed_url(): string {
	return 'https://www.google.com/maps?q=' . rawurlencode('Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba - PR') . '&output=embed&hl=pt-BR';
}

/**
 * URL do WhatsApp da loja (option `vesta_whatsapp` = só dígitos com DDI).
 * Fallback: (41) 99551-6601.
 */
function vesta_whatsapp_url(string $prefill = ''): string {
	$raw = (string) get_option('vesta_whatsapp', '5541995516601');
	$digits = preg_replace('/\\D+/', '', $raw) ?? '';
	if ($digits === '') {
		$digits = '5541995516601';
	}
	// 041... sem DDI → adiciona 55.
	if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
		$digits = '55' . substr($digits, 1);
	} elseif (strlen($digits) === 10 || strlen($digits) === 11) {
		$digits = '55' . $digits;
	}
	$url = 'https://wa.me/' . $digits;
	if ($prefill !== '') {
		$url = add_query_arg('text', rawurlencode($prefill), $url);
	}
	return $url;
}

/** Ícone SVG de rede social (instagram|whatsapp). */
function vesta_social_icon_svg(string $network): string {
	if ($network === 'instagram') {
		return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>';
	}
	if ($network === 'whatsapp') {
		return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.78 11.78 0 0 0 12.04 0C5.5 0 .16 5.34.16 11.9c0 2.1.55 4.15 1.6 5.96L0 24l6.3-1.65a11.86 11.86 0 0 0 5.74 1.46h.01c6.54 0 11.88-5.34 11.88-11.9 0-3.18-1.24-6.17-3.41-8.43zM12.05 21.15h-.01a9.27 9.27 0 0 1-4.72-1.29l-.34-.2-3.74.98 1-3.64-.22-.37a9.28 9.28 0 0 1-1.42-4.93c0-5.13 4.18-9.3 9.32-9.3a9.25 9.25 0 0 1 6.58 2.73 9.24 9.24 0 0 1 2.73 6.58c0 5.13-4.18 9.3-9.3 9.3zm5.4-6.96c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.48-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.48.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35z"/></svg>';
	}
	return '';
}

/** Valida CPF brasileiro (dígitos verificadores). */
function vesta_is_valid_cpf(string $cpf): bool {
	$cpf = preg_replace('/\\D+/', '', $cpf) ?? '';
	if (strlen($cpf) !== 11 || preg_match('/^(\\d)\\1{10}$/', $cpf)) {
		return false;
	}
	for ($t = 9; $t < 11; $t++) {
		$sum = 0;
		for ($i = 0; $i < $t; $i++) {
			$sum += (int) $cpf[ $i ] * (($t + 1) - $i);
		}
		$digit = ((10 * $sum) % 11) % 10;
		if ((int) $cpf[ $t ] !== $digit) {
			return false;
		}
	}
	return true;
}

function vesta_format_brl(float $amount): string {
	return 'R$&nbsp;' . number_format($amount, 2, ',', '.');
}

/**
 * Carrossel horizontal de produtos (estilo ML / RelatedProductRail do Next).
 *
 * @param WC_Product[] $products
 */
function vesta_product_rail(string $eyebrow, string $title, array $products): void {
	$products = array_values(array_filter($products, static fn ($p) => $p instanceof WC_Product));
	if (! $products) {
		return;
	}
	?>
	<section class="vesta-rail-section">
		<div class="vesta-rail-section__head">
			<?php if ($eyebrow !== '') : ?>
				<p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
			<?php endif; ?>
			<h2 class="vesta-rail-section__title"><?php echo esc_html($title); ?></h2>
		</div>
		<div class="product-rail" data-product-rail>
			<button type="button" class="product-rail__nav product-rail__nav--prev" data-rail-prev aria-label="<?php esc_attr_e('Anterior', 'vesta'); ?>">‹</button>
			<div class="product-rail__track" data-rail-track>
				<?php foreach ($products as $product) : ?>
					<div class="product-rail__item">
						<?php vesta_product_card($product); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="product-rail__nav product-rail__nav--next" data-rail-next aria-label="<?php esc_attr_e('Próximo', 'vesta'); ?>">›</button>
		</div>
	</section>
	<?php
}

/**
 * @return WC_Product[]
 */
function vesta_query_products(array $args): array {
	$defaults = [
		'status'  => 'publish',
		'limit'   => 12,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	];
	$query = array_merge($defaults, $args);
	$items = wc_get_products($query);
	return is_array($items) ? $items : [];
}

function vesta_product_card(WC_Product $product): void {
	$link      = get_permalink($product->get_id());
	$price     = (float) $product->get_price();
	$regular   = (float) $product->get_regular_price();
	$sold      = ! $product->is_in_stock();
	$discount  = ($regular > $price && $price > 0) ? (int) round((1 - $price / $regular) * 100) : 0;
	$bestseller = get_post_meta($product->get_id(), '_vesta_bestseller', true) === 'yes';
	$featured  = $bestseller || $product->is_featured();
	$show_installments = $price > 0 && function_exists('vesta_has_mercadopago') && vesta_has_mercadopago();
	$installment = $show_installments ? vesta_format_brl($price / 8) : '';
	$cats = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
	$cat_slug = is_array($cats) && $cats ? (string) $cats[0] : '';
	?>
	<article class="product-card" data-category="<?php echo esc_attr($cat_slug); ?>">
		<div class="product-card__media">
			<a href="<?php echo esc_url($link); ?>">
				<?php echo $product->get_image('woocommerce_thumbnail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<div class="product-card__badges">
				<?php if ($sold) : ?>
					<span class="product-card__badge"><?php esc_html_e('Esgotado', 'vesta'); ?></span>
				<?php else : ?>
					<?php if ($discount > 0) : ?>
						<span class="product-card__badge"><?php echo esc_html($discount . '% OFF'); ?></span>
					<?php endif; ?>
					<?php if ($featured) : ?>
						<span class="product-card__badge"><?php esc_html_e('Destaque', 'vesta'); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php if (! $sold) : ?>
				<button type="button" class="product-card__quick" data-add-to-cart="<?php echo esc_attr((string) $product->get_id()); ?>">
					<?php esc_html_e('Compra rápida', 'vesta'); ?>
				</button>
			<?php endif; ?>
		</div>
		<div class="product-card__body">
			<h3 class="product-card__title">
				<a href="<?php echo esc_url($link); ?>"><?php echo esc_html($product->get_name()); ?></a>
			</h3>
			<div class="product-card__prices">
				<span class="product-card__price"><?php echo wp_kses_post(vesta_format_brl($price)); ?></span>
				<?php if ($regular > $price) : ?>
					<span class="product-card__compare"><?php echo wp_kses_post(vesta_format_brl($regular)); ?></span>
				<?php endif; ?>
			</div>
			<?php if ($installment) : ?>
				<p class="product-card__installments">8x <?php echo wp_kses_post($installment); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}
