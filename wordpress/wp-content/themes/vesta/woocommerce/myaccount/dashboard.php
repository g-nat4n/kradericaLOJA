<?php
/**
 * Dashboard minha conta — paridade Next (sacola em cards + pedidos agrupados).
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$user   = wp_get_current_user();
$first  = $user->first_name ?: $user->display_name;
$orders = wc_get_orders([
	'customer_id' => $user->ID,
	'limit'       => 20,
	'orderby'     => 'date',
	'order'       => 'DESC',
	'status'      => array_keys(wc_get_order_statuses()),
]);

/**
 * @param WC_Order $order
 * @return 'active'|'done'|'cancelled'
 */
$order_bucket = static function (WC_Order $order): string {
	$status = $order->get_status();
	if (in_array($status, ['cancelled', 'refunded', 'failed'], true)) {
		return 'cancelled';
	}
	if (in_array($status, ['completed'], true)) {
		return 'done';
	}
	return 'active';
};

$active    = [];
$done      = [];
$cancelled = [];
foreach ($orders as $order) {
	if (! $order instanceof WC_Order) {
		continue;
	}
	$bucket = $order_bucket($order);
	if ($bucket === 'done') {
		$done[] = $order;
	} elseif ($bucket === 'cancelled') {
		$cancelled[] = $order;
	} else {
		$active[] = $order;
	}
}

$cart_items = [];
$subtotal   = 0.0;
if (WC()->cart && ! WC()->cart->is_empty()) {
	foreach (WC()->cart->get_cart() as $key => $item) {
		$product = $item['data'] ?? null;
		if (! $product instanceof WC_Product) {
			continue;
		}
		$product_id = (int) ($item['product_id'] ?? 0);
		$line       = (float) ($item['line_total'] ?? 0);
		$subtotal  += $line;
		$cart_items[] = [
			'key'       => (string) $key,
			'product'   => $product,
			'id'        => $product_id,
			'brand'     => (string) get_post_meta($product_id, '_vesta_brand', true),
			'size'      => (string) get_post_meta($product_id, '_vesta_size', true),
			'permalink' => get_permalink($product_id),
			'line'      => $line,
		];
	}
}

