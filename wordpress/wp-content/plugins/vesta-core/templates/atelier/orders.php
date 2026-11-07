<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$orders = wc_get_orders([
	'limit'   => 50,
	'orderby' => 'date',
	'order'   => 'DESC',
	'status'  => array_keys(wc_get_order_statuses()),
]);
?>
<p class="eyebrow">Operação</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Pedidos</h1>
<p class="atelier-lead">Acompanhe compras e atualize a situação.</p>

<?php if (! $orders) : ?>
	<p class="atelier-muted" style="margin-top:2rem;">Nenhum pedido ainda.</p>
<?php else : ?>
	<div class="atelier-list">
		<?php foreach ($orders as $order) :
			if (! $order instanceof WC_Order) {
				continue;
			}
			$items = $order->get_items();
			$names = [];
			$thumbs = [];
			$i = 0;
			foreach ($items as $item) {
				if (! $item instanceof WC_Order_Item_Product) {
					continue;
				}
				if ($i < 3) {
					$names[] = $item->get_name();
				}
				if ($i < 4) {
					$product = $item->get_product();
					if ($product instanceof WC_Product && $product->get_image_id()) {
						$thumbs[] = wp_get_attachment_image((int) $product->get_image_id(), 'thumbnail', false, ['class' => 'atelier-order-list__img']);
					} else {
						$thumbs[] = '<span class="atelier-order-list__ph" aria-hidden="true">·</span>';
					}
				}
				$i++;
			}
			$status_label = function_exists('vesta_atelier_order_status_label')
				? vesta_atelier_order_status_label($order->get_status())
				: wc_get_order_status_name($order->get_status());
			?>
			<article class="atelier-list-item">
				<p class="eyebrow">#<?php echo esc_html((string) $order->get_order_number()); ?> · <?php echo esc_html($order->get_date_created() ? $order->get_date_created()->date_i18n('d/m/Y H:i') : ''); ?></p>
				<h2><?php echo esc_html($status_label); ?></h2>
				<?php if ($thumbs) : ?>
					<div class="atelier-order-list__thumbs" aria-hidden="true">
						<?php foreach ($thumbs as $thumb) {
							echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} ?>
					</div>
				<?php endif; ?>
				<p class="atelier-muted" style="margin-top:0.5rem;">
					<?php echo esc_html($order->get_formatted_billing_full_name()); ?>
					· <?php echo esc_html($order->get_billing_email()); ?>
				</p>
				<p class="atelier-muted"><?php echo esc_html(implode(' · ', $names)); ?></p>
				<p class="atelier-card__price" style="margin-top:0.5rem;"><?php echo esc_html(vesta_atelier_format_brl((float) $order->get_total())); ?></p>
				<a class="atelier-btn atelier-btn--ghost" style="margin-top:0.85rem;" href="<?php echo esc_url(Vesta_Core_Atelier::url('pedidos/' . $order->get_id())); ?>">
					Ver e atualizar situação
				</a>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
