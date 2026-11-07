<?php
declare(strict_types=1);
if (! defined('ABSPATH')) {
	exit;
}

$shop = home_url('/produtos/');
$categories = [
	['Blazers', add_query_arg('category', 'blazers', $shop)],
	['Camisas', add_query_arg('category', 'camisas', $shop)],
	['Vestidos', add_query_arg('category', 'vestidos', $shop)],
	['Casacos', add_query_arg('category', 'casacos', $shop)],
	['Sapatos', add_query_arg('category', 'sapatos', $shop)],
	['Bolsas', add_query_arg('category', 'bolsas', $shop)],
];
$pages = [
	['Início', home_url('/')],
	['Peças', $shop],
	['Categorias', home_url('/#categorias')],
	['Contato', home_url('/contato/')],
	['Minha conta', home_url('/login/')],
];
$social = [
	[
		'label' => 'Instagram',
		'url'   => vesta_instagram_url(),
		'icon'  => 'instagram',
	],
	[
		'label' => 'WhatsApp',
		'url'   => vesta_whatsapp_url('Olá! Vim pelo site da Kra de Rica.'),
		'icon'  => 'whatsapp',
	],
];
$logo_url = VESTA_THEME_URI . '/assets/img/logo.jpg?v=' . VESTA_THEME_VERSION;
?>
<footer class="site-footer">
	<div class="container-main footer-grid footer-grid--4">
		<div class="footer-brand">
			<a class="footer-brand__link" href="<?php echo esc_url(home_url('/')); ?>">
				<img class="footer-brand__logo" src="<?php echo esc_url($logo_url); ?>" alt="Brechó Kra de Rica" width="56" height="56">
				<span>
					<strong class="brand">Brechó Kra de Rica</strong>
					<em class="phrase">Peças novas e selecionadas</em>
				</span>
			</a>
			<p class="footer-address">
				<a href="<?php echo esc_url(vesta_maps_url()); ?>" target="_blank" rel="noopener noreferrer">
					Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba.
				</a>
			</p>
		</div>

		<div>
			<p class="eyebrow">Categorias</p>
			<nav class="footer-nav" aria-label="<?php esc_attr_e('Categorias de peças', 'vesta'); ?>">
				<?php foreach ($categories as [$label, $url]) : ?>
					<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div>
			<p class="eyebrow">Páginas</p>
			<nav class="footer-nav" aria-label="<?php esc_attr_e('Páginas', 'vesta'); ?>">
				<?php foreach ($pages as [$label, $url]) : ?>
					<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div>
			<p class="eyebrow">Redes sociais</p>
			<div class="footer-social">
				<?php foreach ($social as $item) : ?>
					<a
						href="<?php echo esc_url($item['url']); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr($item['label']); ?>"
						title="<?php echo esc_attr($item['label']); ?>"
					>
						<?php echo vesta_social_icon_svg($item['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="container-main footer-bottom">
		<p>© <?php echo esc_html(gmdate('Y')); ?> BRECHÓ KRA DE RICA · Peças novas e selecionadas</p>
		<p class="center footer-credit">
			<span>Site construído por</span>
			<a href="https://grupocostabr.com.br/" target="_blank" rel="noopener noreferrer">Grupo Costa Brasil</a>
			<a
				class="footer-credit__ig"
				href="https://www.instagram.com/grupocostabrasil/"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e('Instagram do Grupo Costa Brasil', 'vesta'); ?>"
				title="Instagram"
			>
				<?php echo vesta_social_icon_svg('instagram'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</p>
		<p class="right"><a href="#topo">Voltar ao topo ↑</a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
