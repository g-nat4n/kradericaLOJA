<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$products = vesta_atelier_list_products();
$labels = vesta_atelier_status_labels();
$aviso = isset($_GET['aviso']) ? sanitize_text_field(wp_unslash((string) $_GET['aviso'])) : '';
?>
<div class="atelier-head">
	<div>
		<p class="eyebrow">Acervo</p>
		<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Produtos</h1>
		<p class="atelier-lead">Use vender, arquivar ou excluir sem abrir a edição.</p>
	</div>
	<a class="atelier-btn" href="<?php echo esc_url(Vesta_Core_Atelier::url('produtos/novo')); ?>">Nova peça</a>
</div>

<?php if ($aviso) : ?>
	<p class="atelier-alert"><?php echo esc_html($aviso); ?></p>
<?php endif; ?>

<?php if (! $products) : ?>
	<p class="atelier-muted" style="margin-top:2.5rem;">Nenhuma peça cadastrada ainda.</p>
<?php else : ?>
	<ul class="atelier-grid">
		<?php foreach ($products as $product) :
			$id = $product->get_id();
			$status = vesta_atelier_product_status($product);
			$sold = $status === 'SOLD';
			$brand = (string) get_post_meta($id, '_vesta_brand', true);
			$size = (string) get_post_meta($id, '_vesta_size', true);
			$img = $product->get_image_id() ? wp_get_attachment_image_url($product->get_image_id(), 'medium') : '';
			?>
			<li>
				<article class="atelier-card">
					<a href="<?php echo esc_url(Vesta_Core_Atelier::url('produtos/' . $id)); ?>">
						<div class="atelier-card__media">
							<?php if ($img) : ?>
								<img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="<?php echo $sold ? 'is-sold' : ''; ?>">
							<?php endif; ?>
							<?php if ($sold) : ?><span class="atelier-sold-bar">Vendida</span><?php endif; ?>
							<?php if ($product->get_featured()) : ?><span class="atelier-badge">Destaque</span><?php endif; ?>
						</div>
						<div style="padding-top:0.65rem;">
							<p class="atelier-card__meta"><?php echo esc_html(trim($brand . ($size ? ' · Tam. ' . $size : ''))); ?></p>
							<h2 class="atelier-card__title"><?php echo esc_html($product->get_name()); ?></h2>
							<p class="atelier-card__price"><?php echo esc_html(vesta_atelier_format_brl((float) $product->get_price())); ?></p>
							<span class="atelier-card__status"><?php echo esc_html($labels[$status] ?? $status); ?></span>
						</div>
					</a>
					<div class="atelier-card__actions">
						<a class="atelier-mini" href="<?php echo esc_url(Vesta_Core_Atelier::url('produtos/' . $id)); ?>">Editar</a>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<?php vesta_atelier_nonce_field(); ?>
							<input type="hidden" name="action" value="vesta_atelier_product_action">
							<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
							<input type="hidden" name="atelier_action" value="toggle_featured">
							<button class="atelier-mini" type="submit"><?php echo $product->get_featured() ? 'Tirar da home' : 'Na home'; ?></button>
						</form>
						<?php if (! $sold) : ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Marcar esta peça como vendida?">
								<?php vesta_atelier_nonce_field(); ?>
								<input type="hidden" name="action" value="vesta_atelier_product_action">
								<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
								<input type="hidden" name="atelier_action" value="mark_sold">
								<button class="atelier-mini" type="submit">Vendida</button>
							</form>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<?php vesta_atelier_nonce_field(); ?>
								<input type="hidden" name="action" value="vesta_atelier_product_action">
								<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
								<input type="hidden" name="atelier_action" value="restore">
								<button class="atelier-mini" type="submit">Reabrir</button>
							</form>
						<?php endif; ?>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<?php vesta_atelier_nonce_field(); ?>
							<input type="hidden" name="action" value="vesta_atelier_product_action">
							<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
							<input type="hidden" name="atelier_action" value="<?php echo $status === 'ARCHIVED' ? 'restore' : 'archive'; ?>">
							<button class="atelier-mini" type="submit"><?php echo $status === 'ARCHIVED' ? 'Disponibilizar' : 'Arquivar'; ?></button>
						</form>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Excluir esta peça?">
							<?php vesta_atelier_nonce_field(); ?>
							<input type="hidden" name="action" value="vesta_atelier_product_action">
							<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
							<input type="hidden" name="atelier_action" value="delete">
							<button class="atelier-mini atelier-mini--danger" type="submit">Excluir</button>
						</form>
					</div>
				</article>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