$render_order_list = static function (string $title, string $hint, array $list): void {
	if (! $list) {
		return;
	}
	?>
	<div class="vesta-account__order-group">
		<h3 class="vesta-account__order-group-title"><?php echo esc_html($title); ?></h3>
		<p class="vesta-account__hint"><?php echo esc_html($hint); ?></p>
		<ul class="vesta-account__orders">
			<?php foreach ($list as $order) :
				if (! $order instanceof WC_Order) {
					continue;
				}
				$status     = $order->get_status();
				$status_lbl = wc_get_order_status_name($status);
				$bucket     = in_array($status, ['cancelled', 'refunded', 'failed'], true)
					? 'cancelled'
					: (in_array($status, ['completed'], true) ? 'done' : 'active');
				$names      = [];
				$cover_html = '';
				foreach ($order->get_items() as $item) {
					$names[] = $item->get_name();
					if ($cover_html === '' && $item instanceof WC_Order_Item_Product) {
						$pid = $item->get_product_id();
						$p   = $item->get_product();
						if ($p instanceof WC_Product) {
							$cover_html = $p->get_image('woocommerce_thumbnail', ['class' => 'vesta-account__order-cover-img']);
						} elseif ($pid) {
							$cover_html = get_the_post_thumbnail($pid, 'woocommerce_thumbnail', ['class' => 'vesta-account__order-cover-img']);
						}
					}
				}
				$date = $order->get_date_created();
				$date_label = $date ? $date->date_i18n('d M Y') : '';
				?>
				<li>
					<a class="vesta-account__order" href="<?php echo esc_url($order->get_view_order_url()); ?>">
						<div class="vesta-account__order-cover">
							<?php echo $cover_html ?: '<span class="vesta-account__order-cover-ph" aria-hidden="true"></span>'; // phpcs:ignore ?>
						</div>
						<div class="vesta-account__order-body">
							<div class="vesta-account__order-top">
								<strong>#<?php echo esc_html($order->get_order_number()); ?></strong>
								<span class="vesta-account__badge vesta-account__badge--<?php echo esc_attr($bucket); ?>">
									<?php echo esc_html($status_lbl); ?>
								</span>
							</div>
							<p class="vesta-account__order-items"><?php echo esc_html(implode(' · ', $names)); ?></p>
							<?php if ($date_label) : ?>
								<p class="vesta-account__order-meta"><?php echo esc_html($date_label); ?></p>
							<?php endif; ?>
						</div>
						<div class="vesta-account__order-side">
							<span class="vesta-account__order-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
							<span class="vesta-account__order-cta"><?php esc_html_e('Ver pedido', 'vesta'); ?></span>
						</div>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
};
?>
<section class="vesta-account">
	<p class="eyebrow">Área do cliente</p>
	<h1 class="vesta-account__hello">Olá, <?php echo esc_html($first); ?></h1>
	<p class="vesta-account__email"><?php echo esc_html($user->user_email); ?></p>

	<?php if (current_user_can('edit_products')) : ?>
		<p class="vesta-account__admin">
			<a class="btn btn--ghost" href="<?php echo esc_url(home_url('/admin/')); ?>">Painel Atelier</a>
		</p>
	<?php endif; ?>

	<nav class="vesta-account__shortcuts" aria-label="<?php esc_attr_e('Atalhos da conta', 'vesta'); ?>">
		<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"><?php esc_html_e('Pedidos', 'vesta'); ?></a>
		<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>"><?php esc_html_e('Endereços', 'vesta'); ?></a>
		<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>"><?php esc_html_e('Dados da conta', 'vesta'); ?></a>
	</nav>

	<section class="vesta-account__block">
		<h2 class="vesta-account__block-title">Na sacola</h2>
		<p class="vesta-account__hint">Peças que você ainda não finalizou.</p>

		<?php if ($cart_items) : ?>
			<div class="vesta-account__bag-rail" data-account-bag>
				<article class="vesta-insight-card">
					<h3 class="vesta-insight-card__title">Sua sacola</h3>
					<div class="vesta-insight-card__mosaic<?php echo count($cart_items) === 1 ? ' is-single' : ''; ?>">
						<?php
						$shown = array_slice($cart_items, 0, 4);
						$extra = max(count($cart_items) - 4, 0);
						foreach ($shown as $i => $row) :
							$product = $row['product'];
							?>
							<div class="vesta-insight-card__tile">
								<?php echo $product->get_image('woocommerce_thumbnail', ['class' => 'vesta-insight-card__img']); // phpcs:ignore ?>
								<?php if ($i === 3 && $extra > 0) : ?>
									<span class="vesta-insight-card__more">+<?php echo esc_html((string) $extra); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<p class="vesta-insight-card__names">
						<?php echo esc_html(implode(', ', array_map(static fn ($r) => $r['product']->get_name(), $cart_items))); ?>
					</p>
					<p class="vesta-insight-card__price"><?php echo wp_kses_post(vesta_format_brl($subtotal)); ?></p>
					<div class="vesta-insight-card__actions">
						<a class="vesta-insight-action" href="<?php echo esc_url(wc_get_cart_url()); ?>">Ir para a sacola</a>
					</div>
				</article>

				<?php foreach ($cart_items as $row) :
					$product = $row['product'];
					?>
					<article class="vesta-insight-card" data-account-bag-item="<?php echo esc_attr($row['key']); ?>">
						<h3 class="vesta-insight-card__title"><?php echo esc_html($product->get_name()); ?></h3>
						<a class="vesta-insight-card__link" href="<?php echo esc_url($row['permalink']); ?>">
							<div class="vesta-insight-card__photo">
								<?php echo $product->get_image('woocommerce_thumbnail', ['class' => 'vesta-insight-card__img']); // phpcs:ignore ?>
							</div>
							<?php if ($row['brand']) : ?>
								<p class="vesta-insight-card__name"><?php echo esc_html($row['brand']); ?></p>
							<?php endif; ?>
							<?php if ($row['size']) : ?>
								<p class="vesta-insight-card__meta">Tam. <?php echo esc_html($row['size']); ?></p>
							<?php endif; ?>
							<p class="vesta-insight-card__price"><?php echo wp_kses_post(vesta_format_brl((float) $product->get_price())); ?></p>
						</a>
						<div class="vesta-insight-card__actions vesta-insight-card__actions--split">
							<a class="vesta-insight-action" href="<?php echo esc_url(wc_get_checkout_url()); ?>">Finalizar</a>
							<button type="button" class="vesta-insight-remove" data-remove-cart="<?php echo esc_attr($row['key']); ?>" aria-label="<?php esc_attr_e('Remover da sacola', 'vesta'); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
							</button>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="vesta-account__empty">
				Sua sacola está vazia.
				<a href="<?php echo esc_url(home_url('/produtos/')); ?>">Ver coleção</a>
			</p>
		<?php endif; ?>
	</section>

	<section class="vesta-account__block">
		<h2 class="vesta-account__block-title">Meus pedidos</h2>
		<p class="vesta-account__hint">Acompanhe pedidos em andamento, finalizados e cancelados.</p>

		<?php if ($orders) : ?>
			<?php
			$render_order_list('Em andamento', 'Pagos, em preparação ou a caminho.', $active);
			$render_order_list('Finalizados', 'Pedidos entregues.', $done);
			$render_order_list('Cancelados', 'Compras canceladas ou reembolsadas.', $cancelled);
			?>
		<?php else : ?>
			<p class="vesta-account__empty">
				Você ainda não fez um pedido.
				<a href="<?php echo esc_url(home_url('/produtos/')); ?>">Ver curadoria</a>
			</p>
		<?php endif; ?>
	</section>

	<p class="vesta-account__logout">
		<a class="btn btn--ghost" href="<?php echo esc_url(wc_logout_url(home_url('/'))); ?>">Sair</a>
	</p>
</section>
