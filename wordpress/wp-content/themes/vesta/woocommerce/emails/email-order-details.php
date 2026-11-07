<?php
/**
 * Detalhes do pedido no e-mail (PT · Vesta).
 *
 * @package Vesta
 * @var WC_Order $order
 * @var bool $sent_to_admin
 * @var bool $plain_text
 * @var WC_Email $email
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$text_align = is_rtl() ? 'right' : 'left';
?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 20px;border-collapse:collapse;">
	<tr>
		<td style="padding:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:#a39e94;">
			<?php esc_html_e('Resumo do pedido', 'vesta'); ?>
			· #<?php echo esc_html($order->get_order_number()); ?>
		</td>
	</tr>
	<?php foreach ($order->get_items() as $item_id => $item) :
		$product = $item->get_product();
		$image_url = '';
		if ($product instanceof WC_Product && $product->get_image_id()) {
			$image_url = wp_get_attachment_image_url((int) $product->get_image_id(), 'thumbnail') ?: '';
		}
		?>
		<tr>
			<td style="padding:12px 0;border-top:1px solid #2a2a2a;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="64" valign="top" style="padding-right:12px;">
							<?php if ($image_url) : ?>
								<img src="<?php echo esc_url($image_url); ?>" width="56" height="56" alt="" style="display:block;width:56px;height:56px;object-fit:cover;border:1px solid #2a2a2a;">
							<?php else : ?>
								<div style="width:56px;height:56px;background:#1a1a1a;border:1px solid #2a2a2a;line-height:56px;text-align:center;color:#a39e94;font-family:Georgia,serif;">
									<?php echo esc_html(mb_substr($item->get_name(), 0, 1)); ?>
								</div>
							<?php endif; ?>
						</td>
						<td valign="top" style="font-family:Georgia,'Times New Roman',serif;font-size:15px;color:#f5f2ea;text-align:<?php echo esc_attr($text_align); ?>;">
							<strong><?php echo esc_html($item->get_name()); ?></strong><br>
							<span style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#a39e94;">
								<?php echo esc_html(sprintf(__('Qtd. %d', 'vesta'), (int) $item->get_quantity())); ?>
							</span>
						</td>
						<td valign="top" align="right" style="font-family:Georgia,'Times New Roman',serif;font-size:15px;color:#f5f2ea;white-space:nowrap;">
							<?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<td style="padding:16px 0 0;border-top:1px solid #2a2a2a;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#a39e94;">
			<?php
			$ship = $order->get_shipping_method();
			if ($ship === '') {
				$ship = __('A combinar', 'vesta');
			}
			echo esc_html(sprintf(__('Frete · %s', 'vesta'), $ship));
			?>
		</td>
	</tr>
	<tr>
		<td style="padding:8px 0 0;font-family:Georgia,'Times New Roman',serif;font-size:20px;color:#f5f2ea;">
			<?php esc_html_e('Total', 'vesta'); ?>
			<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
		</td>
	</tr>
	<?php if ($order->get_payment_method_title()) : ?>
		<tr>
			<td style="padding:6px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#a39e94;">
				<?php
				echo esc_html(
					sprintf(
						__('Pagamento · %s', 'vesta'),
						$order->get_payment_method_title()
					)
				);
				?>
			</td>
		</tr>
	<?php endif; ?>
</table>
