<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$is_edit = ($atelier_page ?? '') === 'produto-editar';
$product = null;
$id = 0;
if ($is_edit && ! empty($atelier_id)) {
	$product = wc_get_product((int) $atelier_id);
	$id = $product instanceof WC_Product ? $product->get_id() : 0;
}

$measures = $id ? get_post_meta($id, '_vesta_measurements', true) : [];
if (! is_array($measures)) {
	$measures = [];
}
$cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
$looks = get_terms(['taxonomy' => 'look', 'hide_empty' => false]);
if (is_wp_error($cats)) {
	$cats = [];
}
if (is_wp_error($looks)) {
	$looks = [];
}
$current_cat = $id ? wp_get_post_terms($id, 'product_cat', ['fields' => 'ids']) : [];
$current_look = $id ? wp_get_post_terms($id, 'look', ['fields' => 'ids']) : [];
$status = $product instanceof WC_Product ? vesta_atelier_product_status($product) : 'AVAILABLE';
$price = $product instanceof WC_Product ? (float) $product->get_price() : 0.0;
$regular = $product instanceof WC_Product ? (float) $product->get_regular_price() : 0.0;
$compare = ($regular > $price && $price > 0) ? $regular : 0.0;
$gallery_ids = $product instanceof WC_Product ? array_filter(array_merge([$product->get_image_id()], $product->get_gallery_image_ids())) : [];
?>
<a class="atelier-back" href="<?php echo esc_url(Vesta_Core_Atelier::url('produtos')); ?>">← Voltar aos produtos</a>
<p class="eyebrow"><?php echo $is_edit ? 'Editar peça' : 'Nova peça'; ?></p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;"><?php echo $is_edit && $product ? esc_html($product->get_name()) : 'Cadastrar peça'; ?></h1>
<p class="atelier-lead">Fotos, medidas, condição, estilo e destaque na home.</p>

