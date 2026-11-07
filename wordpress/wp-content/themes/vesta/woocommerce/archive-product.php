<?php
/**
 * Curadoria — layout com filtros laterais (CatalogFilters do Next).
 *
 * @package Vesta
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$facets  = vesta_catalog_facets();
$current = vesta_catalog_current_filters();
$conditions = function_exists('vesta_core_conditions') ? vesta_core_conditions() : [];
$active_count = count(array_filter([
	$current['q'],
	$current['category'],
	$current['size'],
	$current['brand'],
	$current['condition'],
	$current['availability'] !== 'available' ? $current['availability'] : '',
]));
$total = (int) wc_get_loop_prop('total');
?>
<section class="vesta-catalog">
	<p class="eyebrow">Acervo</p>
	<h1 class="vesta-catalog__title">Peças</h1>
	<p class="vesta-catalog__note">Peças escolhidas uma a uma. A maioria existe em uma única unidade.</p>

	<div class="vesta-catalog__layout">
		<aside class="vesta-filters" data-filters>
			<div class="vesta-filters__mobile lg-hidden">
				<button type="button" class="vesta-filters__toggle" data-filters-toggle aria-expanded="false">
					<span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
						Filtros
						<?php if ($active_count) : ?>
							<span class="vesta-filters__badge"><?php echo esc_html((string) $active_count); ?></span>
						<?php endif; ?>
					</span>
					<svg class="vesta-filters__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
				</button>
				<?php if ($active_count) : ?>
					<a class="vesta-filters__clear" href="<?php echo esc_url(home_url('/produtos/')); ?>">Limpar</a>
				<?php endif; ?>
			</div>

			<div class="vesta-filters__panel" data-filters-panel>
				<div class="vesta-filters__head desktop-only">
					<h2>Filtro</h2>
					<?php if ($active_count) : ?>
						<a href="<?php echo esc_url(home_url('/produtos/')); ?>">Limpar</a>
					<?php endif; ?>
				</div>

				<form class="vesta-filters__form" method="get" action="<?php echo esc_url(home_url('/produtos/')); ?>">
					<label class="vesta-filters__label">
						Busca
						<input type="search" name="q" value="<?php echo esc_attr($current['q']); ?>" placeholder="Buscar peça">
					</label>

					<div class="vesta-filters__group">
						<p class="vesta-filters__label">Categoria</p>
						<div class="vesta-filters__chips">
							<a class="vesta-chip<?php echo $current['category'] === '' ? ' is-active' : ''; ?>" href="<?php echo esc_url(vesta_catalog_url(['category' => ''])); ?>">Todas</a>
							<?php foreach ($facets['categories'] as $cat) : ?>
								<a class="vesta-chip<?php echo $current['category'] === $cat['slug'] ? ' is-active' : ''; ?>" href="<?php echo esc_url(vesta_catalog_url(['category' => $current['category'] === $cat['slug'] ? '' : $cat['slug']])); ?>">
									<?php echo esc_html($cat['name']); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="vesta-filters__group">
						<p class="vesta-filters__label">Tamanho</p>
						<div class="vesta-filters__sizes">
							<?php foreach ($facets['sizes'] as $size) : ?>
								<a class="vesta-size<?php echo $current['size'] === $size ? ' is-active' : ''; ?>" href="<?php echo esc_url(vesta_catalog_url(['size' => $current['size'] === $size ? '' : $size])); ?>">
									<?php echo esc_html($size); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

					<label class="vesta-filters__label">
						Marca
						<select name="brand" onchange="this.form.submit()">
							<option value="">Todas</option>
							<?php foreach ($facets['brands'] as $brand) : ?>
								<option value="<?php echo esc_attr($brand); ?>" <?php selected($current['brand'], $brand); ?>><?php echo esc_html($brand); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="vesta-filters__label">
						Condição
						<select name="condition" onchange="this.form.submit()">
							<option value="">Todas</option>
							<?php foreach ($conditions as $value => $label) : ?>
								<option value="<?php echo esc_attr($value); ?>" <?php selected($current['condition'], $value); ?>><?php echo esc_html($label); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="vesta-filters__label">
						Disponibilidade
						<select name="availability" onchange="this.form.submit()">
							<option value="available" <?php selected($current['availability'], 'available'); ?>>Disponíveis</option>
							<option value="sold" <?php selected($current['availability'], 'sold'); ?>>Vendidas</option>
							<option value="all" <?php selected($current['availability'], 'all'); ?>>Todas</option>
						</select>
					</label>

					<?php if ($current['sort'] && $current['sort'] !== 'recent') : ?>
						<input type="hidden" name="sort" value="<?php echo esc_attr($current['sort']); ?>">
					<?php endif; ?>

					<button type="submit" class="btn vesta-filters__apply mobile-only">Ver resultados</button>
				</form>
			</div>
		</aside>

		<div class="vesta-catalog__results">
			<div class="vesta-catalog__sortbar">
				<p class="vesta-catalog__count">
					Você está vendo <strong><?php echo esc_html((string) $total); ?></strong>
					<?php echo esc_html($total === 1 ? 'resultado' : 'resultados'); ?>
				</p>
				<form method="get" action="<?php echo esc_url(home_url('/produtos/')); ?>" class="vesta-catalog__sort">
					<?php foreach (['q', 'category', 'size', 'brand', 'condition', 'availability'] as $key) : ?>
						<?php if (! empty($current[$key]) && ! ($key === 'availability' && $current[$key] === 'available')) : ?>
							<input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($current[$key]); ?>">
						<?php endif; ?>
					<?php endforeach; ?>
					<label>
						<span class="sr-only">Ordenar</span>
						<select name="sort" onchange="this.form.submit()">
							<option value="recent" <?php selected($current['sort'], 'recent'); ?>>Mais recentes</option>
							<option value="price-asc" <?php selected($current['sort'], 'price-asc'); ?>>Preço: menor → maior</option>
							<option value="price-desc" <?php selected($current['sort'], 'price-desc'); ?>>Preço: maior → menor</option>
							<option value="name" <?php selected($current['sort'], 'name'); ?>>Nome A–Z</option>
						</select>
					</label>
				</form>
			</div>

			<?php if (woocommerce_product_loop()) : ?>
				<?php woocommerce_product_loop_start(); ?>
				<?php while (have_posts()) : the_post(); ?>
					<?php wc_get_template_part('content', 'product'); ?>
				<?php endwhile; ?>
				<?php woocommerce_product_loop_end(); ?>
				<?php do_action('woocommerce_after_shop_loop'); ?>
			<?php else : ?>
				<p class="vesta-catalog__empty">Nenhuma peça encontrada com esses filtros.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
