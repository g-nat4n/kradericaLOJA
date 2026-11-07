<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$users = get_users([
	'orderby' => 'registered',
	'order'   => 'DESC',
	'number'  => 100,
]);
?>
<p class="eyebrow">Pessoas</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Clientes</h1>
<p class="atelier-lead">Quem compra na Kra de Rica — e quem pode administrar o Atelier.</p>

<div class="atelier-list">
	<?php foreach ($users as $user) :
		$is_admin = user_can($user, 'edit_products');
		$orders = wc_get_orders([
			'customer_id' => $user->ID,
			'limit'       => -1,
			'return'      => 'ids',
			'status'      => array_keys(wc_get_order_statuses()),
		]);
		$count = is_array($orders) ? count($orders) : 0;
		?>
		<article class="atelier-list-item">
			<p class="eyebrow"><?php echo $is_admin ? 'Administrador' : 'Cliente'; ?></p>
			<h2><?php echo esc_html($user->display_name ?: $user->user_login); ?></h2>
			<p class="atelier-muted" style="margin-top:0.5rem;"><?php echo esc_html($user->user_email); ?> · <?php echo esc_html((string) $count); ?> pedidos</p>
			<?php if (! $is_admin && current_user_can('manage_options') && (int) $user->ID !== (int) get_current_user_id()) : ?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Tornar esta pessoa administradora do Atelier?" style="margin-top:0.75rem;">
					<?php vesta_atelier_nonce_field(); ?>
					<input type="hidden" name="action" value="vesta_atelier_promote_user">
					<input type="hidden" name="user_id" value="<?php echo esc_attr((string) $user->ID); ?>">
					<button class="atelier-mini" type="submit">Tornar admin</button>
				</form>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