<form class="atelier-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
	<?php vesta_atelier_nonce_field(); ?>
	<input type="hidden" name="action" value="vesta_atelier_save_product">
	<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">

	<div class="atelier-field">
		<label for="name">Nome</label>
		<input id="name" name="name" required value="<?php echo esc_attr($product ? $product->get_name() : ''); ?>">
	</div>
	<div class="atelier-field">
		<label for="description">Descrição</label>
		<textarea id="description" name="description" required minlength="3"><?php echo esc_textarea($product ? $product->get_description() : ''); ?></textarea>
	</div>
	<div class="atelier-field">
		<label for="story">História da peça</label>
		<textarea id="story" name="story"><?php echo esc_textarea((string) get_post_meta($id, '_vesta_story', true)); ?></textarea>
	</div>

	<div class="atelier-row atelier-row--3">
		<div class="atelier-field">
			<label for="brand">Marca</label>
			<input id="brand" name="brand" required value="<?php echo esc_attr((string) get_post_meta($id, '_vesta_brand', true)); ?>">
		</div>
		<div class="atelier-field">
			<label for="size">Tamanho</label>
			<input id="size" name="size" required value="<?php echo esc_attr((string) get_post_meta($id, '_vesta_size', true)); ?>">
		</div>
		<div class="atelier-field">
			<label for="color">Cor</label>
			<input id="color" name="color" required value="<?php echo esc_attr((string) get_post_meta($id, '_vesta_color', true)); ?>">
		</div>
	</div>

	<div class="atelier-row atelier-row--2">
		<div class="atelier-field">
			<label for="condition">Condição</label>
			<select id="condition" name="condition">
				<?php
				$cond = (string) get_post_meta($id, '_vesta_condition', true) ?: 'EXCELLENT';
				foreach (vesta_core_conditions() as $value => $label) :
					?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($cond, $value); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="atelier-field">
			<label for="status">Situação</label>
			<select id="status" name="status">
				<?php foreach (vesta_atelier_status_labels() as $value => $label) : ?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="atelier-row atelier-row--3">
		<div class="atelier-field">
			<label for="price">Preço (R$)</label>
			<input id="price" name="price" type="number" step="0.01" min="0.01" required value="<?php echo esc_attr($price > 0 ? (string) $price : ''); ?>">
		</div>
		<div class="atelier-field">
			<label for="compareAt">De (opcional)</label>
			<input id="compareAt" name="compareAt" type="number" step="0.01" value="<?php echo esc_attr($compare > 0 ? (string) $compare : ''); ?>">
		</div>
		<div class="atelier-field">
			<label for="stock">Estoque</label>
			<input id="stock" name="stock" type="number" value="<?php echo esc_attr((string) ($product ? (int) $product->get_stock_quantity() : 1)); ?>">
		</div>
	</div>

	<div class="atelier-field">
		<label for="categoryId">Categoria</label>
		<select id="categoryId" name="categoryId" required>
			<option value="">Selecione</option>
			<?php foreach ($cats as $term) : ?>
				<option value="<?php echo esc_attr((string) $term->term_id); ?>" <?php selected(in_array($term->term_id, $current_cat, true)); ?>>
					<?php echo esc_html($term->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="atelier-field">
		<label for="lookId">Estilo</label>
		<select id="lookId" name="lookId">
			<option value="">Nenhum</option>
			<?php foreach ($looks as $term) : ?>
				<option value="<?php echo esc_attr((string) $term->term_id); ?>" <?php selected(in_array($term->term_id, $current_look, true)); ?>>
					<?php echo esc_html($term->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="atelier-field">
		<label for="material">Material</label>
		<input id="material" name="material" value="<?php echo esc_attr((string) get_post_meta($id, '_vesta_material', true)); ?>">
	</div>

	<div class="atelier-field">
		<label for="photos">Fotos do computador</label>
		<input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
		<p class="atelier-hint">Só envio do computador. Pode escolher várias fotos de uma vez.</p>
	</div>

	<?php if ($gallery_ids) : ?>
		<div class="atelier-gallery">
			<?php foreach ($gallery_ids as $aid) :
				$url = wp_get_attachment_image_url((int) $aid, 'thumbnail');
				if (! $url) {
					continue;
				}
				?>
				<img src="<?php echo esc_url($url); ?>" alt="">
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="atelier-row atelier-row--5">
		<?php foreach (['bust' => 'Busto', 'waist' => 'Cintura', 'hip' => 'Quadril', 'length' => 'Comp.', 'shoulder' => 'Ombro'] as $key => $label) : ?>
			<div class="atelier-field">
				<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
				<input id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) ($measures[$key] ?? '')); ?>">
			</div>
		<?php endforeach; ?>
	</div>

	<label class="atelier-check">
		<input type="checkbox" name="featured" value="1" <?php checked($product instanceof WC_Product && $product->get_featured()); ?>>
		<span>
			<strong>Destaque na home</strong>
			<span class="atelier-hint" style="display:block;margin-top:0.2rem;">Aparece em Mais vendidos e na tela inicial.</span>
		</span>
	</label>
	<label class="atelier-check">
		<input type="checkbox" name="uniquePiece" value="1" <?php checked(! $id || get_post_meta($id, '_vesta_unique_piece', true) !== 'no'); ?>>
		<span>Peça única</span>
	</label>

	<button class="atelier-btn" type="submit">Salvar peça</button>
</form>

<?php if ($is_edit && $product instanceof WC_Product) : ?>
	<div class="atelier-card__actions" style="margin-top:1.5rem;">
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Marcar como vendida?">
			<?php vesta_atelier_nonce_field(); ?>
			<input type="hidden" name="action" value="vesta_atelier_product_action">
			<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
			<input type="hidden" name="atelier_action" value="mark_sold">
			<button class="atelier-btn atelier-btn--ghost" type="submit">Marcar vendida</button>
		</form>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php vesta_atelier_nonce_field(); ?>
			<input type="hidden" name="action" value="vesta_atelier_product_action">
			<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
			<input type="hidden" name="atelier_action" value="archive">
			<button class="atelier-btn atelier-btn--ghost" type="submit">Arquivar</button>
		</form>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="Excluir esta peça?">
			<?php vesta_atelier_nonce_field(); ?>
			<input type="hidden" name="action" value="vesta_atelier_product_action">
			<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $id); ?>">
			<input type="hidden" name="atelier_action" value="delete">
			<button class="atelier-btn atelier-btn--wine" type="submit">Excluir</button>
		</form>
	</div>
<?php endif; ?>
