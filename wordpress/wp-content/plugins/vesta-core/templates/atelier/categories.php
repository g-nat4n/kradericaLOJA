<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
if (is_wp_error($terms)) {
	$terms = [];
}
?>
<p class="eyebrow">Organização</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Categorias</h1>
<p class="atelier-lead">Crie e organize as categorias da curadoria.</p>

<form class="atelier-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php vesta_atelier_nonce_field(); ?>
	<input type="hidden" name="action" value="vesta_atelier_save_category">
	<div class="atelier-field">
		<label for="name">Nome</label>
		<input id="name" name="name" required>
	</div>
	<div class="atelier-field">
		<label for="description">Descrição</label>
		<textarea id="description" name="description"></textarea>
	</div>
	<button class="atelier-btn" type="submit">Criar categoria</button>
</form>

<div class="atelier-list">
	<?php foreach ($terms as $term) :
		if ($term->slug === 'uncategorized') {
			continue;
		}
		?>
		<article class="atelier-list-item">
			<p class="eyebrow"><?php echo esc_html((string) $term->count); ?> peças</p>
			<h2><?php echo esc_html($term->name); ?></h2>
			<?php if ($term->description) : ?>
				<p class="atelier-muted" style="margin-top:0.5rem;"><?php echo esc_html($term->description); ?></p>
			<?php endif; ?>
			<?php if ((int) $term->count === 0) : ?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Excluir categoria?" style="margin-top:0.75rem;">
					<?php vesta_atelier_nonce_field(); ?>
					<input type="hidden" name="action" value="vesta_atelier_category_action">
					<input type="hidden" name="term_id" value="<?php echo esc_attr((string) $term->term_id); ?>">
					<input type="hidden" name="atelier_action" value="delete">
					<button class="atelier-mini atelier-mini--danger" type="submit">Excluir</button>
				</form>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
