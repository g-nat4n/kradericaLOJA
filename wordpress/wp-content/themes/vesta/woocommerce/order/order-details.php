<?php
/**
 * Detalhes do pedido (conta / view-order) com foto.
 *
 * @package Vesta
 * @var WC_Order|null $order
 * @var int|string|null $order_id
 * @var bool $show_downloads Optional.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! isset($order) || ! $order instanceof WC_Order) {
	$oid = isset($order_id) ? (int) $order_id : 0;
	$order = $oid > 0 ? wc_get_order($oid) : false;
}
if (! $order instanceof WC_Order) {
	return;
}

$order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']));
$show_customer_details = (int) $order->get_user_id() === (int) get_current_user_id() || current_user_can('manage_woocommerce');
$actions               = function_exists('wc_get_account_orders_actions') ? wc_get_account_orders_actions($order) : [];

// Só Pagar / Cancelar quando faz sentido. Sem "Ver" / "Comprar de novo".
unset($actions['view'], $actions['order-again']);
if ($order->is_paid() || $order->has_status(['completed', 'processing', 'cancelled', 'refunded', 'failed'])) {
	unset($actions['pay']);
}
// Cancelar: só se ainda não pagou (pending / on-hold).
if ($order->is_paid() || ! $order->has_status(['pending', 'on-hold'])) {
	unset($actions['cancel']);
}
?>

<section class="woocommerce-order-details vesta-order-details">
	<?php do_action('woocommerce_order_details_before_order_table', $order); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e('Detalhes do pedido', 'vesta'); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details vesta-order-details__table">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e('Produto', 'vesta'); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e('Total', 'vesta'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			do_action('woocommerce_order_details_before_order_table_items', $order);
			foreach ($order_items as $item_id => $item) {
				if (! $item instanceof WC_Order_Item_Product) {
					continue;
				}
				$product = $item->get_product();
				$note    = ($product instanceof WC_Product) ? (string) $product->get_purchase_note() : '';
				wc_get_template(
					'order/order-details-item.php',
					[
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $note,
						'product'            => $product instanceof WC_Product ? $product : false,
					]
				);
			}
			do_action('woocommerce_order_details_after_order_table_items', $order);
			?>
		</tbody>
		<tfoot>
			<?php foreach ($order->get_order_item_totals() as $key => $total) : ?>
				<tr>
					<th scope="row"><?php echo wp_kses_post($total['label']); ?></th>
					<td><?php echo wp_kses_post($total['value']); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if ($order->get_customer_note()) : ?>
				<tr>
					<th><?php esc_html_e('Observação', 'vesta'); ?></th>
					<td><?php echo wp_kses_post(nl2br(esc_html($order->get_customer_note()))); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php
	// Sem "ORDER AGAIN" do Woo — só o que a Vesta controla abaixo.
	remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
	do_action('woocommerce_order_details_after_order_table', $order);
	?>

	<?php if (! empty($actions)) : ?>
		<div class="vesta-order-details__actions">
			<div class="vesta-order-details__actions-btns">
				<?php foreach ($actions as $key => $action) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<?php
					$label = $action['name'];
					if ($key === 'pay') {
						$label = __('Pagar', 'vesta');
					} elseif ($key === 'cancel') {
						$label = __('Cancelar pedido', 'vesta');
					}
					$class = 'button vesta-order-btn vesta-order-btn--' . sanitize_html_class((string) $key);
					?>
					<a href="<?php echo esc_url($action['url']); ?>" class="<?php echo esc_attr($class); ?>">
						<?php echo esc_html($label); ?>
					</a>
				<?php endforeach; ?>
			</div>
			<?php if (isset($actions['cancel'])) : ?>
				<p class="vesta-order-details__actions-hint">
					<?php esc_html_e('Enquanto o pagamento não for confirmado, você pode cancelar o pedido.', 'vesta'); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php elseif ($order->is_paid() && ! $order->has_status(['cancelled', 'refunded'])) : ?>
		<p class="vesta-order-details__refund-note">
			<?php esc_html_e('Pedido pago. Para cancelar ou pedir estorno, fale conosco pelo Contato.', 'vesta'); ?>
			<a href="<?php echo esc_url(home_url('/contato/')); ?>"><?php esc_html_e('Falar com a Kra de Rica', 'vesta'); ?></a>
		</p>
	<?php endif; ?>
</section>

<?php
if ($show_customer_details) {
	wc_get_template('order/order-details-customer.php', ['order' => $order]);
}
