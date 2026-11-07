<?php
/**
 * Template Name: A Loja
 */
declare(strict_types=1);
get_header();
?>
<main class="site-main">
	<section class="vesta-about-hero">
		<div class="container-main">
			<p class="site-brand" style="font-size:1.5rem">
				<img class="site-brand__logo" src="<?php echo esc_url(VESTA_THEME_URI . '/assets/img/logo.jpg?v=' . VESTA_THEME_VERSION); ?>" alt="" width="48" height="48">
				<span class="site-brand__text">Kra de Rica</span>
			</p>
			<p class="vesta-about-hero__phrase">Peças novas e selecionadas</p>
		</div>
	</section>

	<section class="section" id="loja">
		<div class="container-main">
			<div class="vesta-manifesto-head" data-reveal>
				<p class="eyebrow">Posicionamento</p>
				<h1 class="display" style="font-size:clamp(2rem,5vw,3rem);margin:.75rem 0 0">O Kra de Rica é para você</h1>
				<p style="margin:1.5rem auto 0;max-width:36rem;color:var(--taupe);line-height:1.75">
					Tratamos a marca como uma boutique de moda feminina selecionada — com proposta comercial de brechó, estética editorial e curadoria atenta a acabamento e presença.
				</p>
			</div>
			<div class="vesta-manifesto-pillars">
				<article data-reveal>
					<span class="eyebrow">01</span>
					<h3>Personalidade</h3>
					<p>Peças que acompanham sua história, sem tentar encaixar você em uma só definição.</p>
				</article>
				<article data-reveal>
					<span class="eyebrow">02</span>
					<h3>Curadoria</h3>
					<p>Seleção atenta a acabamento, presença e versatilidade — imagem protagonista, pouco ruído.</p>
				</article>
				<article data-reveal>
					<span class="eyebrow">03</span>
					<h3>Boutique</h3>
					<p>Brechó com proposta comercial de boutique no Centro de Curitiba.</p>
				</article>
			</div>
			<p style="margin-top:2.5rem;text-align:center;font-weight:600;color:var(--burgundy)">
				Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba.
			</p>
		</div>
	</section>

	<section class="editorial-band">
		<div class="container-main editorial-band__grid">
			<div data-reveal>
				<p class="eyebrow" style="color:var(--gold)">Vestir como gesto</p>
				<h2>Escolha <em>sentir</em>.</h2>
			</div>
			<div class="editorial-band__rule" aria-hidden="true"></div>
			<div data-reveal>
				<p class="text">A elegância deixou de seguir uma única regra. Escolha aquilo que faz você se reconhecer — com tipografia clara, hierarquia limpa e fotografia sofisticada.</p>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container-main" style="text-align:center">
			<a class="btn btn--dark" href="<?php echo esc_url(home_url('/produtos/')); ?>">Explorar peças</a>
		</div>
	</section>
</main>
<?php
get_footer();
