<?php
/**
 * Item do pedido com miniatura.
 *
 * @package Vesta
 * @var WC_Order           $order
 * @var WC_Order_Item      $item
 * @var int|string         $item_id
 * @var bool               $show_purchase_note
 * @var string             $purchase_note
 * @var WC_Product|false|null $product
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
	return;
}

$qty          = (int) $item->get_quantity();
$refunded_qty = $order->get_qty_refunded_for_item((int) $item_id);
if ($refunded_qty) {
	$qty_display = '<del>' . esc_html((string) $qty) . '</del> <ins>' . esc_html((string) ($qty - ( $refunded_qty * -1 ))) . '</ins>';
} else {
	$qty_display = esc_html((string) $qty);
}

$image_html = '';
if ($product instanceof WC_Product) {
	$image_id = (int) $product->get_image_id();
	if ($image_id > 0) {
		$image_html = wp_get_attachment_image(
			$image_id,
			'woocommerce_thumbnail',
			false,
			[
				'class' => 'vesta-order-item__thumb-img',
				'alt'   => '',
			]
		);
	}
}
if ($image_html === '') {
	$initial = function_exists('mb_substr')
		? mb_substr($item->get_name(), 0, 1)
		: substr($item->get_name(), 0, 1);
	$image_html = '<span class="vesta-order-item__thumb-ph" aria-hidden="true">' . esc_html($initial) . '</span>';
}

$name = $item->get_name();
$is_visible = $product instanceof WC_Product && $product->is_visible();
$permalink  = '';
if ($is_visible && $product instanceof WC_Product) {
	$permalink = (string) $product->get_permalink($item);
}
$permalink = (string) apply_filters('woocommerce_order_item_permalink', $permalink, $item, $order);
?>

<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order)); ?>">
	<td class="woocommerce-table__product-name product-name">
		<div class="vesta-order-item">
			<div class="vesta-order-item__thumb"><?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="vesta-order-item__body">
				<?php
				echo wp_kses_post(
					apply_filters(
						'woocommerce_order_item_name',
						$permalink !== '' ? sprintf('<a href="%s">%s</a>', esc_url($permalink), esc_html($name)) : esc_html($name),
						$item,
						$is_visible
					)
				);
				echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">&times;&nbsp;' . $qty_display . '</strong>', $item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
				wc_display_item_meta($item);
				do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
				?>
			</div>
		</div>
	</td>
	<td class="woocommerce-table__product-total product-total">
		<?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
	</td>
</tr>

<?php if ($show_purchase_note && $purchase_note) : ?>
	<tr class="woocommerce-table__product-purchase-note product-purchase-note">
		<td colspan="2"><?php echo wp_kses_post(wpautop(do_shortcode($purchase_note))); ?></td>
	</tr>
<?php endif; ?>
