<?php
declare(strict_types=1);
get_header();

$img_base = VESTA_THEME_URI . '/assets/img';
$hero_img = $img_base . '/hero-editorial.png';
$bestsellers = function_exists('vesta_get_bestseller_products') ? vesta_get_bestseller_products(8) : [];
$novidades = function_exists('vesta_query_products')
	? vesta_query_products(['limit' => 8, 'orderby' => 'date', 'order' => 'DESC'])
	: [];
if (! $novidades && $bestsellers) {
	$novidades = $bestsellers;
}

$category_chips = [
	['Blazers', add_query_arg('category', 'blazers', home_url('/produtos/')), $img_base . '/brand/cat-blazers.png'],
	['Camisas', add_query_arg('category', 'camisas', home_url('/produtos/')), $img_base . '/brand/cat-camisas.png'],
	['Vestidos', add_query_arg('category', 'vestidos', home_url('/produtos/')), $img_base . '/brand/cat-vestidos.png'],
	['Casacos', add_query_arg('category', 'casacos', home_url('/produtos/')), $img_base . '/brand/cat-casacos.png'],
	['Sapatos', add_query_arg('category', 'sapatos', home_url('/produtos/')), $img_base . '/brand/cat-sapatos.png'],
	['Bolsas', add_query_arg('category', 'bolsas', home_url('/produtos/')), $img_base . '/brand/cat-acessorios.png'],
];

$novidade_fallback = [
	['Blazer selecionado', 'blazers', add_query_arg('category', 'blazers', home_url('/produtos/')), $img_base . '/brand/cat-blazers.png'],
	['Camisa selecionada', 'camisas', add_query_arg('category', 'camisas', home_url('/produtos/')), $img_base . '/brand/cat-camisas.png'],
	['Vestido selecionado', 'vestidos', add_query_arg('category', 'vestidos', home_url('/produtos/')), $img_base . '/brand/cat-vestidos.png'],
	['Casaco selecionado', 'casacos', add_query_arg('category', 'casacos', home_url('/produtos/')), $img_base . '/brand/cat-casacos.png'],
	['Sapato selecionado', 'sapatos', add_query_arg('category', 'sapatos', home_url('/produtos/')), $img_base . '/brand/cat-sapatos.png'],
	['Bolsa selecionada', 'bolsas', add_query_arg('category', 'bolsas', home_url('/produtos/')), $img_base . '/brand/cat-acessorios.png'],
];

