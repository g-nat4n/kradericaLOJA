<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$products = vesta_atelier_list_products();
$on = [];
$off = [];
foreach ($products as $p) {
	if ($p->get_featured() || get_post_meta($p->get_id(), '_vesta_bestseller', true) === 'yes') {
		$on[] = $p;
	} else {
		$off[] = $p;
	}
}
?>
<p class="eyebrow">Curadoria</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Mais vendidos</h1>
<p class="atelier-lead">Escolha quais peças aparecem na home e no carrossel de destaques.</p>
<p class="atelier-muted" style="margin-top:1rem;"><?php echo esc_html(count($on) . ' em destaque · ' . count($off) . ' para adicionar'); ?></p>

<section class="atelier-section">
	<h2>Na vitrine agora</h2>
	<?php if (! $on) : ?>
		<p class="atelier-muted" style="margin-top:1rem;">Nenhuma peça em destaque.</p>
	<?php else : ?>
		<ul class="atelier-grid">
			<?php foreach ($on as $product) :
				$id = $product->get_id();
				$img = $product->get_image_id() ? wp_get_attachment_image_url($product->get_image_id(), 'medium') : '';
				?>
				<li>
					<article class="atelier-card">
						<div class="atelier-card__media">
							<?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt=""><?php endif; ?>
							<span class="atelier-badge">No carrossel</span>
							<?php if (! $product->is_in_stock()) : ?><span class="atelier-sold-bar">Sem estoque</span><?php endif; ?>
						</div>
						<div style="padding-top:0.65rem;">
							<h2 class="atelier-card__title"><?php echo esc_html($product->get_name()); ?></h2>
							<p class="atelier-card__price"><?php echo esc_html(vesta_atelier_format_brl((float) $product->get_price())); ?></p>
						</div>
						<div class="atelier-card__actions">
							<a class="atelier-mini" href="<?php echo esc_url(Vesta_Core_Atelier::url('produtos/' . $id)); ?>">Editar</a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<?php vesta_atelier_nonce_field(); ?>
								<input type="hidden" name="action" value="vesta_atelier_toggle_bestseller">
								<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
								<button class="atelier-mini" type="submit">Remover</button>
							</form>
						</div>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>

<section class="atelier-section">
	<h2>Adicionar aos mais vendidos</h2>
	<?php if (! $off) : ?>
		<p class="atelier-muted" style="margin-top:1rem;">Todas as peças já estão na vitrine.</p>
	<?php else : ?>
		<ul class="atelier-grid">
			<?php foreach ($off as $product) :
				$id = $product->get_id();
				$img = $product->get_image_id() ? wp_get_attachment_image_url($product->get_image_id(), 'medium') : '';
				?>
				<li>
					<article class="atelier-card">
						<div class="atelier-card__media">
							<?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt=""><?php endif; ?>
						</div>
						<div style="padding-top:0.65rem;">
							<h2 class="atelier-card__title"><?php echo esc_html($product->get_name()); ?></h2>
						</div>
						<div class="atelier-card__actions">
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<?php vesta_atelier_nonce_field(); ?>
								<input type="hidden" name="action" value="vesta_atelier_toggle_bestseller">
								<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
								<input type="hidden" name="add" value="1">
								<button class="atelier-mini" type="submit">Adicionar</button>
							</form>
						</div>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
