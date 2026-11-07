<?php
/**
 * Pedido recebido — tela clara (padrão loja). E-mail usa modelo Compra confirmada.
 *
 * @package Vesta
 * @var WC_Order|false $order
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>

<div class="vesta-thankyou">
	<?php if ($order instanceof WC_Order) : ?>

		<?php if ($order->has_status('failed')) : ?>
			<h1 class="vesta-thankyou__title"><?php esc_html_e('Não foi possível concluir o pagamento', 'vesta'); ?></h1>
			<p class="vesta-thankyou__lead">
				<?php esc_html_e('Houve um problema com o pagamento. Você pode tentar novamente.', 'vesta'); ?>
			</p>
			<div class="vesta-thankyou__actions">
				<a class="btn" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">
					<?php esc_html_e('Tentar pagar de novo', 'vesta'); ?>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url(home_url('/')); ?>">
					<?php esc_html_e('Voltar à loja', 'vesta'); ?>
				</a>
			</div>
		<?php else : ?>
			<h1 class="vesta-thankyou__title"><?php esc_html_e('Obrigada pelo seu pedido', 'vesta'); ?></h1>
			<p class="vesta-thankyou__lead">
				<?php
				if ($order->is_paid() || $order->has_status(['processing', 'completed'])) {
					esc_html_e('Recebemos o pagamento. O atelier entra em contato para combinar a entrega ou a retirada.', 'vesta');
				} else {
					esc_html_e('Recebemos o seu pedido. Assim que o pagamento for confirmado, o atelier combina a entrega ou a retirada.', 'vesta');
				}
				?>
			</p>

			<ul class="vesta-thankyou__meta">
				<li>
					<span><?php esc_html_e('Número do pedido', 'vesta'); ?></span>
					<strong>#<?php echo esc_html($order->get_order_number()); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e('Data', 'vesta'); ?></span>
					<strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e('E-mail', 'vesta'); ?></span>
					<strong><?php echo esc_html($order->get_billing_email()); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e('Total', 'vesta'); ?></span>
					<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
				</li>
				<?php if ($order->get_payment_method_title()) : ?>
					<li>
						<span><?php esc_html_e('Pagamento', 'vesta'); ?></span>
						<strong><?php echo esc_html($order->get_payment_method_title()); ?></strong>
					</li>
				<?php endif; ?>
			</ul>

			<?php
			// PIX pendente: QR do gateway. Cartão/aprovado: sem UI do Mercado Pago.
			$method = (string) $order->get_payment_method();
			$show_pix = ($method === 'woo-mercado-pago-pix' && ! $order->is_paid() && ! $order->has_status(['processing', 'completed']));
			if ($show_pix) {
				do_action('woocommerce_thankyou_' . $method, $order->get_id());
			}
			remove_all_actions('woocommerce_thankyou_' . $method);
			do_action('woocommerce_thankyou', $order->get_id());
			?>

			<section class="vesta-thankyou__details">
				<h2><?php esc_html_e('Detalhes do pedido', 'vesta'); ?></h2>
				<div class="vesta-thankyou__table-wrap">
					<table class="vesta-thankyou__table">
						<thead>
							<tr>
								<th><?php esc_html_e('Produto', 'vesta'); ?></th>
								<th><?php esc_html_e('Total', 'vesta'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($order->get_items() as $item) : ?>
								<tr>
									<td>
										<?php echo esc_html($item->get_name()); ?>
										<span class="vesta-thankyou__qty">×<?php echo esc_html((string) $item->get_quantity()); ?></span>
										<?php wc_display_item_meta($item); ?>
									</td>
									<td><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<?php foreach ($order->get_order_item_totals() as $total) : ?>
								<tr>
									<th><?php echo wp_kses_post($total['label']); ?></th>
									<td><?php echo wp_kses_post($total['value']); ?></td>
								</tr>
							<?php endforeach; ?>
						</tfoot>
					</table>
				</div>
			</section>

			<?php if ($order->has_billing_address()) : ?>
				<section class="vesta-thankyou__address">
					<h2><?php esc_html_e('Endereço de cobrança', 'vesta'); ?></h2>
					<address><?php echo wp_kses_post($order->get_formatted_billing_address()); ?></address>
					<?php if ($order->get_billing_phone()) : ?>
						<p><?php echo esc_html($order->get_billing_phone()); ?></p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<div class="vesta-thankyou__actions">
				<?php
				// Área do cliente — key só para convidado (cliente logado não precisa).
				$track_url = wc_get_endpoint_url('view-order', (string) $order->get_id(), wc_get_page_permalink('myaccount'));
				if (! is_user_logged_in() && $order->get_order_key()) {
					$track_url = add_query_arg('key', $order->get_order_key(), $track_url);
				}
				?>
				<a class="btn" href="<?php echo esc_url($track_url); ?>">
					<?php esc_html_e('Acompanhar pedido', 'vesta'); ?>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url(home_url('/')); ?>">
					<?php esc_html_e('Continuar na loja', 'vesta'); ?>
				</a>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<h1 class="vesta-thankyou__title"><?php esc_html_e('Pedido não encontrado', 'vesta'); ?></h1>
		<p class="vesta-thankyou__lead">
			<?php esc_html_e('Não encontramos este pedido. Se você acabou de comprar, confira o e-mail de confirmação.', 'vesta'); ?>
		</p>
		<div class="vesta-thankyou__actions">
			<a class="btn" href="<?php echo esc_url(home_url('/')); ?>">
				<?php esc_html_e('Voltar à loja', 'vesta'); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