$tabs = ['Blazers', 'Camisas', 'Vestidos', 'Sapatos', 'Bolsas'];
?>
<main id="top">
	<section class="hero" id="inicio">
		<div class="hero__copy">
			<div class="hero__copy-inner" data-reveal>
				<p class="hero__kicker">Moda feminina selecionada · Curitiba</p>
				<h1 class="hero__title">Vista o que representa você.</h1>
				<p class="hero__desc">Brechó com estética de boutique: peças novas e selecionadas, curadoria atenta e presença editorial.</p>
				<div class="hero__actions">
					<a class="btn" href="<?php echo esc_url(home_url('/produtos/')); ?>">Explorar peças →</a>
					<a class="btn btn--outline-light" href="<?php echo esc_url(home_url('/#categorias')); ?>">Ver categorias</a>
				</div>
				<div class="hero__signature">
					<span class="gold-line" aria-hidden="true"></span>
					<p>Peças novas e selecionadas</p>
				</div>
			</div>
		</div>
		<div class="hero__media" data-reveal>
			<img src="<?php echo esc_url($hero_img); ?>" alt="Editorial Brechó Kra de Rica">
			<span class="hero__badge">Kra de Rica / 01</span>
			<div class="hero__card">
				<span>Curadoria</span>
				<p>Elegância com personalidade, sem ruído visual.</p>
			</div>
		</div>
	</section>

	<section class="trust-bar">
		<div class="container-main trust-bar__inner">
			<div class="trust-item">
				<svg class="trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 3l2.2 4.5L19 8.2l-3.5 3.4.8 4.9L12 14.2 7.7 16.5l.8-4.9L5 8.2l4.8-.7L12 3z"/></svg>
				<div>
					<strong>Peças novas e selecionadas</strong>
					<span>curadoria de boutique</span>
				</div>
			</div>
			<div class="trust-item">
				<svg class="trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/></svg>
				<div>
					<strong>Estética editorial</strong>
					<span>luz quente e contraste controlado</span>
				</div>
			</div>
			<div class="trust-item">
				<svg class="trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
				<div>
					<strong>Pagamento seguro</strong>
					<span>Pix e cartão</span>
				</div>
			</div>
			<div class="trust-item">
				<svg class="trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 7h11v10H3z"/><path d="M14 10h4l3 3v4h-7"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/></svg>
				<div>
					<strong>Centro de Curitiba</strong>
					<span>atendimento e retirada</span>
				</div>
			</div>
		</div>
	</section>

	<section class="section" id="pecas">
		<div class="container-main">
			<p class="eyebrow section-kicker">Peças</p>
			<h2 class="section-title">Novidades</h2>
			<div class="carousel-tabs" data-carousel-tabs>
				<?php foreach ($tabs as $i => $tab) : ?>
					<button type="button" data-tab="<?php echo esc_attr(strtolower($tab)); ?>" class="<?php echo $i === 0 ? 'is-active' : ''; ?>">
						<?php echo esc_html($tab); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="product-rail" data-product-rail>
				<button type="button" class="product-rail__nav product-rail__nav--prev" data-rail-prev aria-label="Anterior">‹</button>
				<div class="product-rail__track" data-rail-track>
					<?php if ($novidades) : ?>
						<?php foreach ($novidades as $product) : ?>
							<?php vesta_product_card($product); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<?php foreach ($novidade_fallback as [$label, $cat, $url, $img]) : ?>
							<article class="product-card" data-category="<?php echo esc_attr($cat); ?>">
								<div class="product-card__media">
									<a href="<?php echo esc_url($url); ?>">
										<img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($label); ?>">
									</a>
								</div>
								<div class="product-card__body">
									<h3 class="product-card__title">
										<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
									</h3>
								</div>
							</article>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<button type="button" class="product-rail__nav product-rail__nav--next" data-rail-next aria-label="Próximo">›</button>
			</div>
		</div>
	</section>

	<section class="section section--cream" id="categorias">
		<div class="container-main">
			<p class="eyebrow section-kicker">Curadoria</p>
			<h2 class="section-title">Categorias</h2>
			<div class="category-chips">
				<?php foreach ($category_chips as [$label, $url, $img]) : ?>
					<a class="category-chip" href="<?php echo esc_url($url); ?>" data-reveal>
						<img src="<?php echo esc_url($img); ?>" alt="">
						<span><?php echo esc_html($label); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="editorial-band">
		<div class="container-main editorial-band__grid">
			<div data-reveal>
				<p class="eyebrow" style="color:var(--gold)">Posicionamento</p>
				<h2>Boutique com <em>alma</em> de brechó.</h2>
			</div>
			<div class="editorial-band__rule" aria-hidden="true"></div>
			<div data-reveal>
				<p class="text">Tratamos o Kra de Rica como uma moda feminina selecionada: imagem protagonista, tipografia elegante, hierarquia clara e zero ruído visual.</p>
			</div>
		</div>
	</section>

	<section class="section" id="faq">
		<div class="container-main" style="max-width:48rem;margin-inline:auto">
			<p class="eyebrow section-kicker">Dúvidas</p>
			<h2 class="section-title">Perguntas frequentes</h2>
			<div class="faq">
				<details open>
					<summary>As peças do Kra de Rica têm bom custo-benefício?</summary>
					<p>Sim. Você investe em peças com curadoria de acabamento e presença — estética de boutique com proposta comercial de brechó.</p>
				</details>
				<details>
					<summary>As peças são novas ou selecionadas?</summary>
					<p>Trabalhamos com peças novas e selecionadas, sempre com olhar atento a acabamento, presença e versatilidade.</p>
				</details>
				<details>
					<summary>Como funcionam frete, retirada e troca?</summary>
					<p>Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba. Frete a combinar ou retirada na loja.</p>
				</details>
				<details>
					<summary>As peças são únicas?</summary>
					<p>Muitas sim. Garanta a sua escolha assim que se reconhecer nela.</p>
				</details>
			</div>
		</div>
	</section>

	<?php
	$instagram  = vesta_instagram_url();
	$whatsapp   = vesta_whatsapp_url('Olá! Vim pelo site da Kra de Rica.');
	$maps       = vesta_maps_url();
	$maps_embed = vesta_maps_embed_url();
	?>
	<section class="contact-band" id="contato">
		<div class="container-main contact-band__grid">
			<div data-reveal>
				<p class="eyebrow">Vamos conversar</p>
				<h2>A sua história também inspira o Kra de Rica.</h2>
				<p class="lead">Conte o que você procura. Atendimento no Centro de Curitiba, com a mesma atenção da curadoria.</p>
				<p class="lead contact-band__address">
					<a href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener noreferrer">
						Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba.
					</a>
				</p>
				<div class="contact-links">
					<a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer">
						<span class="contact-links__icon" aria-hidden="true"><?php echo vesta_social_icon_svg('instagram'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						Instagram
					</a>
					<a href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="noopener noreferrer">
						<span class="contact-links__icon" aria-hidden="true"><?php echo vesta_social_icon_svg('whatsapp'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						WhatsApp
					</a>
				</div>
			</div>
			<div data-reveal class="contact-map">
				<iframe
					class="contact-map__frame"
					title="<?php esc_attr_e('Localização da Kra de Rica no Google Maps', 'vesta'); ?>"
					src="<?php echo esc_url($maps_embed); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen
				></iframe>
				<p class="contact-map__link">
					<a href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener noreferrer">Abrir no Google Maps</a>
				</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
